<template>
  <div class="fixed inset-0 bg-black/80 backdrop-blur-md flex items-center justify-center z-50 p-4 animate-fade-in">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-2xl shadow-2xl flex flex-col max-h-[92vh]">

      <!-- Header -->
      <div class="flex justify-between items-center p-6 border-b border-slate-800/60 shrink-0">
        <div>
          <h2 class="text-2xl font-extrabold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
            {{ isEdit ? '✏️ Editar Mascota' : '🐾 Registrar Nueva Mascota' }}
          </h2>
          <p class="text-xs text-slate-400 mt-0.5">Completa los datos de tu compañero</p>
        </div>
        <button @click="$emit('close')" class="text-slate-500 hover:text-slate-200 transition text-xl leading-none">✕</button>
      </div>

      <!-- Form body - scrollable -->
      <form @submit.prevent="handleSubmit" class="overflow-y-auto flex-1 p-6 space-y-6">

        <!-- Sección 1: Datos básicos -->
        <div>
          <h3 class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-3">1. Identificación</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div class="md:col-span-2">
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nombre *</label>
              <input
                v-model="form.nombre"
                type="text"
                required
                placeholder="Ej: Firulais, Luna, Max..."
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition placeholder-slate-600"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">Especie *</label>
              <select
                v-model="form.especie"
                required
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition"
              >
                <option value="PERRO">🐶 Perro</option>
                <option value="GATO">🐱 Gato</option>
                <option value="AVE">🦜 Ave</option>
                <option value="OTRO">🐾 Otro</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">Sexo *</label>
              <select
                v-model="form.sexo"
                required
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition"
              >
                <option value="MACHO">♂ Macho</option>
                <option value="HEMBRA">♀ Hembra</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">Raza</label>
              <input
                v-model="form.raza"
                type="text"
                placeholder="Ej: Golden Retriever, Siamés..."
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition placeholder-slate-600"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">Color / Pelaje</label>
              <input
                v-model="form.color"
                type="text"
                placeholder="Ej: Dorado, negro con manchas..."
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition placeholder-slate-600"
              />
            </div>

          </div>
        </div>

        <!-- Sección 2: Tamaño y Físico -->
        <div>
          <h3 class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-3">2. Tamaño y Características Físicas</h3>

          <!-- Tamaño destacado con aviso -->
          <div class="bg-indigo-950/30 border border-indigo-800/40 rounded-xl p-4 mb-4">
            <label class="block text-xs font-bold text-indigo-300 mb-2">
              📏 Tamaño de la Mascota *
              <span class="text-indigo-400 font-normal ml-1">(Afecta el tiempo reservado del servicio)</span>
            </label>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
              <button
                v-for="size in tamanioOptions"
                :key="size.value"
                type="button"
                @click="form.tamanio = size.value"
                :class="[
                  'py-2.5 px-3 rounded-lg border text-xs font-bold transition text-center flex flex-col items-center gap-1',
                  form.tamanio === size.value
                    ? 'border-indigo-500 bg-indigo-600/30 text-indigo-200 shadow-lg shadow-indigo-500/10'
                    : 'border-slate-800 bg-slate-950/30 text-slate-400 hover:border-slate-600'
                ]"
              >
                <span class="text-lg">{{ size.icon }}</span>
                <span>{{ size.label }}</span>
                <span class="text-[9px] font-normal opacity-70">{{ size.hint }}</span>
              </button>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">Peso (kg)</label>
              <input
                v-model="form.peso"
                type="number"
                step="0.1"
                min="0"
                max="200"
                placeholder="Ej: 4.5"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition placeholder-slate-600"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">Edad</label>
              <input
                v-model="form.edad"
                type="number"
                min="0"
                max="99"
                placeholder="Ej: 2"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition placeholder-slate-600"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">Unidad de Edad</label>
              <select
                v-model="form.unidad_edad"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition"
              >
                <option value="MESES">Meses</option>
                <option value="AÑOS">Años</option>
              </select>
            </div>

            <div class="md:col-span-3">
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">Fecha de Nacimiento</label>
              <input
                v-model="form.fecha_nacimiento"
                type="date"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition"
              />
            </div>
          </div>
        </div>

        <!-- Sección 3: Observaciones clínicas -->
        <div>
          <h3 class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-3">3. Información Clínica</h3>
          <div class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">Características Físicas Relevantes</label>
              <textarea
                v-model="form.caracteristicas_fisicas"
                rows="2"
                placeholder="Ej: cicatriz en el lomo, mancha en ojo derecho, pelo muy fino..."
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition placeholder-slate-600 resize-none"
              ></textarea>
            </div>

            <!-- Restricciones - alerta destacada -->
            <div class="bg-red-950/20 border border-red-900/40 rounded-xl p-4">
              <label class="block text-xs font-bold text-red-300 mb-1.5">
                ⚠️ Alergias y Restricciones Clínicas
                <span class="text-red-400 font-normal ml-1">(Visible para el Groomer durante el servicio)</span>
              </label>
              <textarea
                v-model="form.restricciones_medicas"
                rows="2"
                placeholder="Ej: alergia a la lavanda, no tocar las orejas, problemas cardíacos, medicación activa..."
                class="w-full bg-slate-950/80 border border-red-900/30 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-red-500 transition placeholder-slate-600 resize-none"
              ></textarea>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">Observaciones Generales</label>
              <textarea
                v-model="form.observaciones"
                rows="2"
                placeholder="Otras notas relevantes sobre la mascota..."
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition placeholder-slate-600 resize-none"
              ></textarea>
            </div>
          </div>
        </div>

      </form>

      <!-- Footer actions -->
      <div class="p-6 pt-4 border-t border-slate-800/60 shrink-0 flex justify-end gap-3">
        <button
          type="button"
          @click="$emit('close')"
          class="px-5 py-2.5 rounded-xl border border-slate-700 text-slate-400 hover:bg-slate-800/40 transition text-sm font-medium"
        >
          Cancelar
        </button>
        <button
          @click="handleSubmit"
          :disabled="loading || !form.tamanio"
          class="bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-bold px-6 py-2.5 rounded-xl transition active:scale-95 shadow-lg shadow-indigo-500/20 disabled:opacity-50 disabled:cursor-not-allowed text-sm"
        >
          {{ loading ? 'Guardando...' : (isEdit ? 'Guardar Cambios' : 'Registrar Mascota') }}
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'

