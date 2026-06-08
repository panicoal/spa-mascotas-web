<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 p-6 md:p-8">
    <div class="max-w-7xl mx-auto">
      
      <!-- TOP BANNER / NAVIGATION -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
          <router-link to="/staff" class="text-sm text-indigo-400 hover:text-indigo-300 transition flex items-center gap-1 mb-1">
            ← Volver al Dashboard de Personal
          </router-link>
          <h1 class="text-3xl font-extrabold bg-gradient-to-r from-teal-400 via-indigo-400 to-purple-400 bg-clip-text text-transparent">
            Calendario Maestro de Recepción
          </h1>
          <p class="text-slate-400 mt-1">Monitorea turnos, aprueba solicitudes entrantes y gestiona cobranzas.</p>
        </div>

        <div class="flex flex-wrap gap-3">
          <div>
            <label class="block text-xs text-slate-400 font-semibold mb-1">Fecha de visualización</label>
            <input
              v-model="selectedDate"
              type="date"
              @change="fetchData"
              class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-slate-200 text-sm focus:outline-none focus:border-teal-500 transition"
            />
          </div>
        </div>
      </div>

      <!-- MAIN CONTROLS GRID -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- LEFT COLUMN: SOLICITUDES PENDIENTES TRAY (4/12) -->
        <div class="lg:col-span-4 bg-slate-900/40 backdrop-blur-xl border border-slate-900 rounded-2xl p-5 flex flex-col h-[75vh]">
          <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-800/60">
            <h2 class="text-lg font-bold flex items-center gap-2 text-slate-200">
              <span class="text-indigo-400">📥</span> Solicitudes Pendientes
            </h2>
            <span class="bg-indigo-600/30 text-indigo-300 px-2 py-0.5 text-xs rounded-full font-bold border border-indigo-500/20">
              {{ pendingAppointments.length }}
            </span>
          </div>

          <div v-if="loading" class="text-center py-12 text-slate-400 flex-1">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-400 mx-auto mb-3"></div>
            Cargando solicitudes...
          </div>

          <div v-else-if="!pendingAppointments.length" class="text-slate-500 text-sm italic py-12 text-center border border-dashed border-slate-800/80 rounded-xl flex-1 flex flex-col justify-center">
            No hay solicitudes pendientes de aprobación por el momento.
          </div>

          <div v-else class="space-y-4 overflow-y-auto flex-1 pr-2">
            <div
              v-for="cita in pendingAppointments"
              :key="cita.id"
              class="bg-slate-900 border border-slate-800/60 rounded-xl p-4 hover:border-indigo-500/40 transition-all flex flex-col gap-3"
            >
              <div class="flex justify-between items-start">
                <div>
                  <h3 class="font-bold text-slate-200 text-sm">
                    {{ cita.pet?.nombre }} <span class="text-xs text-slate-400">({{ cita.pet?.especie }})</span>
                  </h3>
                  <p class="text-xs text-indigo-400 font-medium">Dueño: {{ cita.client?.nombre_completo }}</p>
                </div>
                <span class="text-teal-400 text-xs font-bold font-mono">
                  {{ formatTime(cita.hora_inicio) }}
                </span>
              </div>

              <div class="text-xs text-slate-400 bg-slate-950/40 p-2 rounded-lg space-y-1">
                <p><strong>Servicio:</strong> {{ cita.service?.nombre }}</p>
                <p><strong>Groomer:</strong> {{ cita.groomer?.nombre_completo }}</p>
                <p><strong>Fecha propuesta:</strong> {{ formatDate(cita.fecha) }}</p>
              </div>

              <div class="flex gap-2 justify-end">
                <button
                  @click="openCancelModal(cita)"
                  class="bg-red-950/40 text-red-400 border border-red-900/30 hover:bg-red-900/40 text-xs px-3 py-1.5 rounded-lg font-medium transition"
                >
                  Rechazar
                </button>
                <button
                  @click="approveAppointment(cita.id)"
                  class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs px-3 py-1.5 rounded-lg font-medium transition"
                >
                  ✓ Confirmar
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT COLUMN: MASTER CALENDAR BY GROOMER (8/12) -->
        <div class="lg:col-span-8 bg-slate-900/40 backdrop-blur-xl border border-slate-900 rounded-2xl p-5 flex flex-col h-[75vh]">
          <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-800/60">
            <h2 class="text-lg font-bold flex items-center gap-2 text-slate-200">
              <span class="text-teal-400">📅</span> Agenda Diaria por Groomer
            </h2>
            <div class="text-xs text-slate-400 font-semibold bg-slate-900/60 px-3 py-1.5 rounded-lg border border-slate-800">
              {{ formatDate(selectedDate) }}
            </div>
          </div>

          <div v-if="loading" class="text-center py-20 text-slate-400 flex-1">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-teal-400 mx-auto mb-3"></div>
            Cargando agenda diaria...
          </div>

          <div v-else-if="!activeAppointments.length" class="text-slate-500 text-sm italic py-20 text-center border border-dashed border-slate-800/80 rounded-xl flex-1 flex flex-col justify-center">
            No hay citas confirmadas ni bloqueos de agenda programados para esta fecha.
          </div>

          <!-- MASTER AGENDA GRID VIEW -->
          <div v-else class="overflow-y-auto flex-1 pr-2 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div
                v-for="cita in activeAppointments"
                :key="cita.id"
                class="border rounded-xl p-4 bg-slate-900/80 hover:bg-slate-900 transition flex flex-col gap-3"
                :class="[
                  cita.estado === 'FINALIZADO' ? 'border-blue-800/50 hover:border-blue-500/60' :
                  cita.estado === 'EN_PROCESO' ? 'border-yellow-800/50 hover:border-yellow-500/60' :
                  cita.estado === 'PAGADO' ? 'border-emerald-800/50 hover:border-emerald-500/60' : 'border-slate-800/60 hover:border-slate-600'
                ]"
              >
                <div class="flex justify-between items-start">
                  <div>
                    <h3 class="font-bold text-slate-200 text-sm flex items-center gap-1.5">
                      <span>🐶</span> {{ cita.pet?.nombre }}
                    </h3>
                    <p class="text-xs text-slate-400">{{ cita.client?.nombre_completo }}</p>
                  </div>
                  <span :class="getStatusClass(cita.estado)" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase">
                    {{ getStatusLabel(cita.estado) }}
                  </span>
                </div>

                <div class="text-xs text-slate-400 space-y-1 py-1 px-2.5 rounded-lg bg-slate-950/40">
                  <p><strong>Groomer:</strong> {{ cita.groomer?.nombre_completo }}</p>
                  <p><strong>Servicio:</strong> {{ cita.service?.nombre }}</p>
                  <p><strong>Horario:</strong> {{ formatTime(cita.hora_inicio) }} - {{ formatTime(cita.hora_fin) }}</p>
                </div>

                <div class="flex gap-2 justify-end pt-1">
                  <!-- Reschedule Trigger -->
                  <button
                    v-if="cita.estado === 'PROGRAMADO'"
                    @click="openRescheduleModal(cita)"
                    class="bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700 text-xs px-2.5 py-1.5 rounded-lg font-medium transition"
                  >
                    Reprogramar
                  </button>

                  <!-- Checkout Trigger -->
                  <button
                    v-if="cita.estado === 'FINALIZADO'"
                    @click="openCheckoutModal(cita)"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1.5 rounded-lg font-bold transition shadow-lg shadow-blue-500/10 active:scale-95"
                  >
                    💳 Cobrar y Cerrar
                  </button>
                  <span v-if="cita.estado === 'PAGADO'" class="text-xs text-emerald-400 font-semibold py-1">
                    ✓ Pagado y Archivado
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- RESCHEDULE MODAL -->
    <div v-if="showRescheduleModal" class="fixed inset-0 bg-black/85 backdrop-blur-md flex items-center justify-center p-4 z-50">
      <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl">
        <h2 class="text-xl font-bold text-slate-100 mb-4 bg-gradient-to-r from-teal-400 to-indigo-400 bg-clip-text text-transparent">
          Reprogramar Cita
        </h2>

        <form @submit.prevent="handleRescheduleSubmit" class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-1.5">Nueva Fecha *</label>
            <input
              v-model="rescheduleForm.fecha"
              type="date"
              required
              :min="minDate"
              @change="onRescheduleChange"
              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 focus:outline-none focus:border-teal-500 transition text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">Selecciona un Horario Libre *</label>
            
            <div v-if="loadingSlots" class="text-center py-4 text-teal-400">
              <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-teal-400 mx-auto mb-2"></div>
              Buscando slots disponibles...
            </div>

            <div v-else-if="!rescheduleForm.fecha" class="text-slate-500 text-xs italic text-center py-4 border border-slate-800 rounded-xl">
              Selecciona una fecha para ver disponibilidad.
            </div>

            <div v-else-if="!slotsResponse.length" class="text-red-400 text-xs py-4 text-center border border-red-950/30 bg-red-950/5 rounded-xl">
              No hay disponibilidad para esta fecha. Intenta con otra.
            </div>

            <div v-else class="space-y-3 max-h-[25vh] overflow-y-auto pr-2">
              <div v-for="grm in slotsResponse" :key="grm.groomer_id" class="border border-slate-800/80 rounded-xl p-3 bg-slate-950/20">
                <p class="text-xs font-bold text-slate-400 mb-2">✂️ {{ grm.nombre_completo }}</p>
                <div class="grid grid-cols-3 gap-1.5">
                  <button
                    v-for="slot in grm.slots"
                    :key="slot.hora_inicio"
                    type="button"
                    @click="selectRescheduleSlot(slot, grm)"
                    :class="[
                      rescheduleForm.hora_inicio === slot.hora_inicio && rescheduleForm.groomer_id === grm.groomer_id
                        ? 'bg-teal-600 text-white border-teal-500'
                        : 'bg-slate-900 border-slate-800/60 text-slate-300 hover:border-teal-500/50 text-[10px]'
                    ]"
                    class="border py-1.5 px-2 rounded text-center transition"
                  >
                    {{ formatTime(slot.hora_inicio) }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
            <button
              type="button"
              @click="closeRescheduleModal"
              class="px-4 py-2 rounded-lg border border-slate-800 text-slate-400 hover:bg-slate-800/30 transition text-sm"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="loadingSubmit || !rescheduleForm.hora_inicio"
              class="bg-teal-600 hover:bg-teal-700 text-white font-semibold px-4 py-2 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition text-sm"
            >
              {{ loadingSubmit ? 'Guardando...' : 'Guardar Cambios' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- CHECKOUT & COBRO MODAL -->
    <div v-if="showCheckoutModal" class="fixed inset-0 bg-black/85 backdrop-blur-md flex items-center justify-center p-4 z-50 animate-fade-in">
      <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
        <h2 class="text-xl font-bold text-slate-100 mb-4 bg-gradient-to-r from-teal-400 to-indigo-400 bg-clip-text text-transparent">
          Caja - Punto de Cobro
        </h2>

        <div v-if="selectedCita" class="space-y-4 mb-6">
          <div class="bg-slate-950 p-4 rounded-xl space-y-2 border border-slate-800/60">
            <p class="text-xs text-slate-400">Detalle del Servicio:</p>
            <div class="text-sm">
              <p class="font-bold text-slate-200">{{ selectedCita.service?.nombre }}</p>
              <p class="text-xs text-slate-400">Mascota: <span class="font-medium text-slate-300">{{ selectedCita.pet?.nombre }}</span></p>
              <p class="text-xs text-slate-400">Dueño: <span class="font-medium text-slate-300">{{ selectedCita.client?.nombre_completo }}</span></p>
            </div>
            <div class="flex justify-between items-center pt-2 border-t border-slate-800/80 mt-2">
              <span class="text-sm font-semibold text-slate-300">Monto a Cobrar:</span>
              <span class="text-lg font-black text-teal-400">{{ selectedCita.service?.precio_base }} BOB</span>
            </div>
          </div>

          <form @submit.prevent="handleCheckoutSubmit" class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-1.5">Método de Pago *</label>
              <select
                v-model="checkoutForm.metodo_pago"
                required
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 focus:outline-none focus:border-teal-500 transition text-sm"
              >
                <option value="EFECTIVO">Efectivo</option>
                <option value="QR">Código QR</option>
                <option value="TRANSFERENCIA">Transferencia Bancaria</option>
              </select>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
              <button
                type="button"
                @click="closeCheckoutModal"
                class="px-4 py-2 rounded-lg border border-slate-800 text-slate-400 hover:bg-slate-800/30 transition text-sm"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="loadingSubmit"
                class="bg-gradient-to-r from-teal-500 to-indigo-600 hover:from-teal-600 hover:to-indigo-700 text-white font-bold px-5 py-2 rounded-lg transition text-sm active:scale-95"
              >
                {{ loadingSubmit ? 'Procesando...' : 'Confirmar Cobro' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- VISUAL PRINTABLE RECEIPT MODAL -->
    <div v-if="showReceiptModal && receiptData" class="fixed inset-0 bg-black/90 backdrop-blur-md flex items-center justify-center p-4 z-50 animate-fade-in">
      <div class="bg-white text-slate-900 border border-slate-300 rounded-xl w-full max-w-sm p-6 shadow-2xl flex flex-col gap-4 font-sans relative overflow-hidden">
        
        <!-- PRINT STYLING ORNAMENT -->
        <div class="border-t-4 border-teal-500 absolute top-0 left-0 right-0"></div>

        <div class="text-center pb-2 border-b border-dashed border-slate-300 mt-2">
          <h2 class="text-xl font-black text-slate-800 tracking-wider">PET SPA S.R.L.</h2>
          <p class="text-[10px] text-slate-500 uppercase">Servicios de Grooming y Estética Animal</p>
          <p class="text-[9px] text-slate-400 mt-0.5">Bolivia - Santa Cruz de la Sierra</p>
        </div>

        <div class="text-[11px] text-slate-600 space-y-1">
          <p><strong>Nº Factura/Recibo:</strong> {{ receiptData.id.substring(0,8).toUpperCase() }}</p>
          <p><strong>Fecha/Hora:</strong> {{ formatDateTime(receiptData.updated_at) }}</p>
          <p><strong>Método de Pago:</strong> {{ checkoutForm.metodo_pago }}</p>
          <p><strong>Cliente:</strong> {{ receiptData.client?.nombre_completo }}</p>
          <p><strong>Mascota:</strong> {{ receiptData.pet?.nombre }} ({{ receiptData.pet?.especie }})</p>
        </div>

        <div class="border-t border-b border-slate-300 py-2 my-1 text-xs">
          <div class="flex justify-between font-bold text-slate-800 mb-1">
            <span>Descripción</span>
            <span>Subtotal</span>
          </div>
          <div class="flex justify-between text-slate-600 text-[11px]">
            <span>{{ receiptData.service?.nombre }}</span>
            <span>{{ receiptData.service?.precio_base }} BOB</span>
          </div>
        </div>

        <div class="flex justify-between items-center text-sm font-bold text-slate-900">
          <span>TOTAL PAGADO</span>
          <span class="text-base font-black text-teal-600">{{ receiptData.service?.precio_base }} BOB</span>
        </div>

        <div class="text-center text-[10px] text-slate-400 border-t border-dashed border-slate-200 pt-3 mt-1">
          <p>¡Gracias por confiar en nosotros!</p>
          <p>Tu mascota siempre está en las mejores manos.</p>
        </div>

        <div class="flex gap-2 mt-2">
          <button
            @click="closeReceiptModal"
            class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold py-2.5 rounded-lg text-xs transition text-center"
          >
            Cerrar Recibo
          </button>
        </div>
      </div>
    </div>

    <!-- ANULLATION MODAL -->
    <div v-if="showCancelModal" class="fixed inset-0 bg-black/80 backdrop-blur-md flex items-center justify-center p-4 z-50">
      <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
        <h2 class="text-xl font-bold text-slate-100 mb-2">Rechazar Solicitud de Cita</h2>
        <p class="text-sm text-slate-400 mb-4">
          Indica el motivo de la anulación para notificar al cliente. Esta acción liberará el espacio de tiempo.
        </p>

        <form @submit.prevent="handleCancelSubmit" class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-1.5">Motivo *</label>
            <textarea
              v-model="cancelForm.motivo_cancelacion"
              required
              rows="3"
              placeholder="Indica el motivo de la anulación..."
              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 focus:outline-none focus:border-red-500 transition text-sm"
            ></textarea>
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <button
              type="button"
              @click="closeCancelModal"
              class="px-4 py-2 rounded-lg border border-slate-800 text-slate-400 hover:bg-slate-800/30 transition text-sm"
            >
              Cerrar
            </button>
            <button
              type="submit"
              :disabled="loadingSubmit"
              class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg transition text-sm"
            >
              Confirmar Rechazo
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { getCitas, confirmarCita, reprogramarCita, cancelarCita, pagarCita, getDisponibilidad } from '@/services/appointmentService'

const selectedDate = ref(new Date().toISOString().split('T')[0])
const allAppointments = ref([])
const loading = ref(false)
const loadingSlots = ref(false)
const loadingSubmit = ref(false)

const showRescheduleModal = ref(false)
const showCheckoutModal = ref(false)
const showReceiptModal = ref(false)
const showCancelModal = ref(false)

const selectedCita = ref(null)
const selectedCitaToCancel = ref(null)
const receiptData = ref(null)

const slotsResponse = ref([])

const minDate = computed(() => {
  return new Date().toISOString().split('T')[0]
})

const rescheduleForm = reactive({
  fecha: '',
  groomer_id: '',
  hora_inicio: '',
  hora_fin: ''
})

const checkoutForm = reactive({
  metodo_pago: 'EFECTIVO',
  monto: 0
})

const cancelForm = reactive({
  motivo_cancelacion: ''
})

const pendingAppointments = computed(() => {
  return allAppointments.value.filter(c => c.estado === 'PENDIENTE_CONFIRMACION')
})

const activeAppointments = computed(() => {
  return allAppointments.value.filter(c => c.estado !== 'PENDIENTE_CONFIRMACION')
})

onMounted(async () => {
  await fetchData()
})

const fetchData = async () => {
  loading.value = true
  try {
    const res = await getCitas({ fecha: selectedDate.value })
    allAppointments.value = res.data.citas || []
  } catch (error) {
    console.error('Error fetching appointments:', error)
  } finally {
    loading.value = false
  }
}

// APPROVAL
const approveAppointment = async (id) => {
  try {
    await confirmarCita(id)
    await fetchData()
  } catch (error) {
    alert(error.response?.data?.message || 'Error al confirmar la cita')
  }
}

// RE-SCHEDULING
const openRescheduleModal = (cita) => {
  selectedCita.value = cita
  rescheduleForm.fecha = cita.fecha
  rescheduleForm.groomer_id = cita.groomer_id
  rescheduleForm.hora_inicio = cita.hora_inicio
  rescheduleForm.hora_fin = cita.hora_fin
  slotsResponse.value = []
  showRescheduleModal.value = true
  onRescheduleChange()
}

const closeRescheduleModal = () => {
  showRescheduleModal.value = false
  selectedCita.value = null
}

const onRescheduleChange = async () => {
  if (rescheduleForm.fecha && selectedCita.value) {
    loadingSlots.value = true
    try {
      const res = await getDisponibilidad(
        rescheduleForm.fecha,
        selectedCita.value.servicio_id,
        selectedCita.value.mascota_id
      )
      slotsResponse.value = res.data.disponibilidad || []
    } catch (error) {
      console.error(error)
    } finally {
      loadingSlots.value = false
    }
  }
}

const selectRescheduleSlot = (slot, groomer) => {
  rescheduleForm.hora_inicio = slot.hora_inicio + ':00'
  rescheduleForm.hora_fin = slot.hora_fin + ':00'
  rescheduleForm.groomer_id = groomer.groomer_id
}

const handleRescheduleSubmit = async () => {
  if (!selectedCita.value || !rescheduleForm.hora_inicio) return

  loadingSubmit.value = true
  try {
    await reprogramarCita(selectedCita.value.id, {
      fecha: rescheduleForm.fecha,
      hora_inicio: rescheduleForm.hora_inicio,
      hora_fin: rescheduleForm.hora_fin,
      groomer_id: rescheduleForm.groomer_id
    })
    closeRescheduleModal()
    await fetchData()
  } catch (error) {
    alert(error.response?.data?.message || 'Error al reprogramar')
  } finally {
    loadingSubmit.value = false
  }
}

// COBRO / CHECKOUT
const openCheckoutModal = (cita) => {
  selectedCita.value = cita
  checkoutForm.metodo_pago = 'EFECTIVO'
  checkoutForm.monto = parseFloat(cita.service?.precio_base || 0)
  showCheckoutModal.value = true
}

const closeCheckoutModal = () => {
  showCheckoutModal.value = false
  selectedCita.value = null
}

const handleCheckoutSubmit = async () => {
  if (!selectedCita.value) return

  loadingSubmit.value = true
  try {
    const res = await pagarCita(selectedCita.value.id, {
      metodo_pago: checkoutForm.metodo_pago,
      monto: checkoutForm.monto
    })
    receiptData.value = res.data.cita
    showCheckoutModal.value = false
    showReceiptModal.value = true
  } catch (error) {
    alert(error.response?.data?.message || 'Error al cobrar')
  } finally {
    loadingSubmit.value = false
  }
}

const closeReceiptModal = () => {
  showReceiptModal.value = false
  receiptData.value = null
  selectedCita.value = null
  fetchData()
}

// REJECT / CANCEL
const openCancelModal = (cita) => {
  selectedCitaToCancel.value = cita
  cancelForm.motivo_cancelacion = ''
  showCancelModal.value = true
}

const closeCancelModal = () => {
  showCancelModal.value = false
  selectedCitaToCancel.value = null
}

const handleCancelSubmit = async () => {
  if (!selectedCitaToCancel.value) return

  loadingSubmit.value = true
  try {
    await cancelarCita(selectedCitaToCancel.value.id, cancelForm.motivo_cancelacion)
    closeCancelModal()
    await fetchData()
  } catch (error) {
    alert(error.response?.data?.message || 'Error al rechazar cita')
  } finally {
    loadingSubmit.value = false
  }
}

// HELPERS
const formatDate = (dateStr) => {
  if (!dateStr) return ''
  try {
    // Handle various date formats from backend
    let date
    if (dateStr.includes('T')) {
      date = new Date(dateStr)
    } else if (dateStr.length === 10) {
      date = new Date(dateStr + 'T00:00:00')
    } else {
      date = new Date(dateStr)
    }
    
    if (isNaN(date.getTime())) {
      return 'Fecha inválida'
    }
    
    const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' }
    return date.toLocaleDateString('es-ES', options)
  } catch (error) {
    console.error('Error formatting date:', dateStr, error)
    return 'Fecha inválida'
  }
}

const formatDateTime = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleString('es-ES')
}

const formatTime = (timeStr) => {
  if (!timeStr) return ''
  return timeStr.substring(0, 5)
}

const getStatusLabel = (status) => {
  switch (status) {
    case 'PROGRAMADO': return 'Confirmado'
    case 'EN_PROCESO': return 'En Grooming'
    case 'FINALIZADO': return 'Listo'
    case 'PAGADO': return 'Cobrado'
    case 'CANCELADO': return 'Anulado'
    default: return status
  }
}

const getStatusClass = (status) => {
  switch (status) {
    case 'PROGRAMADO': return 'bg-green-950/40 text-green-400 border border-green-900/30'
    case 'EN_PROCESO': return 'bg-yellow-950/40 text-yellow-400 border border-yellow-900/30'
    case 'FINALIZADO': return 'bg-blue-950/40 text-blue-400 border border-blue-900/30'
    case 'PAGADO': return 'bg-teal-950/40 text-teal-400 border border-teal-900/30'
    case 'CANCELADO': return 'bg-slate-900 text-slate-400 border border-slate-800'
    default: return 'bg-slate-800 text-slate-200'
  }
}
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.25s ease-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.96); }
  to { opacity: 1; transform: scale(1); }
}
</style>
