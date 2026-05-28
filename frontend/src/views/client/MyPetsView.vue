<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 p-6">

    <div class="flex justify-between items-center mb-6">

      <div>
        <h1 class="text-3xl font-extrabold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
          🐾 Mis Mascotas
        </h1>

        <p class="text-slate-400 text-sm mt-1">
          Administra los perfiles de tus compañeros
        </p>
      </div>

      <button
        @click="openCreateModal"
        class="bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm transition active:scale-95 shadow-lg shadow-indigo-500/20"
      >
        + Nueva Mascota
      </button>

    </div>

    <!-- Loading -->

    <div
      v-if="petStore.loading"
      class="text-center py-20 text-slate-400"
    >
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-500 mx-auto mb-3"></div>
      Cargando mascotas...
    </div>

    <!-- Empty -->

    <div
      v-else-if="!petStore.pets.length"
      class="bg-slate-900 border border-dashed border-slate-700 rounded-2xl p-12 text-center"
    >
      <p class="text-5xl mb-4">🐾</p>
      <h3 class="text-xl font-semibold mb-2 text-slate-200">
        No tienes mascotas registradas
      </h3>

      <p class="text-slate-500 mb-6 text-sm">
        Registra a tu primer compañero peludo
      </p>

      <button
        @click="openCreateModal"
        class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm"
      >
        Registrar Mascota
      </button>
    </div>

    <!-- Grid -->

    <div
      v-else
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
    >

      <div
        v-for="pet in petStore.pets"
        :key="pet.id"
        class="bg-slate-900 border border-slate-800 rounded-2xl p-5 hover:border-slate-700 transition flex flex-col gap-3"
      >

        <div class="flex justify-between items-start">

          <div class="flex items-center gap-3">
            <span class="text-3xl">{{ especieIcon(pet.especie) }}</span>
            <div>
              <h2 class="text-lg font-bold text-slate-100">
                {{ pet.nombre }}
              </h2>
              <p class="text-slate-500 text-xs">
                {{ pet.raza || pet.especie }}
              </p>
            </div>
          </div>

          <span :class="sexoBadge(pet.sexo)" class="px-2 py-0.5 rounded-full text-[10px] font-bold border">
            {{ pet.sexo === 'MACHO' ? '♂' : '♀' }} {{ pet.sexo }}
          </span>

        </div>

        <div class="flex flex-wrap gap-1.5">
          <span :class="tamanioBadge(pet.tamanio)" class="px-2 py-0.5 rounded-lg text-[10px] font-bold border">
            {{ tamanioIcon(pet.tamanio) }} {{ pet.tamanio || 'Sin tamaño' }}
          </span>
          <span v-if="pet.peso" class="bg-slate-800 text-slate-400 border border-slate-700 px-2 py-0.5 rounded-lg text-[10px]">
            {{ pet.peso }} kg
          </span>
          <span v-if="pet.color" class="bg-slate-800 text-slate-400 border border-slate-700 px-2 py-0.5 rounded-lg text-[10px]">
            {{ pet.color }}
          </span>
          <span v-if="pet.restricciones_medicas" class="bg-red-950/40 text-red-300 border border-red-900/40 px-2 py-0.5 rounded-lg text-[10px] font-bold">
            ⚠️ Alergias
          </span>
        </div>

        <div class="flex gap-2 pt-2 border-t border-slate-800/60">

          <router-link
            :to="`/pets/${pet.id}`"
            class="flex-1 border border-slate-700 text-center py-1.5 rounded-lg hover:bg-slate-800/40 transition text-xs text-slate-300 font-medium"
          >
            Ver
          </router-link>

          <button
            @click="openEditModal(pet)"
            class="flex-1 bg-indigo-600/20 text-indigo-300 border border-indigo-700/40 py-1.5 rounded-lg hover:bg-indigo-600/40 transition text-xs font-medium"
          >
            Editar
          </button>

          <button
            @click="handleDelete(pet.id)"
            class="flex-1 bg-red-950/20 text-red-400 border border-red-900/30 py-1.5 rounded-lg hover:bg-red-900/30 transition text-xs font-medium"
          >
            Eliminar
          </button>

        </div>

      </div>

    </div>

    <!-- Modal -->

    <PetFormModal
      v-if="showModal"
      :pet="selectedPet"
      @close="closeModal"
      @save="handleSave"
    />

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

import { usePetStore }
  from '@/stores/petStore'

import PetFormModal
  from '@/components/pets/PetFormModal.vue'

const petStore = usePetStore()

const especieIcon = (e) => ({ PERRO: '🐶', GATO: '🐱', AVE: '🦜', OTRO: '🐾' })[e] || '🐾'

const tamanioIcon = (t) => ({ PEQUEÑO: '🐹', MEDIANO: '🐕', GRANDE: '🦮', GIGANTE: '🐘' })[t] || '🐾'

const tamanioBadge = (t) => ({
  PEQUEÑO: 'bg-emerald-950/40 text-emerald-300 border-emerald-800/50',
  MEDIANO: 'bg-blue-950/40 text-blue-300 border-blue-800/50',
  GRANDE:  'bg-orange-950/40 text-orange-300 border-orange-800/50',
  GIGANTE: 'bg-red-950/40 text-red-300 border-red-800/50',
})[t] || 'bg-slate-800 text-slate-300 border-slate-700'

const sexoBadge = (s) => s === 'MACHO'
  ? 'bg-blue-950/40 text-blue-300 border-blue-800/50'
  : 'bg-pink-950/40 text-pink-300 border-pink-800/50'

const showModal =
  ref(false)

const selectedPet =
  ref(null)

onMounted(async () => {

  await petStore.fetchPets()
})

const openCreateModal = () => {

  selectedPet.value = null

  showModal.value = true
}

const openEditModal = (pet) => {

  selectedPet.value = pet

  showModal.value = true
}

const closeModal = () => {

  showModal.value = false
}

const handleSave = async (form) => {

  try {

    if (selectedPet.value) {

      await petStore.updatePet(
        selectedPet.value.id,
        form
      )

    } else {

      await petStore.createPet(form)
    }

    closeModal()

  } catch (error) {

    console.error(error)
  }
}

const handleDelete = async (id) => {

  const confirmed =
    confirm(
      '¿Eliminar mascota?'
    )

  if (!confirmed) return

  try {

    await petStore.deletePet(id)

  } catch (error) {

    console.error(error)
  }
}
</script>