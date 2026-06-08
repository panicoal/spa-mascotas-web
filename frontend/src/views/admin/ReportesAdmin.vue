<template>
  <div class="min-h-screen bg-gray-50">
    <nav class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-xl font-semibold text-gray-900">Pet Spa - Reportes</h1>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-700">{{ user?.nombre_completo }}</span>
            <router-link
              to="/admin"
              class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm font-medium"
            >
              ← Volver al Dashboard
            </router-link>
            <button
              @click="handleLogout"
              class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm font-medium"
            >
              Cerrar sesión
            </button>
          </div>
        </div>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <!-- Alertas -->
        <div v-if="successMessage" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
          ✅ {{ successMessage }}
        </div>
        <div v-if="errorMessage" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
          ❌ {{ errorMessage }}
        </div>

        <!-- Encabezado -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900 mb-2">Centro de Reportes</h1>
          <p class="text-gray-600">Descarga reportes en PDF para análisis y auditoría del negocio</p>
        </div>

        <!-- Grid de reportes -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Reporte Mensual -->
          <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
              <h2 class="text-xl font-bold text-white">📊 Reporte Mensual</h2>
              <p class="text-blue-100 text-sm mt-1">Ventas y transacciones del mes</p>
            </div>
            <div class="px-6 py-4">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Selecciona el mes</label>
                <div class="flex gap-2">
                  <input
                    v-model.number="filtroMes"
                    type="number"
                    min="1"
                    max="12"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Mes (1-12)"
                  />
                  <input
                    v-model.number="filtroAnio"
                    type="number"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Año"
                  />
                </div>
              </div>
              <p class="text-sm text-gray-600 mb-4">
                📅 Período: <strong>{{ getNombreMes(filtroMes) }} {{ filtroAnio }}</strong>
              </p>
              <button
                @click="descargarReporteMensual"
                :disabled="descargandoReporteMensual"
                class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-medium py-2 px-4 rounded-md transition-colors"
              >
                <span v-if="descargandoReporteMensual" class="flex items-center justify-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Generando...
                </span>
                <span v-else>⬇️ Descargar PDF</span>
              </button>
              <p class="text-xs text-gray-500 mt-3 text-center">
                Incluye: Ventas, métodos de pago, productos críticos
              </p>
            </div>
          </div>

          <!-- Reporte Cierre de Caja -->
          <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
            <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
              <h2 class="text-xl font-bold text-white">💰 Cierre de Caja</h2>
              <p class="text-green-100 text-sm mt-1">Resumen del día actual</p>
            </div>
            <div class="px-6 py-4">
              <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-4">
                <p class="text-sm text-gray-600">
                  <strong>Fecha:</strong> {{ formatearFecha(hoy) }}
                </p>
                <p class="text-sm text-gray-600 mt-1">
                  <strong>Total del día:</strong> {{ formatCurrency(totalDia) }}
                </p>
              </div>
              <button
                @click="descargarCierreCajaPdf"
                :disabled="descargandoCierreCaja"
                class="w-full bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white font-medium py-2 px-4 rounded-md transition-colors"
              >
                <span v-if="descargandoCierreCaja" class="flex items-center justify-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Generando...
                </span>
                <span v-else>⬇️ Descargar PDF</span>
              </button>
              <p class="text-xs text-gray-500 mt-3 text-center">
                Incluye: Desglose por método de pago
              </p>
            </div>
          </div>

          <!-- Reporte Inventario -->
          <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
              <h2 class="text-xl font-bold text-white">📦 Inventario</h2>
              <p class="text-purple-100 text-sm mt-1">Estado actual del stock</p>
            </div>
            <div class="px-6 py-4">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de reporte</label>
                <div class="space-y-2">
                  <label class="flex items-center">
                    <input
                      v-model="tipoInventario"
                      type="radio"
                      value="completo"
                      class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                    />
                    <span class="ml-2 text-sm text-gray-700">Inventario completo</span>
                  </label>
                  <label class="flex items-center">
                    <input
                      v-model="tipoInventario"
                      type="radio"
                      value="alertas"
                      class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                    />
                    <span class="ml-2 text-sm text-gray-700">Solo bajo stock</span>
                  </label>
                </div>
              </div>
              <button
                @click="descargarReporteInventario"
                :disabled="descargandoInventario"
                class="w-full bg-purple-600 hover:bg-purple-700 disabled:bg-purple-400 text-white font-medium py-2 px-4 rounded-md transition-colors"
              >
                <span v-if="descargandoInventario" class="flex items-center justify-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Generando...
                </span>
                <span v-else>⬇️ Descargar PDF</span>
              </button>
              <p class="text-xs text-gray-500 mt-3 text-center">
                Incluye: Productos, stock, valores
              </p>
            </div>
          </div>
        </div>

        <!-- Información adicional -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
          <h3 class="text-lg font-semibold text-blue-900 mb-2">ℹ️ Información sobre Reportes</h3>
          <ul class="text-sm text-blue-800 space-y-1">
            <li>✓ Los reportes se generan en PDF con formato profesional</li>
            <li>✓ Puedes descargar múltiples reportes para comparar períodos</li>
            <li>✓ Los datos se actualizar en tiempo real desde la base de datos</li>
            <li>✓ Todos los reportes incluyen fecha y usuario que los generó para auditoría</li>
          </ul>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import * as adminService from '@/services/adminService'

