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
    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-2xl shadow-sm border p-8 mb-8">
        <div class="text-left md:text-center max-w-2xl mx-auto">
          <h2 class="text-3xl font-extrabold text-slate-900 mb-2">Panel del Personal del Spa</h2>
          <p class="text-slate-600 mb-6 text-base">
            Bienvenido al panel operativo. Aquí puedes coordinar la agenda, confirmar citas y registrar las fichas técnicas de grooming.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto mt-6">
          
          <!-- RECEPCION CARD -->
          <div
            v-if="roles.includes('RECEPCION') || roles.includes('ADMIN')"
            @click="router.push('/reception/calendar')"
            class="bg-gradient-to-tr from-indigo-50 to-teal-50 hover:shadow-xl hover:border-teal-400/50 transition cursor-pointer p-6 rounded-2xl border flex flex-col justify-between"
          >
            <div>
              <span class="text-4xl block mb-4">📅</span>
              <h3 class="text-xl font-bold text-slate-900 mb-2">Calendario de Recepción</h3>
              <p class="text-slate-600 text-sm">
                Controla la agenda en tiempo real, confirma solicitudes de clientes, reprograma citas y gestiona pagos de caja.
              </p>
            </div>
            <button class="mt-6 w-full bg-teal-600 hover:bg-teal-700 text-white font-semibold py-2.5 rounded-xl transition text-sm">
              Abrir Recepción
            </button>
          </div>

          <!-- GROOMER CARD -->
          <div
            v-if="roles.includes('GROOMER') || roles.includes('ADMIN')"
            @click="router.push('/groomer/workspace')"
            class="bg-gradient-to-tr from-purple-50 to-indigo-50 hover:shadow-xl hover:border-indigo-400/50 transition cursor-pointer p-6 rounded-2xl border flex flex-col justify-between"
          >
            <div>
              <span class="text-4xl block mb-4">✂️</span>
              <h3 class="text-xl font-bold text-slate-900 mb-2">Estación de Grooming</h3>
              <p class="text-slate-600 text-sm">
                Visualiza tus servicios asignados hoy, abre la ficha técnica del animal, marca el checklist obligatorio y cierra atenciones.
              </p>
            </div>
            <button class="mt-6 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-xl transition text-sm">
              Ingresar a Mesa
            </button>
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