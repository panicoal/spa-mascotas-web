<template>
  <div class="min-h-screen bg-gray-50">
    <nav class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-xl font-semibold text-gray-900">Pet Spa - Personal</h1>
          </div>
          <div class="flex items-center space-x-4">
            <div class="text-sm text-gray-700">
              <p>{{ user?.nombre_completo }}</p>
              <p class="text-xs text-gray-500">Rol: {{ roles.join(', ') }}</p>
            </div>
            <button
              @click="handleLogout"
              class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm font-medium"
            >
              Cerrar sesión
            </button>
          </div>
        </div>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="border-4 border-dashed border-gray-200 rounded-lg p-8">
          <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Panel de Personal</h2>
            <p class="text-gray-600 mb-6">
              Gestiona citas, servicios y atiende a los clientes.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Citas del Día</h3>
                <p class="text-gray-600">Revisa las citas programadas</p>
              </div>
              <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Servicios</h3>
                <p class="text-gray-600">Registra servicios realizados</p>
              </div>
              <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Clientes</h3>
                <p class="text-gray-600">Busca y administra clientes</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const router = useRouter()
const authStore = useAuthStore()

const user = computed(() => authStore.user)
const roles = computed(() => authStore.roles)

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>