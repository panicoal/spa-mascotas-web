
<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Cambiar Contraseña
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Debe cambiar su contraseña antes de continuar
        </p>
      </div>

      <!-- Alerta -->
      <div v-if="errorMessage" class="rounded-md bg-red-50 p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-red-800">{{ errorMessage }}</p>
          </div>
        </div>
      </div>

      <!-- Formulario -->
      <form @submit.prevent="handleChangePassword" class="mt-8 space-y-6">
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="current_password" class="sr-only">Contraseña Temporal</label>
            <input
              id="current_password"
              type="password"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              placeholder="Contraseña Temporal"
              v-model="form.current_password"
            />
          </div>
          <div>
            <label for="new_password" class="sr-only">Nueva Contraseña</label>
            <input
              id="new_password"
              type="password"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              placeholder="Nueva Contraseña"
              v-model="form.new_password"
            />
          </div>
          <div>
            <label for="new_password_confirmation" class="sr-only">Confirmar Contraseña</label>
            <input
              id="new_password_confirmation"
              type="password"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              placeholder="Confirmar Contraseña"
              v-model="form.new_password_confirmation"
            />
          </div>
        </div>

        <!-- Requisitos de contraseña -->
        <div class="text-sm text-gray-600 bg-gray-50 p-4 rounded">
          <p class="font-medium text-gray-900 mb-2">Requisitos:</p>
          <ul class="space-y-1 text-xs">
            <li :class="passwordRequirements.minLength ? 'text-green-600' : 'text-gray-600'">
              ✓ Mínimo 8 caracteres
            </li>
            <li :class="passwordRequirements.hasUpperCase ? 'text-green-600' : 'text-gray-600'">
              ✓ Al menos una mayúscula
            </li>
            <li :class="passwordRequirements.hasLowerCase ? 'text-green-600' : 'text-gray-600'">
              ✓ Al menos una minúscula
            </li>
            <li :class="passwordRequirements.hasNumber ? 'text-green-600' : 'text-gray-600'">
              ✓ Al menos un número
            </li>
            <li :class="passwordRequirements.hasSymbol ? 'text-green-600' : 'text-gray-600'">
              ✓ Al menos un símbolo (!@#$%^&*)
            </li>
          </ul>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading || !passwordRequirements.allMet"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
            <span v-else>Cambiar Contraseña</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import api from '@/services/api'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

const loading = ref(false)
const errorMessage = ref('')

const passwordRequirements = computed(() => {

  const pwd = form.value.new_password

  return {

    minLength: pwd.length >= 8,

    hasUpperCase: /[A-Z]/.test(pwd),

    hasLowerCase: /[a-z]/.test(pwd),

    hasNumber: /[0-9]/.test(pwd),

    hasSymbol: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(pwd),

    allMet:
      pwd.length >= 8 &&
      /[A-Z]/.test(pwd) &&
      /[a-z]/.test(pwd) &&
      /[0-9]/.test(pwd) &&
      /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(pwd) &&
      form.value.new_password ===
      form.value.new_password_confirmation
  }
})

const handleChangePassword = async () => {

  errorMessage.value = ''

  if (
    form.value.new_password !==
    form.value.new_password_confirmation
  ) {

    errorMessage.value =
      'Las contraseñas no coinciden'

    return
  }

  loading.value = true

  try {

    const response = await api.post(
      '/users/change-password',
      {

        current_password:
          form.value.current_password,

        new_password:
          form.value.new_password,

        new_password_confirmation:
          form.value.new_password_confirmation
      }
    )

    console.log(response.data)

    await authStore.fetchUser()

    if (
      authStore.user?.role_names?.includes('ADMIN')
    ) {

      router.push('/admin')

    } else if (

      authStore.user?.role_names?.includes('GROOMER') ||

      authStore.user?.role_names?.includes('RECEPCION')

    ) {

      router.push('/staff')

    } else {

      router.push('/client')
    }

  } catch (err) {

    console.error(err)

    errorMessage.value =

      err.response?.data?.message ||

      err.message ||

      'Error al cambiar contraseña'

  } finally {

    loading.value = false
  }
}
</script>
