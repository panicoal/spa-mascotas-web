<template>
  <div class="p-6">

    <div class="flex justify-between items-center mb-6">

      <h1 class="text-2xl font-bold">
        Servicios
      </h1>

      <button
        @click="openCreateModal"
        class="bg-indigo-600 text-white px-4 py-2 rounded"
      >
        Nuevo Servicio
      </button>
    </div>

    <div
      v-if="serviceStore.loading"
      class="text-center py-10"
    >
      Cargando...
    </div>

    <div
      v-else-if="!serviceStore.services.length"
      class="text-center py-10"
    >
      No hay servicios.
    </div>

    <div
      v-else
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
    >

      <div
        v-for="service in serviceStore.services"
        :key="service.id"
        class="bg-white rounded shadow p-4"
      >

        <div class="flex justify-between">

          <h2 class="font-bold">
            {{ service.nombre }}
          </h2>

          <span
            class="text-xs px-2 py-1 rounded"
            :class="service.is_active
              ? 'bg-green-100 text-green-700'
              : 'bg-red-100 text-red-700'"
          >
            {{ service.is_active ? 'Activo' : 'Inactivo' }}
          </span>
        </div>

        <p class="text-sm text-gray-600 mt-2">
          {{ service.descripcion }}
        </p>

        <div class="mt-4 text-sm">
          <p>
            Precio:
            Bs {{ service.precio }}
          </p>

          <p>
            Duración:
            {{ service.duracion_minutos }} min
          </p>

          <p>
            Categoría:
            {{ service.categoria }}
          </p>
        </div>

        <div class="flex gap-2 mt-4">

          <button
            @click="editService(service)"
            class="bg-yellow-500 text-white px-3 py-1 rounded"
          >
            Editar
          </button>

          <button
            v-if="!service.deleted_at"
            @click="removeService(service.id)"
            class="bg-red-600 text-white px-3 py-1 rounded"
          >
            Eliminar
          </button>

          <button
            v-else
            @click="restore(service.id)"
            class="bg-green-600 text-white px-3 py-1 rounded"
          >
            Restaurar
          </button>
        </div>
      </div>
    </div>

    <ServiceFormModal
      :show="showModal"
      :service="selectedService"
      @close="closeModal"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

import { useServiceStore }
from '@/stores/serviceStore'

import ServiceFormModal
from '@/components/services/ServiceFormModal.vue'

const serviceStore =
  useServiceStore()

const showModal =
  ref(false)

const selectedService =
  ref(null)

onMounted(() => {

  serviceStore.fetchServices()
})

const openCreateModal = () => {

  selectedService.value = null

  showModal.value = true
}

const editService = (service) => {

  selectedService.value = service

  showModal.value = true
}

const closeModal = () => {

  showModal.value = false
}

const removeService = async (id) => {

  if (
    confirm(
      '¿Eliminar servicio?'
    )
  ) {

    await serviceStore.deleteService(id)
  }
}

const restore = async (id) => {

  await serviceStore.restoreService(id)
}
</script>