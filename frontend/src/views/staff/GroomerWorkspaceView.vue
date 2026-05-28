<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 p-6 md:p-8">
    <div class="max-w-6xl mx-auto">
      
      <!-- TOP BANNER -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
          <router-link to="/staff" class="text-sm text-indigo-400 hover:text-indigo-300 transition flex items-center gap-1 mb-1">
            ← Volver al Dashboard de Personal
          </router-link>
          <h1 class="text-3xl font-extrabold bg-gradient-to-r from-orange-400 via-rose-400 to-indigo-400 bg-clip-text text-transparent">
            Estación de Grooming y Estética
          </h1>
          <p class="text-slate-400 mt-1">Registra diagnósticos, ejecuta checklists de higiene y cierra servicios finalizados.</p>
        </div>
        <!-- <div class="text-xs text-slate-400 bg-slate-900 px-3.5 py-2 rounded-xl border border-slate-800 font-semibold font-mono">
          Hoy: {{ formatDate(todayStr) }}
        </div> -->
        <div class="flex gap-2 mt-4">
          <button
            @click="changeView('dia')"
            :class="viewMode === 'dia'
              ? 'bg-orange-500 text-white'
              : 'bg-slate-900 text-slate-400'"
            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition"
          >
            Día
          </button>

          <button
            @click="changeView('semana')"
            :class="viewMode === 'semana'
              ? 'bg-orange-500 text-white'
              : 'bg-slate-900 text-slate-400'"
            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition"
          >
            Semana
          </button>

          <button
            @click="changeView('mes')"
            :class="viewMode === 'mes'
              ? 'bg-orange-500 text-white'
              : 'bg-slate-900 text-slate-400'"
            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition"
          >
            Mes
          </button>
        </div>

      </div>

      <!-- MAIN CONTROLS GRID -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- LEFT COLUMN: MY AGENDA FOR TODAY (5/12) -->
        <div class="lg:col-span-5 bg-slate-900/40 backdrop-blur-xl border border-slate-900 rounded-2xl p-5 flex flex-col h-[75vh]">
          <h2 class="text-lg font-bold mb-4 pb-2 border-b border-slate-800/60 flex items-center gap-2">
            <div class="flex gap-2 mb-4">
            <button
              @click="changeView('dia')"
              :class="viewMode === 'dia'
                ? 'bg-orange-500 text-white'
                : 'bg-slate-900 text-slate-400 border border-slate-800'"
              class="px-3 py-1.5 rounded-lg text-xs font-semibold transition"
            >
              Día
            </button>

            <button
              @click="changeView('semana')"
              :class="viewMode === 'semana'
                ? 'bg-orange-500 text-white'
                : 'bg-slate-900 text-slate-400 border border-slate-800'"
              class="px-3 py-1.5 rounded-lg text-xs font-semibold transition"
            >
              Semana
            </button>

            <button
              @click="changeView('mes')"
              :class="viewMode === 'mes'
                ? 'bg-orange-500 text-white'
                : 'bg-slate-900 text-slate-400 border border-slate-800'"
              class="px-3 py-1.5 rounded-lg text-xs font-semibold transition"
            >
              Mes
            </button>
          </div>
            <span>✂️</span> Citas Asignadas
          </h2>

          <div v-if="loadingAgenda" class="text-center py-12 text-slate-400 flex-1">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-400 mx-auto mb-3"></div>
            Cargando agenda...
          </div>

          <div v-else-if="!citas.length" class="text-slate-500 text-sm italic py-12 text-center border border-dashed border-slate-800/80 rounded-xl flex-1 flex flex-col justify-center">
            No tienes servicios asignados en este rango.
          </div>

          <div v-else class="space-y-4 overflow-y-auto flex-1 pr-2">
            <div
              v-for="cita in citas"
              :key="cita.id"
              @click="selectAppointment(cita)"
              :class="[
                activeAppointment?.id === cita.id
                  ? 'border-orange-500/60 bg-slate-900 shadow-lg shadow-orange-500/5'
                  : 'border-slate-800/60 bg-slate-950/20 hover:border-slate-700'
              ]"
              class="border rounded-xl p-4 cursor-pointer transition-all flex flex-col gap-2.5"
            >
              <div class="flex justify-between items-start">
                <div>
                  <h3 class="font-bold text-slate-200 text-sm">
                    {{ cita.pet?.nombre }} <span class="text-xs font-normal text-slate-400">({{ cita.pet?.especie }})</span>
                  </h3>
                  <p class="text-xs text-orange-400 font-semibold">{{ cita.service?.nombre }}</p>
                </div>
                <span :class="getStatusClass(cita.estado)" class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider">
                  {{ getStatusLabel(cita.estado) }}
                </span>
              </div>

              <div class="flex justify-between text-xs text-slate-400 pt-1 border-t border-slate-800/40">
                <span>Hora: <strong>{{ formatTime(cita.hora_inicio) }}</strong></span>
                <span>Tamaño: <strong class="text-indigo-400">{{ cita.pet?.tamanio || 'PEQUEÑO' }}</strong></span>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT COLUMN: WORKSPACE INTAKE AND CLOSE (7/12) -->
        <div class="lg:col-span-7 bg-slate-900/40 backdrop-blur-xl border border-slate-900 rounded-2xl p-5 flex flex-col h-[75vh] overflow-y-auto">
          
          <!-- NO APPOINTMENT SELECTED -->
          <div v-if="!activeAppointment" class="text-slate-500 text-sm italic py-20 text-center border border-dashed border-slate-800/60 rounded-xl flex-1 flex flex-col justify-center">
            <span class="text-4xl mb-3">🐶</span>
            Selecciona una cita de la agenda de hoy para abrir la mesa de grooming.
          </div>

          <!-- APPOINTMENT DETAILS & WORKSPACE -->
          <div v-else class="space-y-6">
            
            <!-- CORE HEADER -->
            <div class="flex justify-between items-start pb-4 border-b border-slate-800">
              <div>
                <h2 class="text-xl font-black text-slate-200">
                  Mesa: {{ activeAppointment.pet?.nombre }}
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">
                  Raza: <strong class="text-slate-300">{{ activeAppointment.pet?.raza || 'Común' }}</strong> | 
                  Peso: <strong class="text-slate-300">{{ activeAppointment.pet?.peso || '-' }} kg</strong>
                </p>
              </div>

              <!-- Action buttons depending on state -->
              <button
                v-if="activeAppointment.estado === 'PROGRAMADO'"
                @click="startService(activeAppointment.id)"
                :disabled="loadingSubmit"
                class="bg-gradient-to-r from-orange-500 to-rose-600 hover:from-orange-600 hover:to-rose-700 text-white font-bold px-4 py-2 rounded-xl text-xs transition active:scale-95 flex items-center gap-1.5"
              >
                ✂️ Iniciar Atención
              </button>
            </div>

            <!-- RESTRICTIONS ALERT -->
            <div v-if="activeAppointment.pet?.restricciones_medicas" class="bg-red-950/20 border border-red-900/30 p-3 rounded-xl flex items-start gap-2.5">
              <span class="text-red-400 text-base">⚠️</span>
              <div>
                <p class="text-xs font-bold text-red-300">Restricciones / Alergias Clínicas:</p>
                <p class="text-xs text-red-400/90 mt-0.5">{{ activeAppointment.pet?.restricciones_medicas }}</p>
              </div>
            </div>

            <!-- INTRODUCING INTAKE CARD FOR CITA EN PROCESO -->
            <div v-if="activeAppointment.estado === 'EN_PROCESO'" class="space-y-6">
              <form @submit.prevent="submitTechnicalClosure" class="space-y-5">
                
                <!-- STAGE 1: INTAKE INSPECTION -->
                <div class="bg-slate-950/40 p-4 border border-slate-850 rounded-xl space-y-4">
                  <h3 class="text-xs font-bold text-orange-400 uppercase tracking-wider">1. Inspección de Ingreso</h3>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-xs font-semibold text-slate-300 mb-1.5">Presencia de Nudos *</label>
                      <select
                        v-model="form.estado_ingreso_nudos"
                        required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-200 text-xs focus:outline-none focus:border-orange-500 transition"
                      >
                        <option value="NO">Ninguno</option>
                        <option value="MODERADO">Nudos Leves/Moderados</option>
                        <option value="SEVERO">Nudos Severos / Lana</option>
                      </select>
                    </div>

                    <div>
                      <label class="block text-xs font-semibold text-slate-300 mb-1.5">Pulgas / Parásitos *</label>
                      <select
                        v-model="form.estado_ingreso_pulgas"
                        required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-200 text-xs focus:outline-none focus:border-orange-500 transition"
                      >
                        <option :value="false">No detectados</option>
                        <option :value="true">Sí (Requiere antiparasitario)</option>
                      </select>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-xs font-semibold text-slate-300 mb-1.5">Temperamento observado *</label>
                      <select
                        v-model="form.temperamento"
                        required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-200 text-xs focus:outline-none focus:border-orange-500 transition"
                      >
                        <option value="TRANQUILO">Tranquilo / Dócil</option>
                        <option value="NERVIOSO">Asustadizo / Nervioso</option>
                        <option value="INQUIETO">Hiperactivo / Inquieto</option>
                        <option value="AGRESIVO">Reactivo / Agresivo</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-slate-300 mb-1.5">Tiempo Real Empleado (minutos) *</label>
                      <input
                        v-model.number="form.tiempo_real_minutos"
                        type="number"
                        required
                        min="5"
                        max="480"
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-200 text-xs focus:outline-none focus:border-orange-500 transition"
                      />
                    </div>
                  </div>

                  <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Heridas / Observaciones físicas (Opcional)</label>
                    <textarea
                      v-model="form.estado_ingreso_heridas"
                      rows="2"
                      placeholder="Registra cicatrices, verrugas, raspones o marcas detectadas antes del baño..."
                      class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-200 text-xs focus:outline-none focus:border-orange-500 transition"
                    ></textarea>
                  </div>
                </div>

                <!-- STAGE 2: HYGIENE CHECKLIST (MANDATORY STEPS) -->
                <div class="bg-slate-950/40 p-4 border border-slate-850 rounded-xl space-y-3">
                  <h3 class="text-xs font-bold text-orange-400 uppercase tracking-wider">2. Tareas Realizadas (Checklist Obligatorio)</h3>
                  
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <label class="flex items-center gap-2 cursor-pointer p-2 bg-slate-950 rounded-lg border border-slate-800/40 hover:border-slate-700">
                      <input type="checkbox" v-model="form.checklist" value="Baño profundo" class="rounded border-slate-800 text-orange-500 focus:ring-0" />
                      <span>🧼 Baño Profundo</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer p-2 bg-slate-950 rounded-lg border border-slate-800/40 hover:border-slate-700">
                      <input type="checkbox" v-model="form.checklist" value="Limpieza de oídos" class="rounded border-slate-800 text-orange-500 focus:ring-0" />
                      <span>👂 Limpieza de Oídos</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer p-2 bg-slate-950 rounded-lg border border-slate-800/40 hover:border-slate-700">
                      <input type="checkbox" v-model="form.checklist" value="Corte de uñas" class="rounded border-slate-800 text-orange-500 focus:ring-0" />
                      <span>💅 Corte de Uñas</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer p-2 bg-slate-950 rounded-lg border border-slate-800/40 hover:border-slate-700">
                      <input type="checkbox" v-model="form.checklist" value="Drenaje de glándulas" class="rounded border-slate-800 text-orange-500 focus:ring-0" />
                      <span>🍑 Glándulas Anales</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer p-2 bg-slate-950 rounded-lg border border-slate-800/40 hover:border-slate-700">
                      <input type="checkbox" v-model="form.checklist" value="Corte estilizado" class="rounded border-slate-800 text-orange-500 focus:ring-0" />
                      <span>✂️ Corte Estilizado/Tijera</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer p-2 bg-slate-950 rounded-lg border border-slate-800/40 hover:border-slate-700">
                      <input type="checkbox" v-model="form.checklist" value="Perfume y peinado" class="rounded border-slate-800 text-orange-500 focus:ring-0" />
                      <span>🌸 Perfume y Peinado final</span>
                    </label>
                  </div>
                </div>

                <!-- STAGE 3: SHOUTOUTS AND WRAPUP -->
                <div class="bg-slate-950/40 p-4 border border-slate-850 rounded-xl space-y-4">
                  <h3 class="text-xs font-bold text-orange-400 uppercase tracking-wider">3. Ficha Técnica General y Recomendaciones</h3>
                  
                  <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Condición general detectada *</label>
                    <textarea
                      v-model="form.condicion_general"
                      required
                      rows="3"
                      placeholder="Diagnóstico del pelaje, piel (caspa, dermatitis, etc.) y estado general de salud estética..."
                      class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-200 text-xs focus:outline-none focus:border-orange-500 transition"
                    ></textarea>
                  </div>

                  <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Recomendaciones para el Dueño (Opcional)</label>
                    <textarea
                      v-model="form.recomendaciones_tecnicas"
                      rows="2"
                      placeholder="Indica recomendaciones de cepillado, productos o sugerencia de visita médica al dueño..."
                      class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-200 text-xs focus:outline-none focus:border-orange-500 transition"
                    ></textarea>
                  </div>

                  <!-- SIMULATED PHOTOS UPLOAD -->
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-xs font-semibold text-slate-300 mb-1.5">Fotos de evidencia (Ingreso/Salida)</label>
                      <button
                        type="button"
                        @click="addMockPhoto"
                        class="w-full bg-slate-900 border border-slate-800 hover:border-orange-500/50 py-2.5 rounded-lg text-xs font-medium text-slate-300 flex items-center justify-center gap-1.5 transition"
                      >
                        📸 Cargar Foto Antes / Después
                      </button>
                    </div>
                    <div class="flex items-center gap-2 overflow-x-auto">
                      <div
                        v-for="(photo, index) in form.fotos"
                        :key="index"
                        class="h-10 w-10 border border-slate-800 rounded-lg bg-slate-950 flex items-center justify-center relative group"
                      >
                        <span class="text-lg">🖼️</span>
                        <button
                          type="button"
                          @click="removePhoto(index)"
                          class="absolute -top-1.5 -right-1.5 bg-red-600 text-white rounded-full h-4 w-4 text-[9px] flex items-center justify-center shadow"
                        >✕</button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- SUBMIT ACTION -->
                <button
                  type="submit"
                  :disabled="loadingSubmit || !form.checklist.length"
                  class="w-full bg-gradient-to-r from-orange-500 to-rose-600 hover:from-orange-600 hover:to-rose-700 text-white font-bold py-3.5 rounded-xl transition active:scale-95 shadow-xl shadow-orange-500/10 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 text-sm"
                >
                  🚀 Finalizar Servicio y Notificar Cliente
                </button>

              </form>
            </div>

            <!-- INFO ABOUT ALREADY COMPLETED/PAGADO APPOINTMENTS -->
            <div v-else-if="activeAppointment.estado === 'FINALIZADO' || activeAppointment.estado === 'PAGADO'" class="bg-indigo-950/15 border border-indigo-900/30 p-5 rounded-2xl space-y-4">
              <h3 class="text-sm font-bold text-slate-200 flex items-center gap-2">
                <span>✓</span> Servicio Finalizado con Éxito
              </h3>
              
              <div class="text-xs text-slate-400 space-y-2 bg-slate-950/60 p-4 rounded-xl border border-slate-850">
                <p><strong>Nudos de ingreso:</strong> {{ activeAppointment.grooming_card?.estado_ingreso_nudos || 'NO' }}</p>
                <p><strong>Parásitos de ingreso:</strong> {{ activeAppointment.grooming_card?.estado_ingreso_pulgas ? 'Sí' : 'No' }}</p>
                <p><strong>Diagnóstico general:</strong> {{ activeAppointment.grooming_card?.condicion_general }}</p>
                <p><strong>Checklist técnico completado:</strong></p>
                <div class="flex flex-wrap gap-1.5 mt-1">
                  <span
                    v-for="item in activeAppointment.grooming_card?.checklist"
                    :key="item"
                    class="bg-slate-900 border border-slate-800 text-slate-300 px-2 py-0.5 rounded text-[10px]"
                  >
                    {{ item }}
                  </span>
                </div>
              </div>
            </div>

          </div>

        </div>

      </div>
    </div>

    <!-- CLIENT READY-TO-PICKUP NOTIFICATION MODAL -->
    <div v-if="showNotificationModal && notificationPayload" class="fixed inset-0 bg-black/85 backdrop-blur-md flex items-center justify-center p-4 z-50 animate-fade-in">
      <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-sm p-6 shadow-2xl text-center">
        <span class="text-5xl block mb-4">📲</span>
        <h2 class="text-xl font-bold text-slate-100 mb-2">Notificación Enviada</h2>
        <p class="text-xs text-slate-400 mb-4">
          Se ha enviado un mensaje automático al dueño para informarle que su mascota ya está peinada y lista.
        </p>

        <div class="bg-slate-950 border border-slate-800 text-left p-3.5 rounded-xl text-xs space-y-1 mb-5">
          <p class="text-slate-400">Mensaje enviado a <strong class="text-slate-300">{{ notificationPayload.cliente }}</strong>:</p>
          <p class="text-orange-400 font-medium italic mt-1 font-mono">"{{ notificationPayload.mensaje }}"</p>
        </div>

        <button
          @click="closeNotificationModal"
          class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 rounded-lg text-sm transition"
        >
          Aceptar
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>