const router = useRouter()

// State
const user = ref(null)
const successMessage = ref('')
const errorMessage = ref('')

const filtroMes = ref(new Date().getMonth() + 1)
const filtroAnio = ref(new Date().getFullYear())
const tipoInventario = ref('completo')
const hoy = ref(new Date().toISOString().split('T')[0])
const totalDia = ref(0)

const descargandoReporteMensual = ref(false)
const descargandoCierreCaja = ref(false)
const descargandoInventario = ref(false)

// Métodos
const handleLogout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/login')
}

const getNombreMes = (mes) => {
  const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
  return meses[mes - 1] || 'Inválido'
}

const formatearFecha = (fecha) => {
  const date = new Date(fecha)
  return date.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'USD'
  }).format(value || 0)
}

const descargarReporteMensual = async () => {
  try {
    descargandoReporteMensual.value = true
    errorMessage.value = ''
    successMessage.value = ''

    const response = await adminService.downloadReporteMensualPdf(filtroMes.value, filtroAnio.value)
    
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `Reporte-Mensual-${filtroAnio.value}-${filtroMes.value.toString().padStart(2, '0')}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.parentNode.removeChild(link)

    successMessage.value = 'Reporte descargado exitosamente'
  } catch (error) {
    errorMessage.value = 'Error al descargar el reporte: ' + (error.response?.data?.message || error.message)
  } finally {
    descargandoReporteMensual.value = false
  }
}

const descargarCierreCajaPdf = async () => {
  try {
    descargandoCierreCaja.value = true
    errorMessage.value = ''
    successMessage.value = ''

    const response = await adminService.downloadCierreCajaPdf()
    
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `Cierre-Caja-${hoy.value}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.parentNode.removeChild(link)

    successMessage.value = 'Cierre de caja descargado exitosamente'
  } catch (error) {
    errorMessage.value = 'Error al descargar el cierre: ' + (error.response?.data?.message || error.message)
  } finally {
    descargandoCierreCaja.value = false
  }
}

const descargarReporteInventario = async () => {
  try {
    descargandoInventario.value = true
    errorMessage.value = ''
    successMessage.value = ''

    const bajoStockOnly = tipoInventario.value === 'alertas'
    const response = await adminService.downloadReporteInventarioPdf(bajoStockOnly)
    
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    const tipo = bajoStockOnly ? 'Alertas' : 'Completo'
    link.setAttribute('download', `Reporte-Inventario-${tipo}-${hoy.value}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.parentNode.removeChild(link)

    successMessage.value = 'Reporte de inventario descargado exitosamente'
  } catch (error) {
    errorMessage.value = 'Error al descargar el reporte: ' + (error.response?.data?.message || error.message)
  } finally {
    descargandoInventario.value = false
  }
}

const cargarTotalDia = async () => {
  try {
    const response = await adminService.getCierreCaja()
    totalDia.value = response.data.total_neto || 0
  } catch (error) {
    console.error('Error cargando total del día:', error)
  }
}

onMounted(() => {
  const userData = localStorage.getItem('user')
  if (userData) {
    user.value = JSON.parse(userData)
  }
  cargarTotalDia()
})
</script>
