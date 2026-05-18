<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8"
  >
    <div class="max-w-md w-full space-y-8">

      <!-- HEADER -->
      <div>
        <h2
          class="mt-6 text-center text-3xl font-extrabold text-gray-900"
        >
          Crear cuenta
        </h2>

        <p
          class="mt-2 text-center text-sm text-gray-600"
        >
          ¿Ya tienes cuenta?

          <router-link
            to="/login"
            class="font-medium text-indigo-600 hover:text-indigo-500"
          >
            Inicia sesión
          </router-link>
        </p>
      </div>

      <!-- FORM -->
      <form
        class="mt-8 space-y-6"
        @submit.prevent="handleRegister"
      >

        <div class="rounded-md shadow-sm space-y-4">

          <!-- NOMBRE -->
          <div>
            <label
              for="nombre_completo"
              class="block text-sm font-medium text-gray-700 mb-1"
            >
              Nombre completo
            </label>

            <input
              id="nombre_completo"
              type="text"
              required
              v-model="form.nombre_completo"
              class="appearance-none relative block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              placeholder="Juan Pérez"
            />
          </div>

          <!-- EMAIL -->
          <div>
            <label
              for="email"
              class="block text-sm font-medium text-gray-700 mb-1"
            >
              Email
            </label>

            <input
              id="email"
              type="email"
              required
              v-model="form.email"
              class="appearance-none relative block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              placeholder="correo@email.com"
            />
          </div>

          <!-- TELEFONO -->
          <div>
            <label
              for="telefono"
              class="block text-sm font-medium text-gray-700 mb-1"
            >
              Teléfono
            </label>

            <input
              id="telefono"
              type="tel"
              required
              v-model="form.telefono"
              class="appearance-none relative block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              placeholder="77777777"
            />
          </div>

          <!-- CI -->
          <div>
            <label
              for="ci"
              class="block text-sm font-medium text-gray-700 mb-1"
            >
              CI
            </label>

            <input
              id="ci"
              type="text"
              required
              v-model="form.ci"
              class="appearance-none relative block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              placeholder="1234567"
            />
          </div>

          <!-- PASSWORD -->
          <div>
            <label
              for="password"
              class="block text-sm font-medium text-gray-700 mb-1"
            >
              Contraseña
            </label>

            <div class="relative">

              <input
                id="password"
                :type="showPassword ? 'text' : 'password'"
                required
                v-model="form.password"
                class="appearance-none relative block w-full px-3 py-2 border rounded-md placeholder-gray-500 text-gray-900 focus:outline-none sm:text-sm pr-12"
                :class="passwordBorderColor"
                placeholder="Contraseña"
              />

              <!-- OJO -->
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700"
              >
                <span v-if="showPassword">🙈</span>
                <span v-else>👁️</span>
              </button>

            </div>

            <!-- BARRA SEGURIDAD -->
            <div class="mt-3">

              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="h-2 rounded-full transition-all duration-300"
                  :class="strengthBarColor"
                  :style="{ width: strengthWidth }"
                ></div>
              </div>

              <p
                class="mt-2 text-sm font-medium"
                :class="strengthTextColor"
              >
                {{ passwordStrengthText }}
              </p>

            </div>

            <!-- CHECKLIST -->
            <div class="mt-4 space-y-1 text-sm">

              <div
                class="flex items-center"
                :class="hasMinLength ? 'text-green-600' : 'text-gray-500'"
              >
                <span class="mr-2">
                  {{ hasMinLength ? '✅' : '❌' }}
                </span>

                Mínimo 8 caracteres
              </div>

              <div
                class="flex items-center"
                :class="hasUppercase ? 'text-green-600' : 'text-gray-500'"
              >
                <span class="mr-2">
                  {{ hasUppercase ? '✅' : '❌' }}
                </span>

                Al menos una mayúscula
              </div>

              <div
                class="flex items-center"
                :class="hasLowercase ? 'text-green-600' : 'text-gray-500'"
              >
                <span class="mr-2">
                  {{ hasLowercase ? '✅' : '❌' }}
                </span>

                Al menos una minúscula
              </div>

              <div
                class="flex items-center"
                :class="hasNumber ? 'text-green-600' : 'text-gray-500'"
              >
                <span class="mr-2">
                  {{ hasNumber ? '✅' : '❌' }}
                </span>

                Al menos un número
              </div>

              <div
                class="flex items-center"
                :class="hasSpecialChar ? 'text-green-600' : 'text-gray-500'"
              >
                <span class="mr-2">
                  {{ hasSpecialChar ? '✅' : '❌' }}
                </span>

                Al menos un símbolo
              </div>

            </div>

          </div>

          <!-- CONFIRM PASSWORD -->
          <div>

            <label
              for="password_confirmation"
              class="block text-sm font-medium text-gray-700 mb-1"
            >
              Confirmar contraseña
            </label>

            <div class="relative">

              <input
                id="password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                required
                v-model="form.password_confirmation"
                class="appearance-none relative block w-full px-3 py-2 border rounded-md placeholder-gray-500 text-gray-900 focus:outline-none sm:text-sm pr-12"
                :class="passwordsMatch
                  ? 'border-green-500 focus:ring-green-500 focus:border-green-500'
                  : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'"
                placeholder="Confirmar contraseña"
              />

              <!-- OJO -->
              <button
                type="button"
                @click="showConfirmPassword = !showConfirmPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700"
              >
                <span v-if="showConfirmPassword">🙈</span>
                <span v-else>👁️</span>
              </button>

            </div>

            <!-- MATCH -->
            <div
              v-if="form.password_confirmation"
              class="mt-2 text-sm"
            >
              <span
                v-if="passwordsMatch"
                class="text-green-600"
              >
                ✅ Las contraseñas coinciden
              </span>

              <span
                v-else
                class="text-red-600"
              >
                ❌ Las contraseñas no coinciden
              </span>
            </div>

          </div>

        </div>

        <!-- BOTON -->
        <div>

          <button
            type="submit"
            :disabled="loading || !isPasswordValid"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white transition duration-200"
            :class="loading || !isPasswordValid
              ? 'bg-gray-400 cursor-not-allowed'
              : 'bg-indigo-600 hover:bg-indigo-700'"
          >

            <span
              v-if="loading"
              class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"
            ></span>

            <span v-else>
              Crear cuenta
            </span>

          </button>

        </div>

        <!-- ERROR -->
        <div
          v-if="error"
          class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-md text-sm"
        >
          {{ error }}
        </div>

        <!-- SUCCESS -->
        <div
          v-if="success"
          class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-md text-sm"
        >
          {{ success }}
        </div>

      </form>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

