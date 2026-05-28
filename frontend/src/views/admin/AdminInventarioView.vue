<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 p-6">

    <!-- Header -->
    <div class="flex flex-wrap justify-between items-center gap-4 mb-8">
      <div>
        <h1 class="text-3xl font-extrabold bg-gradient-to-r from-emerald-400 to-teal-400 bg-clip-text text-transparent">
          📦 Inventario
        </h1>
        <p class="text-slate-400 text-sm mt-0.5">Gestión de productos y stock del spa</p>
      </div>
      <button
        @click="openCreate"
        class="bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm transition active:scale-95 shadow-lg shadow-emerald-500/20"
      >
        + Nuevo Producto
      </button>
    </div>

    <!-- KPI Cards -->
    <div v-if="store.dashboard" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4">
        <p class="text-xs text-slate-400 mb-1">Total Productos</p>
        <p class="text-2xl font-extrabold text-slate-100">{{ store.dashboard.total_productos }}</p>
      </div>
      <div :class="store.dashboard.bajo_stock_count > 0 ? 'border-red-900/50 bg-red-950/20' : 'border-slate-800 bg-slate-900'"
           class="border rounded-2xl p-4 transition">
        <p class="text-xs text-slate-400 mb-1">Bajo Stock ⚠️</p>
        <p class="text-2xl font-extrabold" :class="store.dashboard.bajo_stock_count > 0 ? 'text-red-400' : 'text-emerald-400'">
          {{ store.dashboard.bajo_stock_count }}
        </p>
      </div>
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 col-span-2">
        <p class="text-xs text-slate-400 mb-2">Alertas de Stock</p>
        <div v-if="store.dashboard.productos_alerta?.length" class="space-y-1">
          <div v-for="p in store.dashboard.productos_alerta.slice(0,3)" :key="p.id"
               class="flex justify-between text-xs">
            <span class="text-slate-300 truncate">{{ p.nombre }}</span>
            <span class="text-red-400 font-bold ml-2 shrink-0">{{ p.stock_actual }}/{{ p.stock_minimo }} {{ p.unidad_medida }}</span>
          </div>
        </div>
        <p v-else class="text-xs text-emerald-400">✓ Todos los productos tienen stock suficiente</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-3 mb-6">
      <input
        v-model="filtroNombre"
        type="text"
        placeholder="Buscar producto..."
        class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500 transition placeholder-slate-600 flex-1 min-w-48"
      />
      <select
        v-model="filtroCategoria"
        class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500 transition"
      >
        <option value="">Todas las categorías</option>
        <option value="SHAMPOO">Shampoo</option>
        <option value="ACONDICIONADOR">Acondicionador</option>
        <option value="HERRAMIENTAS">Herramientas</option>
        <option value="MEDICAMENTOS">Medicamentos</option>
        <option value="ACCESORIOS">Accesorios</option>
        <option value="GENERAL">General</option>
      </select>
      <label class="flex items-center gap-2 text-sm text-slate-400 bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 cursor-pointer hover:border-slate-600 transition">
        <input type="checkbox" v-model="solobajoStock" class="accent-red-500" />
        Solo bajo stock
      </label>
    </div>

    <!-- Loading -->
    <div v-if="store.loading" class="flex justify-center py-16">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-500"></div>
    </div>

    <!-- Products Table -->
    <div v-else class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-xs text-slate-400 uppercase tracking-wider border-b border-slate-800/80">
              <th class="text-left px-5 py-3.5">Producto</th>
              <th class="text-left px-5 py-3.5">Categoría</th>
              <th class="text-left px-5 py-3.5">Stock</th>
              <th class="text-left px-5 py-3.5">Mín.</th>
              <th class="text-left px-5 py-3.5">Precio Venta</th>
              <th class="text-left px-5 py-3.5">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!productosFiltrados.length">
              <td colspan="6" class="text-center py-12 text-slate-500">
                Sin productos que mostrar
              </td>
            </tr>
            <tr
              v-for="p in productosFiltrados"
              :key="p.id"
              class="border-b border-slate-800/40 hover:bg-slate-800/30 transition"
            >
              <td class="px-5 py-3.5">
                <div class="font-semibold text-slate-100">{{ p.nombre }}</div>
                <div v-if="p.codigo" class="text-[10px] text-slate-500">{{ p.codigo }}</div>
              </td>
              <td class="px-5 py-3.5">
                <span class="bg-slate-800 text-slate-300 px-2 py-0.5 rounded-full text-[10px] font-medium">
                  {{ p.categoria }}
                </span>
              </td>
              <td class="px-5 py-3.5">
                <div class="flex items-center gap-2">
                  <span
                    class="font-bold text-base"
                    :class="p.bajo_stock ? 'text-red-400' : 'text-emerald-400'"
                  >
                    {{ p.stock_actual }}
                  </span>
                  <span class="text-slate-500 text-xs">{{ p.unidad_medida }}</span>
                  <span v-if="p.bajo_stock" class="text-xs text-red-400">⚠️ Bajo stock</span>
                </div>
              </td>
              <td class="px-5 py-3.5 text-slate-400">{{ p.stock_minimo }}</td>
              <td class="px-5 py-3.5 text-slate-300">
                {{ p.precio_venta ? `$${Number(p.precio_venta).toFixed(2)}` : '-' }}
              </td>
              <td class="px-5 py-3.5">
                <div class="flex gap-1.5">
                  <button
                    @click="openMovimiento(p)"
                    class="bg-emerald-950/30 text-emerald-300 border border-emerald-900/40 px-2.5 py-1 rounded-lg text-xs font-medium hover:bg-emerald-900/40 transition"
                    title="Registrar movimiento"
                  >
                    📦 Stock
                  </button>
                  <button
                    @click="openEdit(p)"
                    class="bg-blue-950/30 text-blue-300 border border-blue-900/40 px-2.5 py-1 rounded-lg text-xs font-medium hover:bg-blue-900/40 transition"
                  >
                    ✏️
                  </button>
                  <button
                    @click="eliminarProducto(p.id)"
                    class="bg-red-950/20 text-red-400 border border-red-900/30 px-2.5 py-1 rounded-lg text-xs font-medium hover:bg-red-900/30 transition"
                  >
                    🗑️
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Product Modal (Create/Edit) -->
    <div v-if="showProductoModal" class="fixed inset-0 bg-black/80 backdrop-blur-md flex items-center justify-center z-50 p-4 animate-fade-in">
      <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg shadow-2xl">
        <div class="flex justify-between items-center p-6 border-b border-slate-800/60">
          <h2 class="text-xl font-extrabold text-slate-100">
            {{ editingProducto ? '✏️ Editar Producto' : '📦 Nuevo Producto' }}
          </h2>
          <button @click="showProductoModal = false" class="text-slate-500 hover:text-slate-200 text-xl">✕</button>
        </div>
        <form @submit.prevent="saveProducto" class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nombre *</label>
              <input v-model="productoForm.nombre" required type="text" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-emerald-500 transition" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">Código</label>
              <input v-model="productoForm.codigo" type="text" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-emerald-500 transition" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">Categoría</label>
              <select v-model="productoForm.categoria" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-emerald-500 transition">
                <option value="GENERAL">General</option>
                <option value="SHAMPOO">Shampoo</option>
                <option value="ACONDICIONADOR">Acondicionador</option>
                <option value="HERRAMIENTAS">Herramientas</option>
                <option value="MEDICAMENTOS">Medicamentos</option>
                <option value="ACCESORIOS">Accesorios</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">Unidad</label>
              <select v-model="productoForm.unidad_medida" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-emerald-500 transition">
                <option value="UNIDAD">Unidad</option>
                <option value="ML">ML</option>
                <option value="KG">KG</option>
                <option value="LITRO">Litro</option>
                <option value="GR">Gramos</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">Stock Mínimo</label>
              <input v-model="productoForm.stock_minimo" type="number" min="0" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-emerald-500 transition" />
            </div>
            <div v-if="!editingProducto">
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">Stock Inicial *</label>
              <input v-model="productoForm.stock_actual" type="number" min="0" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-emerald-500 transition" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">Precio Compra</label>
              <input v-model="productoForm.precio_compra" type="number" step="0.01" min="0" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-emerald-500 transition" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">Precio Venta</label>
              <input v-model="productoForm.precio_venta" type="number" step="0.01" min="0" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-emerald-500 transition" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Descripción</label>
            <textarea v-model="productoForm.descripcion" rows="2" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-emerald-500 transition resize-none"></textarea>
          </div>
          <div v-if="errorMsg" class="bg-red-950/30 border border-red-900/40 text-red-300 text-sm rounded-xl px-4 py-2.5">{{ errorMsg }}</div>
          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="showProductoModal = false" class="px-5 py-2.5 rounded-xl border border-slate-700 text-slate-400 hover:bg-slate-800/40 transition text-sm">Cancelar</button>
            <button type="submit" :disabled="saving" class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold px-6 py-2.5 rounded-xl transition text-sm disabled:opacity-50">
              {{ saving ? 'Guardando...' : (editingProducto ? 'Actualizar' : 'Crear Producto') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Movimiento Modal -->
    <div v-if="showMovimientoModal" class="fixed inset-0 bg-black/80 backdrop-blur-md flex items-center justify-center z-50 p-4 animate-fade-in">
      <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md shadow-2xl">
        <div class="flex justify-between items-center p-6 border-b border-slate-800/60">
          <div>
            <h2 class="text-xl font-extrabold text-slate-100">📦 Registrar Movimiento</h2>
            <p class="text-xs text-slate-400">{{ selectedProducto?.nombre }}</p>
          </div>
          <button @click="showMovimientoModal = false" class="text-slate-500 hover:text-slate-200 text-xl">✕</button>
        </div>
        <form @submit.prevent="saveMovimiento" class="p-6 space-y-4">
          <!-- Tipo buttons -->
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-2">Tipo de Movimiento *</label>
            <div class="grid grid-cols-3 gap-2">
              <button
                v-for="tipo_movimiento in ['ENTRADA','SALIDA','AJUSTE']"
                :key="tipo_movimiento"
                type="button"
                @click="movimientoForm.tipo_movimiento = tipo_movimiento"
                :class="[
                  'py-2 rounded-lg border text-xs font-bold transition',
                  movimientoForm.tipo_movimiento === tipo_movimiento
                    ? tipo_movimiento === 'ENTRADA' ? 'border-emerald-500 bg-emerald-600/20 text-emerald-300'
                      : tipo_movimiento === 'SALIDA' ? 'border-red-500 bg-red-600/20 text-red-300'
                      : 'border-blue-500 bg-blue-600/20 text-blue-300'
                    : 'border-slate-800 bg-slate-950/30 text-slate-400 hover:border-slate-600'
                ]"
              >
                {{ tipo_movimiento === 'ENTRADA' ? '⬆️ ' : tipo_movimiento === 'SALIDA' ? '⬇️ ' : '🔄 ' }}{{ tipo_movimiento }}
              </button>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
              {{ movimientoForm.tipo_movimiento === 'AJUSTE' ? 'Nuevo Stock Total *' : 'Cantidad *' }}
            </label>
            <input
              v-model="movimientoForm.cantidad"
              type="number" min="1" required
              :placeholder="movimientoForm.tipo_movimiento === 'AJUSTE' ? 'Ingresa el stock total correcto' : 'Cantidad a mover'"
              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-emerald-500 transition"
            />
            <p class="text-xs text-slate-500 mt-1">
              Stock actual: <span class="font-bold text-slate-300">{{ selectedProducto?.stock_actual }}</span> {{ selectedProducto?.unidad_medida }}
            </p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Motivo</label>
            <input
              v-model="movimientoForm.motivo"
              type="text"
              placeholder="Ej: Compra proveedor X, uso en servicio..."
              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-emerald-500 transition placeholder-slate-600"
            />
          </div>

          <div v-if="errorMsg" class="bg-red-950/30 border border-red-900/40 text-red-300 text-sm rounded-xl px-4 py-2.5">{{ errorMsg }}</div>

          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="showMovimientoModal = false" class="px-5 py-2.5 rounded-xl border border-slate-700 text-slate-400 hover:bg-slate-800/40 transition text-sm">Cancelar</button>
            <button type="submit" :disabled="saving || !movimientoForm.tipo_movimiento" class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold px-6 py-2.5 rounded-xl transition text-sm disabled:opacity-50">
              {{ saving ? 'Registrando...' : 'Registrar' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { useInventarioStore } from '@/stores/inventarioStore'

const store = useInventarioStore()

// State
const showProductoModal   = ref(false)
const showMovimientoModal = ref(false)
const editingProducto     = ref(null)
const selectedProducto    = ref(null)
const saving              = ref(false)
const errorMsg            = ref('')

// Filters
const filtroNombre    = ref('')
const filtroCategoria = ref('')
const solobajoStock   = ref(false)

const productosFiltrados = computed(() => {
  let list = store.productos
  if (filtroNombre.value) {
    const q = filtroNombre.value.toLowerCase()
    list = list.filter(p => p.nombre.toLowerCase().includes(q) || (p.codigo || '').toLowerCase().includes(q))
  }
  if (filtroCategoria.value) {
    list = list.filter(p => p.categoria === filtroCategoria.value)
  }
  if (solobajoStock.value) {
    list = list.filter(p => p.bajo_stock)
  }
  return list
})

const productoForm = reactive({
  nombre: '', codigo: '', descripcion: '', categoria: 'GENERAL',
  unidad_medida: 'UNIDAD', precio_compra: '', precio_venta: '',
  stock_actual: 0, stock_minimo: 5
})

const movimientoForm = reactive({ tipo_movimiento: 'ENTRADA', cantidad: '', motivo: '' })

onMounted(async () => {
  await Promise.all([store.fetchProductos(), store.fetchDashboard()])
})

function openCreate() {
  editingProducto.value = null
  Object.assign(productoForm, { nombre: '', codigo: '', descripcion: '', categoria: 'GENERAL',
    unidad_medida: 'UNIDAD', precio_compra: '', precio_venta: '', stock_actual: 0, stock_minimo: 5 })
  errorMsg.value = ''
  showProductoModal.value = true
}

function openEdit(p) {
  editingProducto.value = p
  Object.assign(productoForm, {
    nombre: p.nombre, codigo: p.codigo || '', descripcion: p.descripcion || '',
    categoria: p.categoria, unidad_medida: p.unidad_medida,
    precio_compra: p.precio_compra || '', precio_venta: p.precio_venta || '',
    stock_minimo: p.stock_minimo
  })
  errorMsg.value = ''
  showProductoModal.value = true
}

function openMovimiento(p) {
  selectedProducto.value = p
  Object.assign(movimientoForm, { tipo_movimiento: 'ENTRADA', cantidad: '', motivo: '' })
  errorMsg.value = ''
  showMovimientoModal.value = true
}

async function saveProducto() {
  saving.value = true
  errorMsg.value = ''
  try {
    if (editingProducto.value) {
      await store.updateProducto(editingProducto.value.id, productoForm)
    } else {
      await store.createProducto(productoForm)
    }
    showProductoModal.value = false
    await store.fetchDashboard()
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Error al guardar el producto.'
  } finally {
    saving.value = false
  }
}

async function saveMovimiento() {
  if (!movimientoForm.tipo_movimiento || !movimientoForm.cantidad) return
  saving.value = true
  errorMsg.value = ''
  try {
    await store.registrarMovimiento(selectedProducto.value.id, movimientoForm)
    showMovimientoModal.value = false
    await store.fetchDashboard()
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Error al registrar el movimiento.'
  } finally {
    saving.value = false
  }
}

async function eliminarProducto(id) {
  if (!confirm('¿Eliminar este producto del inventario?')) return
  try {
    await store.deleteProducto(id)
    await store.fetchDashboard()
  } catch (e) {
    alert('No se pudo eliminar el producto.')
  }
}
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.18s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: scale(0.97); } to { opacity: 1; transform: scale(1); } }
</style>
