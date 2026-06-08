<template>
  <div class="min-h-screen bg-slate-950 text-slate-100">
    <nav class="bg-slate-900/95 border-b border-slate-800 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div>
            <h1 class="text-lg font-semibold text-white">Cierre de Caja - Recepción</h1>
            <p class="text-slate-400 text-sm">Resumen diario y auditoría de los movimientos financieros de caja.</p>
          </div>
          <div class="flex items-center gap-3">
            <button
              @click="router.push('/staff')"
              class="bg-slate-800 hover:bg-slate-700 text-slate-100 px-4 py-2 rounded-lg text-sm"
            >
              ← Volver al Panel
            </button>
          </div>
        </div>
      </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div class="grid gap-6">
        <section class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl backdrop-blur">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
              <p class="text-sm uppercase tracking-[0.24em] text-emerald-300">Resumen financiero del día</p>
              <h2 class="mt-2 text-3xl font-extrabold text-white">{{ cierre.fecha || 'Hoy' }}</h2>
              <p class="mt-1 text-slate-400">Movimientos de caja consolidados para el día actual.</p>
            </div>
            <div class="space-y-2 text-right">
              <p class="text-sm text-slate-400">Transacciones</p>
              <p class="text-4xl font-bold text-emerald-400">{{ cierre.total_transacciones ?? 0 }}</p>
            </div>
          </div>

          <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-3xl border border-slate-800 bg-slate-950/80 p-5">
              <p class="text-sm text-slate-400">Ingreso Neto</p>
              <p class="mt-3 text-3xl font-semibold text-white">{{ formatCurrency(cierre.total_neto || 0) }}</p>
            </div>
            <div class="rounded-3xl border border-slate-800 bg-slate-950/80 p-5">
              <p class="text-sm text-slate-400">Métodos Soportados</p>
              <ul class="mt-3 space-y-2 text-sm text-slate-200">
                <li>• Efectivo</li>
                <li>• QR</li>
                <li>• Transferencia</li>
              </ul>
            </div>
            <div class="rounded-3xl border border-slate-800 bg-slate-950/80 p-5">
              <p class="text-sm text-slate-400">Última actualización</p>
              <p class="mt-3 text-base text-slate-200">{{ cierre.fecha ? formatDate(cierre.fecha) : 'Cargando...' }}</p>
            </div>
          </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-[1.5fr_1fr]">
          <div class="rounded-3xl border border-slate-800 bg-slate-900/95 p-6 shadow-xl">
            <div class="flex items-center justify-between mb-5">
              <div>
                <h3 class="text-xl font-semibold text-white">Desglose por método de pago</h3>
                <p class="text-slate-400 text-sm">Los importes están filtrados por los tipos autorizados para cierre de caja.</p>
              </div>
              <button
                @click="handleCloseCaja"
                :disabled="closing"
                class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400 disabled:opacity-50"
              >
                <span v-if="closing" class="animate-spin rounded-full h-3 w-3 border-b-2 border-slate-950"></span>
                Ejecutar Cierre de Caja
              </button>
            </div>

            <div v-if="errorMessage" class="rounded-2xl border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-200 mb-4">
              {{ errorMessage }}
            </div>
            <div v-if="successMessage" class="rounded-2xl border border-emerald-500/40 bg-emerald-500/10 p-4 text-sm text-emerald-200 mb-4">
              {{ successMessage }}
            </div>

            <div v-if="loading" class="py-12 text-center text-slate-400">
              Cargando el cierre de caja...
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="(item, method) in pagoTipos"
                :key="method"
                class="rounded-3xl border border-slate-800 bg-slate-950/80 p-5"
              >
                <div class="flex items-center justify-between gap-3">
                  <div>
                    <p class="text-sm uppercase tracking-[0.16em] text-slate-400">{{ method }}</p>
                    <p class="mt-2 text-2xl font-semibold text-white">{{ formatCurrency(item.total_monto) }}</p>
                  </div>
                  <span class="rounded-full bg-slate-800 px-3 py-1 text-xs uppercase tracking-[0.18em] text-slate-300">{{ item.transacciones }} transacciones</span>
                </div>
              </div>
              <div class="rounded-3xl border border-slate-800 bg-slate-950/80 p-5">
                <p class="text-sm text-slate-400">Total consolidado</p>
                <p class="mt-3 text-4xl font-extrabold text-white">{{ formatCurrency(cierre.total_neto || 0) }}</p>
              </div>
            </div>
          </div>

          <div class="rounded-3xl border border-slate-800 bg-slate-900/95 p-6 shadow-xl">
            <h3 class="text-xl font-semibold text-white mb-4">Validaciones de caja</h3>
            <div class="space-y-4 text-sm text-slate-300">
              <p>• Verifica comprobantes QR y transferencias antes de cerrar.</p>
              <p>• El cierre solo consolida pagos del día actual.</p>
              <p>• Los importes de caja quedan registrados en auditoría para trazabilidad.</p>
            </div>
          </div>
        </section>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { getCierreCaja, closeCaja } from '@/services/adminService'

const router = useRouter()
const authStore = useAuthStore()
const cierre = ref({})
const loading = ref(false)
const closing = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const pagoTipos = computed(() => {
  return cierre.value.pagos_por_tipo || {
    EFECTIVO: { transacciones: 0, total_monto: 0 },
    QR: { transacciones: 0, total_monto: 0 },
    TRANSFERENCIA: { transacciones: 0, total_monto: 0 }
  }
})

const formatCurrency = (value) => {
  return new Intl.NumberFormat('es-PE', {
    style: 'currency',
    currency: 'PEN',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(Number(value || 0))
}

const formatDate = (value) => {
  if (!value) return 'Hoy'
  try {
    return new Date(value).toLocaleDateString('es-ES', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    })
  } catch {
    return value
  }
}

const loadCierreCaja = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const response = await getCierreCaja()
    cierre.value = response.data
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'No se pudo obtener el cierre de caja.'
  } finally {
    loading.value = false
  }
}

const handleCloseCaja = async () => {
  closing.value = true
  errorMessage.value = ''
  successMessage.value = ''
  try {
    const response = await closeCaja()
    cierre.value = response.data.cierre
    successMessage.value = response.data.message || 'Cierre de caja ejecutado correctamente.'
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Error al ejecutar el cierre de caja.'
  } finally {
    closing.value = false
  }
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

onMounted(() => {
  loadCierreCaja()
})
</script>
