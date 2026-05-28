<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
  >

    <div class="bg-white w-full max-w-lg rounded p-6">

      <h2 class="text-xl font-bold mb-4">

        {{ service
          ? 'Editar Servicio'
          : 'Nuevo Servicio'
        }}
      </h2>

      <form @submit.prevent="submitForm">

        <div class="space-y-4">

          <input
            v-model="form.nombre"
            type="text"
            placeholder="Nombre"
            class="w-full border rounded px-3 py-2"
            required
          />

          <textarea
            v-model="form.descripcion"
            placeholder="Descripción"
            class="w-full border rounded px-3 py-2"
          />

          <input
            v-model="form.precio"
            type="number"
            step="0.01"
            placeholder="Precio"
            class="w-full border rounded px-3 py-2"
            required
          />

          <input
            v-model="form.duracion_minutos"
            type="number"
            placeholder="Duración"
            class="w-full border rounded px-3 py-2"
            required
          />

          <select
            v-model="form.categoria"
            class="w-full border rounded px-3 py-2"
            required
          >

            <option value="">
              Seleccione categoría
            </option>

            <option value="GROOMING">
              Grooming
            </option>

            <option value="SPA">
              Spa
            </option>

            <option value="SALUD">
              Salud
            </option>

            <option value="VACUNAS">
              Vacunas
            </option>

            <option value="ESTETICA">
              Estética
            </option>
          </select>

          <label class="flex items-center gap-2">

            <input
              v-model="form.is_active"
              type="checkbox"
            />

            Activo
          </label>
        </div>

        <div class="flex justify-end gap-2 mt-6">

          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 border rounded"
          >
            Cancelar
          </button>

          <button
            type="submit"
            class="bg-indigo-600 text-white px-4 py-2 rounded"
          >
            Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import {

  ref,

  watch

} from 'vue'

import { useServiceStore }
from '@/stores/serviceStore'

const props = defineProps({

  show: Boolean,

  service: Object
})

const emit =
  defineEmits(['close'])

const serviceStore =
  useServiceStore()

const form = ref({

  nombre: '',

  descripcion: '',

  precio: '',

  duracion_minutos: '',

  categoria: '',

  is_active: true
})

watch(
  () => props.service,
  (value) => {

    if (value) {

      form.value = {

        nombre:
          value.nombre,

        descripcion:
          value.descripcion,

        precio:
          value.precio,

        duracion_minutos:
          value.duracion_minutos,

        categoria:
          value.categoria,

        is_active:
          value.is_active
      }

    } else {

      form.value = {

        nombre: '',

        descripcion: '',

        precio: '',

        duracion_minutos: '',

        categoria: '',

        is_active: true
      }
    }
  },
  {
    immediate: true
  }
)

const submitForm = async () => {

  if (props.service) {

    await serviceStore.updateService(
      props.service.id,
      form.value
    )

  } else {

    await serviceStore.createService(
      form.value
    )
  }

  emit('close')
}
</script>