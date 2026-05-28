<template>
  <div class="min-h-screen bg-gradient-to-tr from-slate-900 via-indigo-950 to-slate-900 text-slate-100 p-6 md:p-8">
    <div class="max-w-6xl mx-auto">
      
      <!-- HEADER -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
          <router-link to="/client" class="text-sm text-indigo-400 hover:text-indigo-300 transition flex items-center gap-1 mb-2">
            ← Volver al Dashboard
          </router-link>
          <h1 class="text-3xl font-extrabold bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
            Mis Citas y Reservas
          </h1>
          <p class="text-slate-400 mt-1">Solicita citas y administra el historial estético de tus mascotas.</p>
        </div>
        <button
          @click="showRequestModal = true"
          class="bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-500/20 transform hover:-translate-y-0.5 transition active:translate-y-0 flex items-center gap-2"
        >
          📅 Solicitar Nueva Cita
        </button>
      </div>

      <!-- MAIN CONTENT -->
      <div class="grid grid-cols-1 gap-8">
        
        <!-- UPCOMING & HISTORICAL APPOINTMENTS -->
        <div class="bg-slate-900/50 backdrop-blur-xl border border-slate-800 rounded-2xl p-6">
          <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
            <span>🐾</span> Historial de Citas
          </h2>

          <div v-if="loadingAppointments" class="text-center py-12 text-slate-400">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-400 mx-auto mb-3"></div>
            Cargando citas...
          </div>

          <div v-else-if="!citas.length" class="text-center py-12 text-slate-400 border border-dashed border-slate-800 rounded-xl">
            <p class="text-lg font-medium mb-1">No tienes citas registradas</p>
            <p class="text-sm text-slate-500 mb-4">¡Reserva un espacio para mimar a tu mascota!</p>
            <button @click="showRequestModal = true" class="bg-indigo-600/20 text-indigo-300 border border-indigo-500/30 px-4 py-2 rounded-lg hover:bg-indigo-600/30 transition text-sm">
              Solicitar ahora
            </button>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-slate-800 text-slate-400 text-sm font-semibold">
                  <th class="py-3 px-4">Fecha / Hora</th>
                  <th class="py-3 px-4">Mascota</th>
                  <th class="py-3 px-4">Servicio</th>
                  <th class="py-3 px-4">Groomer</th>
                  <th class="py-3 px-4">Estado</th>
                  <th class="py-3 px-4 text-right">Acción</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/50">
                <tr v-for="cita in citas" :key="cita.id" class="hover:bg-slate-800/20 transition-colors">
                  <td class="py-4 px-4">
                    <div class="font-medium">{{ formatDate(cita.fecha) }}</div>
                    <div class="text-xs text-indigo-400">{{ formatTime(cita.hora_inicio) }} - {{ formatTime(cita.hora_fin) }}</div>
                  </td>
                  <td class="py-4 px-4">
                    <span class="font-medium text-slate-200">{{ cita.pet?.nombre }}</span>
                    <span class="text-xs block text-slate-400">{{ cita.pet?.especie }} ({{ cita.pet?.tamanio }})</span>
                  </td>
                  <td class="py-4 px-4">
                    <div>{{ cita.service?.nombre }}</div>
                    <div class="text-xs text-slate-500">{{ cita.service?.precio_base }} BOB</div>
                  </td>
                  <td class="py-4 px-4 text-slate-300">
                    {{ cita.groomer?.nombre_completo || 'Cualquier Groomer' }}
                  </td>
                  <td class="py-4 px-4">
                    <span :class="getStatusClass(cita.estado)" class="px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wider">
                      {{ getStatusLabel(cita.estado) }}
                    </span>
                  </td>
                  <td class="py-4 px-4 text-right">
                    <button
                      v-if="cita.estado === 'PENDIENTE_CONFIRMACION' || cita.estado === 'PROGRAMADO'"
                      @click="openCancelModal(cita)"
                      class="text-xs bg-red-950/40 text-red-400 border border-red-900/30 px-3 py-1.5 rounded-lg hover:bg-red-900/40 transition active:scale-95"
                    >
                      Anular
                    </button>
                    <span v-else class="text-xs text-slate-500">-</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>

    <!-- REQUEST APPOINTMENT MODAL -->
    <div v-if="showRequestModal" class="fixed inset-0 bg-black/70 backdrop-blur-md flex items-center justify-center p-4 z-50 animate-fade-in">
      <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 md:p-8 shadow-2xl shadow-indigo-500/10">
        
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
            Solicitar Nueva Cita
          </h2>
          <button @click="closeRequestModal" class="text-slate-400 hover:text-slate-200 text-xl transition">✕</button>
        </div>

        <form @submit.prevent="handleRequestSubmit" class="space-y-6">
          
          <!-- STEP 1: SELECT PET & SERVICE -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">Selecciona tu Mascota *</label>
              <select
                v-model="form.mascota_id"
                required
                @change="onFormChange"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-indigo-500 transition"
              >
                <option value="" disabled>Selecciona una mascota...</option>
                <option v-for="pet in pets" :key="pet.id" :value="pet.id">
                  {{ pet.nombre }} ({{ pet.especie }} - {{ pet.tamanio }})
                </option>
              </select>
              <p v-if="!pets.length" class="text-xs text-indigo-400 mt-1">
                Primero debes registrar una mascota en tu perfil.
              </p>
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">Tipo de Servicio *</label>
              <select
                v-model="form.servicio_id"
                required
                @change="onFormChange"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-indigo-500 transition"
              >
                <option value="" disabled>Selecciona un servicio...</option>
                <option v-for="serv in services" :key="serv.id" :value="serv.id">
                  {{ serv.nombre }} ({{ serv.precio_base }} BOB)
                </option>
              </select>
            </div>
          </div>

          <!-- STEP 2: SELECT DATE -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">Fecha del Servicio *</label>
              <input
                v-model="form.fecha"
                type="date"
                required
                :min="minDate"
                @change="onFormChange"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-indigo-500 transition"
              />
            </div>
            
            <div class="flex flex-col justify-end text-slate-400 text-sm">
              <div v-if="form.fecha && form.servicio_id && form.mascota_id" class="bg-indigo-950/20 border border-indigo-900/30 rounded-xl p-3">
                <p class="font-medium text-slate-300">Duración estimada:</p>
                <p class="text-indigo-400 font-semibold text-base mt-0.5">
                  {{ estimatedDuration ? estimatedDuration + ' minutos' : 'Calculando...' }}
                </p>
              </div>
            </div>
          </div>

          <!-- STEP 3: AVAILABLE SLOTS -->
          <div class="mt-4">
            <h3 class="text-sm font-semibold text-slate-300 mb-3">Horarios Disponibles por Groomer</h3>
            
            <div v-if="loadingSlots" class="text-center py-6 text-indigo-400">
              <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-400 mx-auto mb-2"></div>
              Calculando disponibilidad de la agenda...
            </div>

            <div v-else-if="!form.fecha || !form.servicio_id || !form.mascota_id" class="text-slate-500 text-sm italic py-6 text-center border border-slate-800 rounded-xl">
              Selecciona mascota, servicio y fecha para calcular los horarios libres.
            </div>

            <div v-else-if="!slotsResponse.length" class="text-red-400 text-sm py-6 text-center border border-red-950/30 bg-red-950/5 rounded-xl">
              ⚠️ No hay slots de tiempo disponibles para esta combinación de fecha y servicio. Intenta con otra fecha.
            </div>

            <div v-else class="space-y-4 max-h-[30vh] overflow-y-auto pr-2">
              <div v-for="grm in slotsResponse" :key="grm.groomer_id" class="border border-slate-800 rounded-xl p-4 bg-slate-950/30">
                <p class="text-sm font-bold text-slate-300 mb-2.5 flex items-center gap-2">
                  <span>✂️</span> {{ grm.nombre_completo }}
                </p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                  <button
                    v-for="slot in grm.slots"
                    :key="slot.hora_inicio"
                    type="button"
                    @click="selectSlot(slot, grm)"
                    :class="[
                      form.hora_inicio === slot.hora_inicio && form.groomer_id === grm.groomer_id
                        ? 'bg-indigo-600 text-white border-indigo-500'
                        : 'bg-slate-900 border-slate-800 text-slate-300 hover:border-indigo-500/50 hover:bg-slate-800/40'
                    ]"
                    class="border text-xs py-2 px-3 rounded-lg text-center font-medium transition active:scale-95"
                  >
                    {{ formatTime(slot.hora_inicio) }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- ACTIONS -->
          <div class="flex justify-end gap-4 pt-4 border-t border-slate-800">
            <button
              type="button"
              @click="closeRequestModal"
              class="px-5 py-2.5 rounded-xl border border-slate-800 text-slate-400 hover:bg-slate-800/50 transition font-medium"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="loadingSubmit || !form.hora_inicio"
              class="bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold px-6 py-2.5 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed transform hover:-translate-y-0.5 transition active:translate-y-0"
            >
              {{ loadingSubmit ? 'Enviando...' : 'Confirmar Reserva' }}
            </button>
          </div>

        </form>
      </div>
    </div>

    <!-- ANULLATION MODAL -->
    <div v-if="showCancelModal" class="fixed inset-0 bg-black/80 backdrop-blur-md flex items-center justify-center p-4 z-50">
      <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
        <h2 class="text-xl font-bold text-slate-100 mb-2">Anular Cita</h2>
        <p class="text-sm text-slate-400 mb-4">
          ¿Estás seguro de que deseas anular tu cita programada? Esta acción no se puede deshacer y liberará tu horario.
        </p>

        <form @submit.prevent="handleCancelSubmit" class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-1.5">Motivo de la Cancelación *</label>
            <textarea
              v-model="cancelForm.motivo_cancelacion"
              required
              rows="3"
              placeholder="Indica el motivo de la anulación (mínimo 5 caracteres)..."
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
              :disabled="loadingCancel || cancelForm.motivo_cancelacion.trim().length < 5"
              class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition text-sm"
            >
              {{ loadingCancel ? 'Anulando...' : 'Confirmar Anulación' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { getPetsRequest } from '@/services/petService'
import { getPublicServices } from '@/services/serviceService'
import { getDisponibilidad, getCitas, createCita, cancelarCita } from '@/services/appointmentService'

const pets = ref([])
const services = ref([])
const citas = ref([])

const loadingAppointments = ref(false)
const loadingSlots = ref(false)
const loadingSubmit = ref(false)
const loadingCancel = ref(false)

const showRequestModal = ref(false)
const showCancelModal = ref(false)
const selectedCitaToCancel = ref(null)

const estimatedDuration = ref(0)
const slotsResponse = ref([])

const minDate = computed(() => {
  const tomorrow = new Date()
  tomorrow.setDate(tomorrow.getDate() + 1)
  return tomorrow.toISOString().split('T')[0]
})

const form = reactive({
  mascota_id: '',
  servicio_id: '',
  fecha: '',
  groomer_id: '',
  hora_inicio: '',
  hora_fin: ''
})

const cancelForm = reactive({
  motivo_cancelacion: ''
})

onMounted(async () => {
  await fetchAppointments()
  await fetchPets()
  await fetchServices()
})

const fetchAppointments = async () => {
  loadingAppointments.value = true
  try {
    const res = await getCitas()
    citas.value = res.data.citas
  } catch (error) {
    console.error('Error fetching appointments:', error)
  } finally {
    loadingAppointments.value = false
  }
}

const fetchPets = async () => {
  try {
    const res = await getPetsRequest()
    pets.value = res.data.pets
  } catch (error) {
    console.error('Error fetching pets:', error)
  }
}

const fetchServices = async () => {
  try {
    const res = await getPublicServices()
    services.value = res.data.services
  } catch (error) {
    console.error('Error fetching services:', error)
  }
}

const onFormChange = async () => {
  // Reset selected slot
  form.hora_inicio = ''
  form.hora_fin = ''
  form.groomer_id = ''
  estimatedDuration.value = 0
  slotsResponse.value = []

  if (form.fecha && form.servicio_id && form.mascota_id) {
    loadingSlots.value = true
    try {
      const res = await getDisponibilidad(form.fecha, form.servicio_id, form.mascota_id)
      estimatedDuration.value = res.data.duracion_minutos
      slotsResponse.value = res.data.disponibilidad
    } catch (error) {
      console.error('Error getting slots:', error)
    } finally {
      loadingSlots.value = false
    }
  }
}

const selectSlot = (slot, groomer) => {
  form.hora_inicio = slot.hora_inicio + ':00'
  form.hora_fin = slot.hora_fin + ':00'
  form.groomer_id = groomer.groomer_id
}

const handleRequestSubmit = async () => {
  if (!form.hora_inicio) return

  loadingSubmit.value = true
  try {
    await createCita({
      mascota_id: form.mascota_id,
      servicio_id: form.servicio_id,
      groomer_id: form.groomer_id,
      fecha: form.fecha,
      hora_inicio: form.hora_inicio,
      hora_fin: form.hora_fin
    })
    closeRequestModal()
    await fetchAppointments()
  } catch (error) {
    alert(error.response?.data?.message || 'Error al solicitar la cita')
  } finally {
    loadingSubmit.value = false
  }
}

const closeRequestModal = () => {
  showRequestModal.value = false
  form.mascota_id = ''
  form.servicio_id = ''
  form.fecha = ''
  form.hora_inicio = ''
  form.hora_fin = ''
  form.groomer_id = ''
  estimatedDuration.value = 0
  slotsResponse.value = []
}

// CANCELLATION
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

  loadingCancel.value = true
  try {
    await cancelarCita(selectedCitaToCancel.value.id, cancelForm.motivo_cancelacion)
    closeCancelModal()
    await fetchAppointments()
  } catch (error) {
    alert(error.response?.data?.message || 'Error al anular la cita')
  } finally {
    loadingCancel.value = false
  }
}

// HELPERS
const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
  const date = new Date(dateStr + 'T00:00:00')
  return date.toLocaleDateString('es-ES', options)
}

const formatTime = (timeStr) => {
  if (!timeStr) return ''
  return timeStr.substring(0, 5)
}

const getStatusLabel = (status) => {
  switch (status) {
    case 'PENDIENTE_CONFIRMACION': return 'Pendiente'
    case 'PROGRAMADO': return 'Confirmado'
    case 'EN_PROCESO': return 'En Grooming'
    case 'FINALIZADO': return 'Listo'
    case 'PAGADO': return 'Pagado'
    case 'CANCELADO': return 'Anulado'
    default: return status
  }
}

const getStatusClass = (status) => {
  switch (status) {
    case 'PENDIENTE_CONFIRMACION': return 'bg-purple-950/50 text-purple-300 border border-purple-800/40'
    case 'PROGRAMADO': return 'bg-green-950/50 text-green-300 border border-green-800/40'
    case 'EN_PROCESO': return 'bg-yellow-950/50 text-yellow-300 border border-yellow-800/40'
    case 'FINALIZADO': return 'bg-blue-950/50 text-blue-300 border border-blue-800/40'
    case 'PAGADO': return 'bg-emerald-950/50 text-emerald-300 border border-emerald-800/40'
    case 'CANCELADO': return 'bg-slate-800/60 text-slate-400 border border-slate-700/40'
    default: return 'bg-slate-800 text-slate-200'
  }
}
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.2s ease-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.97); }
  to { opacity: 1; transform: scale(1); }
}
</style>
