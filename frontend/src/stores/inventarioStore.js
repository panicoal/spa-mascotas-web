import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useInventarioStore = defineStore('inventario', () => {
  const productos   = ref([])
  const dashboard   = ref(null)
  const loading     = ref(false)
  const error       = ref(null)

  async function fetchProductos(params = {}) {
    loading.value = true
    error.value   = null
    try {
      const { data } = await api.get('/inventario/productos', { params })
      productos.value = data.productos
    } catch (e) {
      error.value = e.response?.data?.message || 'Error al cargar productos'
    } finally {
      loading.value = false
    }
  }

  async function fetchDashboard() {
    loading.value = true
    try {
      const { data } = await api.get('/inventario/dashboard')
      dashboard.value = data
    } catch (e) {
      error.value = e.response?.data?.message || 'Error al cargar dashboard'
    } finally {
      loading.value = false
    }
  }

  async function createProducto(payload) {
    const { data } = await api.post('/inventario/productos', payload)
    productos.value.push(data.producto)
    return data
  }

  async function updateProducto(id, payload) {
    const { data } = await api.put(`/inventario/productos/${id}`, payload)
    const idx = productos.value.findIndex(p => p.id === id)
    if (idx !== -1) productos.value[idx] = data.producto
    return data
  }

  async function deleteProducto(id) {
    await api.delete(`/inventario/productos/${id}`)
    productos.value = productos.value.filter(p => p.id !== id)
  }

  async function registrarMovimiento(productoId, payload) {
    const { data } = await api.post(`/inventario/productos/${productoId}/movimiento`, payload)
    // Update stock in local store
    const idx = productos.value.findIndex(p => p.id === productoId)
    if (idx !== -1) {
      productos.value[idx] = {
        ...productos.value[idx],
        stock_actual: data.stock_actual,
        bajo_stock:   data.bajo_stock
      }
    }
    return data
  }

  async function fetchMovimientos(productoId) {
    const { data } = await api.get(`/inventario/productos/${productoId}/movimientos`)
    return data
  }

  return {
    productos, dashboard, loading, error,
    fetchProductos, fetchDashboard,
    createProducto, updateProducto, deleteProducto,
    registrarMovimiento, fetchMovimientos
  }
})
