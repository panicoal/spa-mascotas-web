<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 p-6">
    <div class="max-w-4xl mx-auto">

      <!-- Back link -->
      <router-link to="/pets" class="text-sm text-indigo-400 hover:text-indigo-300 transition flex items-center gap-1 mb-6">
        ← Volver a Mis Mascotas
      </router-link>

      <div v-if="petStore.loading" class="text-center py-20 text-slate-400">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-500 mx-auto mb-3"></div>
        Cargando perfil...
      </div>

      <div v-else-if="pet" class="space-y-6">

        <!-- Hero Card -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-6 flex flex-col sm:flex-row gap-6 items-start">
          <!-- Avatar placeholder -->
          <div class="h-24 w-24 rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-700 flex items-center justify-center text-5xl shrink-0 shadow-xl shadow-indigo-500/10">
            {{ especieIcon(pet.especie) }}
          </div>
          <div class="flex-1">
            <div class="flex flex-wrap items-start gap-2 mb-1">
              <h1 class="text-3xl font-extrabold text-slate-100">{{ pet.nombre }}</h1>
              <span :class="sexoBadge(pet.sexo)" class="px-2.5 py-0.5 rounded-full text-xs font-bold self-center">
                {{ pet.sexo === 'MACHO' ? '♂ Macho' : '♀ Hembra' }}
              </span>
            </div>
            <p class="text-slate-400 text-sm">{{ pet.especie }} {{ pet.raza ? '· ' + pet.raza : '' }}</p>

            <!-- Tamaño destacado -->
            <div class="mt-3 flex flex-wrap gap-2">
              <span :class="tamanioBadge(pet.tamanio)" class="px-3 py-1 rounded-xl text-xs font-bold border flex items-center gap-1">
                {{ tamanioIcon(pet.tamanio) }} {{ pet.tamanio || 'Sin tamaño' }}
              </span>
              <span v-if="pet.peso" class="bg-slate-800 text-slate-300 border border-slate-700 px-3 py-1 rounded-xl text-xs font-medium">
                ⚖️ {{ pet.peso }} kg
              </span>
              <span v-if="pet.edad" class="bg-slate-800 text-slate-300 border border-slate-700 px-3 py-1 rounded-xl text-xs font-medium">
                🎂 {{ pet.edad }} {{ pet.unidad_edad?.toLowerCase() || 'años' }}
              </span>
              <span v-if="pet.color" class="bg-slate-800 text-slate-300 border border-slate-700 px-3 py-1 rounded-xl text-xs font-medium">
                🎨 {{ pet.color }}
              </span>
              <span v-if="pet.fecha_nacimiento" class="bg-slate-800 text-slate-300 border border-slate-700 px-3 py-1 rounded-xl text-xs font-medium">
                📅 Nac: {{ formatDate(pet.fecha_nacimiento) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Restricciones médicas — alerta especial -->
        <div v-if="pet.restricciones_medicas" class="bg-red-950/25 border border-red-900/50 rounded-2xl p-5 flex gap-3">
          <span class="text-red-400 text-2xl shrink-0">⚠️</span>
          <div>
            <p class="text-xs font-bold text-red-300 uppercase tracking-wider mb-1">Alergias y Restricciones Clínicas</p>
            <p class="text-sm text-red-200/90">{{ pet.restricciones_medicas }}</p>
          </div>
        </div>

        <!-- Info Grid -->
        <div class="grid md:grid-cols-2 gap-6">
          <!-- Características Físicas -->
          <div v-if="pet.caracteristicas_fisicas" class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Características Físicas</h2>
            <p class="text-sm text-slate-300 leading-relaxed">{{ pet.caracteristicas_fisicas }}</p>
          </div>

          <!-- Observaciones -->
          <div v-if="pet.observaciones" class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Observaciones Generales</h2>
            <p class="text-sm text-slate-300 leading-relaxed">{{ pet.observaciones }}</p>
          </div>
        </div>

        <!-- Futuras secciones -->
        <div class="grid md:grid-cols-3 gap-6">
          <div class="bg-slate-900/40 border border-dashed border-slate-800 rounded-2xl p-6 text-center">
            <p class="text-2xl mb-2">💉</p>
            <h3 class="font-bold text-slate-300 mb-1">Historial Médico</h3>
            <p class="text-slate-500 text-xs">Próximamente</p>
          </div>
          <div class="bg-slate-900/40 border border-dashed border-slate-800 rounded-2xl p-6 text-center">
            <p class="text-2xl mb-2">📅</p>
            <h3 class="font-bold text-slate-300 mb-1">Mis Citas</h3>
            <router-link to="/client/appointments" class="text-indigo-400 text-xs hover:underline">Ver citas →</router-link>
          </div>
          <div class="bg-slate-900/40 border border-dashed border-slate-800 rounded-2xl p-6 text-center">
            <p class="text-2xl mb-2">🩺</p>
            <h3 class="font-bold text-slate-300 mb-1">Vacunas</h3>
            <p class="text-slate-500 text-xs">Próximamente</p>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { usePetStore } from '@/stores/petStore'

const route = useRoute()
const petStore = usePetStore()

onMounted(async () => {
  await petStore.fetchPet(route.params.id)
})

const pet = computed(() => petStore.selectedPet)

const formatDate = (d) => {
  if (!d) return '-'
  try {
    let date
    if (d.includes('T')) {
      date = new Date(d)
    } else if (d.length === 10) {
      date = new Date(d + 'T00:00:00')
    } else {
      date = new Date(d)
    }
    
    if (isNaN(date.getTime())) {
      return '-'
    }
    
    return date.toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric' })
  } catch (error) {
    console.error('Error formatting date:', d, error)
    return '-'
  }
}

const especieIcon = (especie) => {
  const map = { PERRO: '🐶', GATO: '🐱', AVE: '🦜', OTRO: '🐾' }
  return map[especie] || '🐾'
}

const tamanioIcon = (t) => {
  const map = { PEQUEÑO: '🐹', MEDIANO: '🐕', GRANDE: '🦮', GIGANTE: '🐘' }
  return map[t] || '🐾'
}

const tamanioBadge = (t) => {
  const map = {
    PEQUEÑO:  'bg-emerald-950/40 text-emerald-300 border-emerald-800/50',
    MEDIANO:  'bg-blue-950/40 text-blue-300 border-blue-800/50',
    GRANDE:   'bg-orange-950/40 text-orange-300 border-orange-800/50',
    GIGANTE:  'bg-red-950/40 text-red-300 border-red-800/50',
  }
  return map[t] || 'bg-slate-800 text-slate-300 border-slate-700'
}

const sexoBadge = (sexo) => sexo === 'MACHO'
  ? 'bg-blue-950/40 text-blue-300 border border-blue-800/50'
  : 'bg-pink-950/40 text-pink-300 border border-pink-800/50'
</script>