const props = defineProps({
  pet: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['save', 'close'])

const loading = ref(false)
const isEdit = props.pet !== null

const tamanioOptions = [
  { value: 'PEQUEÑO', label: 'Pequeño', icon: '🐹', hint: 'Hasta 10 kg' },
  { value: 'MEDIANO', label: 'Mediano', icon: '🐕', hint: '10 - 25 kg' },
  { value: 'GRANDE', label: 'Grande', icon: '🦮', hint: '25 - 45 kg' },
  { value: 'GIGANTE', label: 'Gigante', icon: '🐘', hint: '+ 45 kg' },
]

const form = reactive({
  nombre: props.pet?.nombre || '',
  especie: props.pet?.especie || 'PERRO',
  raza: props.pet?.raza || '',
  sexo: props.pet?.sexo || 'MACHO',
  tamanio: props.pet?.tamanio || 'MEDIANO',
  edad: props.pet?.edad || '',
  unidad_edad: props.pet?.unidad_edad || 'AÑOS',
  fecha_nacimiento: props.pet?.fecha_nacimiento || '',
  peso: props.pet?.peso || '',
  color: props.pet?.color || '',
  caracteristicas_fisicas: props.pet?.caracteristicas_fisicas || '',
  restricciones_medicas: props.pet?.restricciones_medicas || '',
  observaciones: props.pet?.observaciones || '',
})

const handleSubmit = async () => {
  if (!form.tamanio) return
  loading.value = true
  try {
    emit('save', { ...form })
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.2s ease-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.97); }
  to   { opacity: 1; transform: scale(1); }
}
</style>