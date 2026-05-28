<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Iniciar sesión
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          ¿No tienes cuenta?
          <router-link to="/register" class="font-medium text-indigo-600 hover:text-indigo-500">
            Regístrate
          </router-link>
        </p>
      </div>

      <!-- Formulario de login -->
      <form v-if="!requires2FA && !requires2FASetup" class="mt-8 space-y-6" @submit.prevent="handleLogin">
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="email" class="sr-only">Email</label>
            <input
              id="email"
              name="email"
              type="email"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              placeholder="Email"
              v-model="form.email"
            />
          </div>
          <div>
            <label for="password" class="sr-only">Contraseña</label>
            <input
              id="password"
              name="password"
              type="password"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              placeholder="Contraseña"
              v-model="form.password"
            />
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
          >
            <span v-if="loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
            <span v-else>Iniciar sesión</span>
          </button>
        </div>

        <!-- Divider -->
        <div class="relative">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-gray-50 text-gray-500">O continua con</span>
          </div>
        </div>

        <!-- Google OAuth Button -->
        <div>
          <button
            type="button"
            @click="loginWithGoogle"
            :disabled="loading"
            class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
          >
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12.545,10.239v3.821h5.445c-0.712,2.315-2.647,3.972-5.445,3.972c-3.332,0-6.033-2.701-6.033-6.032 c0-3.331,2.701-6.032,6.033-6.032c1.498,0,2.866,0.549,3.921,1.453l2.814-2.814C17.461,2.268,15.365,1.04,12.545,1.04 c-6.302,0-11.405,5.104-11.405,11.405c0,6.301,5.103,11.405,11.405,11.405c6.301,0,11.405-5.104,11.405-11.405 C23.95,11.577,23.884,10.892,23.775,10.239z"/>
            </svg>
            <span class="ml-2">Google</span>
          </button>
        </div>

        <div v-if="error" class="text-red-600 text-sm text-center">
          {{ error }}
        </div>
      </form>

      <!-- Formulario 2FA -->
      <form v-if="requires2FA" class="mt-8 space-y-6" @submit.prevent="handle2FA">
        <div>
          <h3 class="text-lg font-medium text-gray-900 text-center">Verificación de dos factores</h3>
          <p class="mt-2 text-sm text-gray-600 text-center">
            Ingresa el código de tu aplicación autenticadora
          </p>
        </div>
        <div class="rounded-md shadow-sm">
          <div>
            <label for="code" class="sr-only">Código 2FA</label>
            <input
              id="code"
              name="code"
              type="text"
              required
              class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              placeholder="Código de 6 dígitos"
              v-model="twoFactorCode"
              maxlength="6"
            />
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
          >
            <span v-if="loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
            <span v-else>Verificar</span>
          </button>
        </div>

        <div v-if="error" class="text-red-600 text-sm text-center">
          {{ error }}
        </div>

        <div class="text-center">
          <button
            type="button"
            @click="resetLogin"
            class="text-sm text-indigo-600 hover:text-indigo-500"
          >
            Volver al login
          </button>
        </div>
      </form>

      <!-- Setup 2FA para admin -->
      <!-- <div v-if="requires2FASetup" class="mt-8 space-y-6">
        <div>
          <h3 class="text-lg font-medium text-gray-900 text-center">Configurar autenticación de dos factores</h3>
          <p class="mt-2 text-sm text-gray-600 text-center">
            Como administrador, debes configurar 2FA. Escanea el código QR con tu aplicación autenticadora.
          </p>
        </div>

        <div v-if="qrCode" class="flex justify-center">
          <img :src="'data:image/svg+xml;base64,' + qrCode" alt="QR Code" class="w-48 h-48" />
        </div>

        <div v-if="secret" class="text-center text-sm text-gray-600">
          <p>Si no puedes escanear el QR, ingresa manualmente:</p>
          <code class="bg-gray-100 px-2 py-1 rounded">{{ secret }}</code>
        </div>

        <form @submit.prevent="handleEnable2FA">
          <div class="rounded-md shadow-sm">
            <div>
              <label for="setupCode" class="sr-only">Código de verificación</label>
              <input
                id="setupCode"
                name="setupCode"
                type="text"
                required
                class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                placeholder="Código de 6 dígitos"
                v-model="setupCode"
                maxlength="6"
              />
            </div>
          </div>

          <div class="mt-4">
            <button
              type="submit"
              :disabled="loading"
              class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
            >
              <span v-if="loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
              <span v-else>Habilitar 2FA</span>
            </button>
          </div>
        </form>

        <div v-if="error" class="text-red-600 text-sm text-center">
          {{ error }}
        </div>
      </div> -->
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { loginRequest, verify2FARequest, generate2FARequest, enable2FARequest } from '@/services/authService'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  email: '',
  password: ''
})

