<template>
  <div class="min-h-screen bg-gray-100">

    <!-- NAVBAR -->
    <nav class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex justify-between h-16">

          <!-- Logo -->
          <div class="flex items-center">
            <h1 class="text-2xl font-bold text-indigo-600">
              Pet Spa
            </h1>
          </div>

          <!-- User -->
          <div class="flex items-center gap-4">

            <div class="text-right">
              <p class="text-sm font-semibold text-gray-900">
                {{ user?.nombre_completo }}
              </p>

              <p class="text-xs text-gray-500">
                Cliente
              </p>
            </div>

            <button
              @click="handleLogout"
              class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition"
            >
              Cerrar sesión
            </button>

          </div>

        </div>

      </div>
    </nav>

    <!-- CONTENT -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

      <!-- HEADER -->
      <div class="mb-8">

        <h2 class="text-3xl font-bold text-gray-900">
          Bienvenido 
        </h2>

        <p class="text-gray-600 mt-2">
          Administra tus mascotas y reservas.
        </p>

      </div>

      <!-- STATS -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        <!-- Mascotas -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border">

          <div class="flex justify-between items-center">

            <div>

              <p class="text-sm text-gray-500">
                Mascotas registradas
              </p>

              <h3 class="text-4xl font-bold text-indigo-600 mt-2">
                {{ petCount }}
              </h3>

            </div>

            <div class="text-5xl">
              🐶
            </div>

          </div>

        </div>

        <!-- Reservas -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border">

          <div class="flex justify-between items-center">

            <div>

              <p class="text-sm text-gray-500">
                Próximas reservas
              </p>

              <h3 class="text-4xl font-bold text-green-600 mt-2">
                {{ upcomingCount }}
              </h3>

            </div>

            <div class="text-5xl">
              📅
            </div>

          </div>

        </div>

        <!-- Historial -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border">

          <div class="flex justify-between items-center">

            <div>

              <p class="text-sm text-gray-500">
                Servicios realizados
              </p>

              <h3 class="text-4xl font-bold text-pink-600 mt-2">
                {{ finishedCount }}
              </h3>

            </div>

            <div class="text-5xl">
              ✂️
            </div>

          </div>

        </div>

      </div>

      <!-- MODULES -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Mascotas -->
        <button
          @click="router.push('/pets')"
          class="bg-white hover:shadow-xl transition rounded-2xl border p-6 text-left"
        >

          <div class="text-5xl mb-4">
            🐾
          </div>

          <h3 class="text-xl font-bold text-gray-900 mb-2">
            Mis Mascotas
          </h3>

          <p class="text-gray-600">
            Registra, edita y administra tus mascotas.
          </p>

        </button>

        <!-- Reservas -->
        <button
          @click="router.push('/client/appointments')"
          class="bg-white hover:shadow-xl transition rounded-2xl border p-6 text-left"
        >

          <div class="text-5xl mb-4">
            📅
          </div>

          <h3 class="text-xl font-bold text-gray-900 mb-2">
            Reservas
          </h3>

          <p class="text-gray-600">
            Solicita citas y administra tus horarios.
          </p>

        </button>

        <!-- Historial -->
        <button
          @click="router.push('/client/appointments')"
          class="bg-white hover:shadow-xl transition rounded-2xl border p-6 text-left"
        >

          <div class="text-5xl mb-4">
            ✂️
          </div>

          <h3 class="text-xl font-bold text-gray-900 mb-2">
            Historial
          </h3>

          <p class="text-gray-600">
            Consulta el historial y las fichas estéticas.
          </p>

        </button>

        <!-- Tienda -->
        <button
          @click="router.push('/tienda')"
          class="bg-white hover:shadow-xl hover:border-indigo-400 transition rounded-2xl border border-indigo-100 shadow-sm shadow-indigo-50 p-6 text-left"
        >

          <div class="text-5xl mb-4">
            🛍️
          </div>

          <h3 class="text-xl font-bold text-gray-900 mb-2 flex items-center gap-2">
            Tienda Boutique
            <span class="bg-indigo-100 text-indigo-800 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">Nuevo</span>
          </h3>

          <p class="text-gray-600">
            Compra champús y lociones premium directo en WhatsApp.
          </p>

        </button>

      </div>

    </main>

  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'

import { useRouter } from 'vue-router'

import { useAuthStore } from '@/stores/authStore'

import { usePetStore } from '@/stores/petStore'
import { getCitas } from '@/services/appointmentService'

const router = useRouter()

const authStore = useAuthStore()

const petStore = usePetStore()

const user = computed(() => authStore.user)

const petCount = computed(() => petStore.pets.length)
const upcomingCount = ref(0)
const finishedCount = ref(0)

onMounted(async () => {
  await petStore.fetchPets()
  try {
    const res = await getCitas()
    const allCitas = res.data.citas || []
    upcomingCount.value = allCitas.filter(c => c.estado === 'PROGRAMADO' || c.estado === 'PENDIENTE_CONFIRMACION').length
    finishedCount.value = allCitas.filter(c => c.estado === 'FINALIZADO' || c.estado === 'PAGADO').length
  } catch (err) {
    console.error(err)
  }
})

const handleLogout = async () => {

  await authStore.logout()

  router.push('/login')
}
</script>