import { ref, reactive, onMounted } from 'vue'
import { getGroomerAgenda, iniciarFicha, cerrarFicha } from '@/services/groomingService'

const todayStr = new Date().toISOString().split('T')[0]

const viewMode = ref('dia')
const citas = ref([])
const activeAppointment = ref(null)

const loadingAgenda = ref(false)
const loadingSubmit = ref(false)

const showNotificationModal = ref(false)
const notificationPayload = ref(null)

const form = reactive({
  estado_ingreso_nudos: 'NO',
  estado_ingreso_pulgas: false,
  estado_ingreso_heridas: '',
  temperamento: 'TRANQUILO',
  condicion_general: '',
  recomendaciones_tecnicas: '',
  tiempo_real_minutos: 60,
  checklist: [],
  fotos: []
})

onMounted(async () => {
  await fetchAgenda()
})

const fetchAgenda = async () => {
  loadingAgenda.value = true
  try {
    // const res = await getGroomerAgenda(todayStr)
    const res = await getGroomerAgenda({
      fecha: todayStr,
      vista: viewMode.value
    })
    
    citas.value = res.data.citas || []
    
    // Auto-select first appointment if nothing is selected or update selection
    if (activeAppointment.value) {
      const updated = citas.value.find(c => c.id === activeAppointment.value.id)
      activeAppointment.value = updated || null
    }
  } catch (error) {
    console.error('Error fetching groomer agenda:', error)
  } finally {
    loadingAgenda.value = false
  }
}
const changeView = async (mode) => {
  viewMode.value = mode
  await fetchAgenda()
}