const loading = ref(false)
const error = ref('')
const requires2FA = ref(false)
const requires2FASetup = ref(false)
const twoFactorCode = ref('')
const setupCode = ref('')
const qrCode = ref('')
const secret = ref('')
const tempToken = ref('')

const handleLogin = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await loginRequest(form)

    if (response.data.requires_2fa) {
      requires2FA.value = true
      tempToken.value = response.data.token || ''
      return
    }

    if (response.data.requires_2fa_setup) {

      authStore.setToken(
        response.data.token
      )

      router.push('/setup-2fa')

      return
    }

    // Login exitoso - usar los datos de la respuesta directamente
    await authStore.loginSuccess(response.data)
    redirectBasedOnRole()

  } catch (err) {
    // Solo procesar 2FA setup si es específicamente ese caso y no un error de autenticación
    if (err.response?.status === 200 && err.response?.data?.requires_2fa_setup) {
      requires2FASetup.value = true
      if (err.response.data.token) {
        authStore.setToken(err.response.data.token)
      }
      await generateQR()
      return
    }

    // Para cualquier otro error (credenciales malas, etc.), mostrar mensaje de error
    error.value = err.response?.data?.message || 'Error al iniciar sesión'
  } finally {
    loading.value = false
  }
}

const handle2FA = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await verify2FARequest({
      email: form.email,
      code: twoFactorCode.value
    })

    await authStore.loginSuccess(response.data)
    redirectBasedOnRole()

  } catch (err) {
    error.value = err.response?.data?.message || 'Código inválido'
  } finally {
    loading.value = false
  }
}

const generateQR = async () => {
  try {
    const response = await generate2FARequest()
    qrCode.value = response.data.qr_code
    secret.value = response.data.secret
  } catch (err) {
    error.value = 'Error al generar QR'
  }
}

const handleEnable2FA = async () => {
  loading.value = true
  error.value = ''

  try {
    await enable2FARequest({ code: setupCode.value })
    // Después de habilitar, intentar login nuevamente
    await authStore.login(form)
    redirectBasedOnRole()

  } catch (err) {
    error.value = err.response?.data?.message || 'Código inválido'
  } finally {
    loading.value = false
  }
}

const resetLogin = () => {
  requires2FA.value = false
  requires2FASetup.value = false
  twoFactorCode.value = ''
  setupCode.value = ''
  qrCode.value = ''
  secret.value = ''
  tempToken.value = ''
  error.value = ''
}

const redirectBasedOnRole = () => {
  const roles = authStore.roles
  
  // Si el usuario debe cambiar contraseña, ir a esa página primero
  if (authStore.user?.password_change_required) {
    router.push('/change-password')
    return
  }
  
  if (roles.includes('ADMIN')) {
    router.push('/admin')
  } else if (roles.includes('RECEPCION') || roles.includes('GROOMER')) {
    router.push('/staff')
  } else {
    router.push('/client')
  }
}

const loginWithGoogle = () => {
  // Redirigir al endpoint de Google OAuth del backend
  const backendUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
  window.location.href = `${backendUrl}/api/auth/google/redirect`
}
</script>