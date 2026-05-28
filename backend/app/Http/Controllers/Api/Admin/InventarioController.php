<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\MovimientoInventario;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InventarioController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    //  PRODUCTOS
    // ─────────────────────────────────────────────────────────────

    /**
     * List all products with low-stock alert flag
     */
    public function index(Request $request): JsonResponse
    {
        $query = Producto::query();

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->boolean('bajo_stock')) {
            $query->whereRaw('stock_actual <= stock_minimo');
        }

        if ($request->filled('q')) {
            $query->where('nombre', 'like', '%' . $request->q . '%')
                  ->orWhere('codigo', 'like', '%' . $request->q . '%');
        }

        $productos = $query->orderBy('nombre')->get();

        return response()->json(['productos' => $productos]);
    }

    /**
     * Create a new product
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'        => 'required|string|max:150',
            'codigo'        => 'nullable|string|max:50|unique:productos,codigo',
            'descripcion'   => 'nullable|string|max:1000',
            'categoria'     => 'nullable|string|max:80',
            'unidad_medida' => 'nullable|string|in:UNIDAD,ML,KG,LITRO,GR',
            'precio_compra' => 'nullable|numeric|min:0',
            'precio_venta'  => 'nullable|numeric|min:0',
            'stock_actual'  => 'required|integer|min:0',
            'stock_minimo'  => 'nullable|integer|min:0',
        ]);

        $producto = Producto::create($data);

        // Register initial entry movement
        if ($data['stock_actual'] > 0) {
            MovimientoInventario::create([
                'producto_id'    => $producto->id,
                'usuario_id'     => auth()->id(),
                'tipo_movimiento'=> 'ENTRADA',
                'motivo'         => 'Stock inicial al crear producto',
                'cantidad'       => $data['stock_actual'],
                'stock_anterior' => 0,
                'stock_nuevo'    => $data['stock_actual'],
            ]);
        }

        AuditService::log(auth()->id(), 'CREAR_PRODUCTO', 'productos', $producto->id, null, $producto->toArray());

        return response()->json(['message' => 'Producto creado correctamente.', 'producto' => $producto], 201);
    }

    /**
     * Show a single product with its movement history
     */
    public function show(string $id): JsonResponse
    {
        $producto = Producto::with('movimientos.usuario')->findOrFail($id);

        return response()->json(['producto' => $producto]);
    }

    /**
     * Update product details (not stock - use movimiento endpoint)
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $producto = Producto::findOrFail($id);
        $antes = $producto->toArray();

        $data = $request->validate([
            'nombre'        => 'sometimes|string|max:150',
            'codigo'        => 'nullable|string|max:50|unique:productos,codigo,' . $id,
            'descripcion'   => 'nullable|string|max:1000',
            'categoria'     => 'nullable|string|max:80',
            'unidad_medida' => 'nullable|string|in:UNIDAD,ML,KG,LITRO,GR',
            'precio_compra' => 'nullable|numeric|min:0',
            'precio_venta'  => 'nullable|numeric|min:0',
            'stock_minimo'  => 'nullable|integer|min:0',
            'activo'        => 'sometimes|boolean',
        ]);

        $producto->update($data);

        AuditService::log(auth()->id(), 'ACTUALIZAR_PRODUCTO', 'productos', $producto->id, $antes, $producto->fresh()->toArray());

        return response()->json(['message' => 'Producto actualizado.', 'producto' => $producto->fresh()]);
    }

    /**
     * Soft-delete a product
     */
    public function destroy(string $id): JsonResponse
    {
        $producto = Producto::findOrFail($id);
        $antes = $producto->toArray();

        $producto->delete();

        AuditService::log(auth()->id(), 'ELIMINAR_PRODUCTO', 'productos', $producto->id, $antes, null);

        return response()->json(['message' => 'Producto eliminado correctamente.']);
    }

    // ─────────────────────────────────────────────────────────────
    //  MOVIMIENTOS DE INVENTARIO
    // ─────────────────────────────────────────────────────────────

    /**
     * Register a stock movement (ENTRADA | SALIDA | AJUSTE)
     * This is the core inventory operation that satisfies checklist item
     */
    public function registrarMovimiento(Request $request, string $productoId): JsonResponse
    {
        $producto = Producto::findOrFail($productoId);

        $data = $request->validate([
            'tipo_movimiento'     => 'required|in:ENTRADA,SALIDA,AJUSTE',
            'cantidad' => 'required|integer|min:1',
            'motivo'   => 'nullable|string|max:200',
            'cita_id'  => 'nullable|uuid|exists:citas,id',
        ]);

        $stockAnterior = $producto->stock_actual;

        // Calculate new stock
        $nuevoStock = match ($data['tipo_movimiento']) {
            'ENTRADA' => $stockAnterior + $data['cantidad'],
            'SALIDA'  => $stockAnterior - $data['cantidad'],
            'AJUSTE'  => $data['cantidad'],  // Set absolute value for adjustments
        };

        // Prevent negative stock on SALIDA
        if ($data['tipo_movimiento'] === 'SALIDA' && $nuevoStock < 0) {
            return response()->json([
                'message' => "Stock insuficiente. Disponible: {$stockAnterior} {$producto->unidad_medida}."
            ], 422);
        }

        $movimiento = MovimientoInventario::create([
            'producto_id'    => $producto->id,
            'usuario_id'     => auth()->id(),
            'cita_id'        => $data['cita_id'] ?? null,
            'tipo_movimiento'           => $data['tipo_movimiento'],
            'motivo'         => $data['motivo'] ?? null,
            'cantidad'       => $data['cantidad'],
            'stock_anterior' => $stockAnterior,
            'stock_nuevo'    => $nuevoStock,
        ]);

        $producto->update(['stock_actual' => $nuevoStock]);

        // Low stock alert flag in response
        $bajoStock = $nuevoStock <= $producto->stock_minimo;

        AuditService::log(
            auth()->id(),
            'MOVIMIENTO_INVENTARIO',
            'movimientos_inventario',
            $movimiento->id,
            ['stock_anterior' => $stockAnterior],
            ['stock_nuevo' => $nuevoStock, 'tipo_movimiento' => $data['tipo_movimiento']]
        );

        return response()->json([
            'message'    => "Movimiento de {$data['tipo_movimiento']} registrado correctamente.",
            'movimiento' => $movimiento->load('usuario'),
            'stock_actual' => $nuevoStock,
            'bajo_stock'   => $bajoStock,
        ]);
    }

    /**
     * List movements for a specific product (history)
     */
    public function movimientos(string $productoId): JsonResponse
    {
        $producto = Producto::findOrFail($productoId);

        $movimientos = MovimientoInventario::with('usuario')
            ->where('producto_id', $productoId)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'producto'    => $producto,
            'movimientos' => $movimientos,
        ]);
    }

    /**
     * Dashboard summary: total products, low stock count, recent movements
     */
    public function dashboard(): JsonResponse
    {
        $totalProductos   = Producto::count();
        $bajoStock        = Producto::whereRaw('stock_actual <= stock_minimo')->count();
        $productosAlerta  = Producto::whereRaw('stock_actual <= stock_minimo')
                                    ->orderBy('stock_actual')
                                    ->get(['id','nombre','stock_actual','stock_minimo','unidad_medida']);

        $movimientosRecientes = MovimientoInventario::with(['producto:id,nombre', 'usuario:id,name'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'total_productos'       => $totalProductos,
            'bajo_stock_count'      => $bajoStock,
            'productos_alerta'      => $productosAlerta,
            'movimientos_recientes' => $movimientosRecientes,
        ]);
    }
}