const selectAppointment = (cita) => {
  activeAppointment.value = cita
  
  // Pre-fill form values for EN_PROCESO if they exist
  form.estado_ingreso_nudos = 'NO'
  form.estado_ingreso_pulgas = false
  form.estado_ingreso_heridas = ''
  form.temperamento = cita.pet?.temperamento || 'TRANQUILO'
  form.condicion_general = ''
  form.recomendaciones_tecnicas = ''
  form.tiempo_real_minutos = cita.service?.duracion_base_minutos || 60
  form.checklist = []
  form.fotos = []
}

const startService = async (citaId) => {
  loadingSubmit.value = true
  try {
    const res = await iniciarFicha(citaId)
    await fetchAgenda()
    
    // Select the updated appointment
    const updated = citas.value.find(c => c.id === citaId)
    if (updated) selectAppointment(updated)
  } catch (error) {
    alert(error.response?.data?.message || 'Error al iniciar servicio')
  } finally {
    loadingSubmit.value = false
  }
}

const addMockPhoto = () => {
  const mockUrl = `/uploads/evidence_photo_${Date.now()}.jpg`
  form.fotos.push(mockUrl)
}

const removePhoto = (index) => {
  form.fotos.splice(index, 1)
}

const submitTechnicalClosure = async () => {
  if (!activeAppointment.value || !form.checklist.length) return

  loadingSubmit.value = true
  try {
    const res = await cerrarFicha(activeAppointment.value.id, {
      estado_ingreso_nudos: form.estado_ingreso_nudos,
      estado_ingreso_pulgas: form.estado_ingreso_pulgas,
      estado_ingreso_heridas: form.estado_ingreso_heridas,
      temperamento: form.temperamento,
      condicion_general: form.condicion_general,
      recomendaciones_tecnicas: form.recomendaciones_tecnicas,
      tiempo_real_minutos: form.tiempo_real_minutos,
      checklist: form.checklist,
      fotos: form.fotos
    })
    
    notificationPayload.value = res.data.notificacion_cliente
    showNotificationModal.value = true
  } catch (error) {
    alert(error.response?.data?.message || 'Error al guardar la ficha')
  } finally {
    loadingSubmit.value = false
  }
}

const closeNotificationModal = () => {
  showNotificationModal.value = false
  notificationPayload.value = null
  fetchAgenda()
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