import { useRouter } from 'vue-router'

import { registerRequest } from '@/services/authService'

const router = useRouter()

const loading = ref(false)

const error = ref('')

const success = ref('')

const showPassword = ref(false)

const showConfirmPassword = ref(false)

const form = ref({

  nombre_completo: '',

  email: '',

  telefono: '',

  ci: '',

  password: '',

  password_confirmation: ''
})

/*
|--------------------------------------------------------------------------
| VALIDACIONES PASSWORD
|--------------------------------------------------------------------------
*/

const hasMinLength = computed(() =>
  form.value.password.length >= 8
)

const hasUppercase = computed(() =>
  /[A-Z]/.test(form.value.password)
)

const hasLowercase = computed(() =>
  /[a-z]/.test(form.value.password)
)

const hasNumber = computed(() =>
  /[0-9]/.test(form.value.password)
)

const hasSpecialChar = computed(() =>
  /[^A-Za-z0-9]/.test(form.value.password)
)

const passwordsMatch = computed(() =>
  form.value.password === form.value.password_confirmation
)

const strengthScore = computed(() => {

  let score = 0

  if (hasMinLength.value) score++
  if (hasUppercase.value) score++
  if (hasLowercase.value) score++
  if (hasNumber.value) score++
  if (hasSpecialChar.value) score++

  return score
})

const passwordStrengthText = computed(() => {

  if (strengthScore.value <= 2) {
    return '🔴 Contraseña débil'
  }

  if (strengthScore.value <= 4) {
    return '🟡 Contraseña media'
  }

  return '🟢 Contraseña fuerte'
})

const strengthWidth = computed(() => {

  return `${(strengthScore.value / 5) * 100}%`
})

const strengthBarColor = computed(() => {

  if (strengthScore.value <= 2) {
    return 'bg-red-500'
  }

  if (strengthScore.value <= 4) {
    return 'bg-yellow-500'
  }

  return 'bg-green-500'
})

const strengthTextColor = computed(() => {

  if (strengthScore.value <= 2) {
    return 'text-red-600'
  }

  if (strengthScore.value <= 4) {
    return 'text-yellow-600'
  }

  return 'text-green-600'
})

const passwordBorderColor = computed(() => {

  if (!form.value.password) {
    return 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'
  }

  if (strengthScore.value <= 2) {
    return 'border-red-500 focus:ring-red-500 focus:border-red-500'
  }

  if (strengthScore.value <= 4) {
    return 'border-yellow-500 focus:ring-yellow-500 focus:border-yellow-500'
  }

  return 'border-green-500 focus:ring-green-500 focus:border-green-500'
})

const isPasswordValid = computed(() => {

  return (
    hasMinLength.value &&
    hasUppercase.value &&
    hasLowercase.value &&
    hasNumber.value &&
    hasSpecialChar.value &&
    passwordsMatch.value
  )
})

/*
|--------------------------------------------------------------------------
| REGISTER
|--------------------------------------------------------------------------
*/

const handleRegister = async () => {

  if (!isPasswordValid.value) {

    error.value =
      'La contraseña no cumple los requisitos.'

    return
  }

  loading.value = true

  error.value = ''

  success.value = ''

  try {

    await registerRequest(form.value)

    success.value =
      'Cuenta creada exitosamente. Revisa tu email para verificar tu cuenta.'

    setTimeout(() => {

      router.push('/login')

    }, 3000)

  } catch (err) {

    error.value =
      err.response?.data?.message ||
      'Error al crear la cuenta'

  } finally {

    loading.value = false
  }
}
</script>