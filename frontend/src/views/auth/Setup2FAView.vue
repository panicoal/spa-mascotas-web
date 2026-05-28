<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">

      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Configurar 2FA
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Escanea el código QR usando Google Authenticator,
          Authy o Microsoft Authenticator.
        </p>
      </div>

      <!-- ERROR -->
      <div
        v-if="error"
        class="bg-red-50 border border-red-200 text-red-700 p-3 rounded"
      >
        {{ error }}
      </div>

      <!-- LOADING -->
      <div
        v-if="loadingQr"
        class="text-center text-gray-600"
      >
        Generando QR...
      </div>

      <!-- QR como SVG inline -->
      <div
        v-if="qrSvg"
        class="flex justify-center"
      >
        <div
          class="w-64 h-64"
          v-html="qrSvg"
        ></div>
      </div>

      <!-- QR fallback como img base64 si el SVG inline no funciona -->
      <div
        v-else-if="qrCode && !loadingQr"
        class="flex justify-center"
      >
        <img
          :src="'data:image/svg+xml;base64,' + qrCode"
          alt="QR 2FA"
          class="w-64 h-64"
        />
      </div>

      <!-- SECRET -->
      <div
        v-if="secret"
        class="bg-gray-100 rounded p-4 text-center"
      >
        <p class="text-sm text-gray-700 mb-2">
          Código manual:
        </p>
        <code class="text-sm font-bold break-all">
          {{ secret }}
        </code>
      </div>

      <!-- FORM -->
      <form
        class="space-y-6"
        @submit.prevent="handleEnable2FA"
      >
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Código de verificación
          </label>

          <input
            v-model="code"
            type="text"
            maxlength="6"
            required
            placeholder="123456"
            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50"
        >
          <span
            v-if="loading"
            class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"
          ></span>

          <span v-else>
            Activar 2FA
          </span>
        </button>
      </form>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

import { useRouter } from 'vue-router'

import { useAuthStore } from '@/stores/authStore'

import {
  generate2FARequest,
  enable2FARequest
} from '@/services/authService'

const router = useRouter()

const authStore = useAuthStore()

const qrCode = ref('')
const qrSvg = ref('')
const secret = ref('')
const code = ref('')
const error = ref('')
const loading = ref(false)
const loadingQr = ref(true)

onMounted(async () => {
  await generateQR()
})

const generateQR = async () => {
  error.value = ''
  loadingQr.value = true

  try {
    const response = await generate2FARequest()

    // La respuesta puede traer el SVG directo o en base64
    const rawQr = response.data.qr_code
    const rawSecret = response.data.secret

    secret.value = rawSecret

    if (!rawQr) {
      error.value = 'El servidor no devolvió un código QR.'
      return
    }

    // Intentar decodificar el base64 para obtener el SVG
    try {
      const decoded = atob(rawQr)
      if (decoded.includes('<svg') || decoded.includes('<?xml')) {
        // Es un SVG válido codificado en base64
        qrSvg.value = decoded
        qrCode.value = rawQr
      } else {
        // Usar como base64 directamente
        qrCode.value = rawQr
      }
    } catch {
      // Si atob falla, puede que sea SVG en base64 con espacios u otros chars
      qrCode.value = rawQr
    }

  } catch (err) {
    console.error('Error generando QR:', err)
    if (err.response?.status === 401) {
      error.value = 'Sesión no válida. Por favor inicia sesión nuevamente.'
    } else if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else {
      error.value = 'No se pudo generar el código QR. Verifica que estés autenticado.'
    }
  } finally {
    loadingQr.value = false
  }
}

const handleEnable2FA = async () => {
  loading.value = true
  error.value = ''

  try {
    await enable2FARequest({
      code: code.value
    })

    await authStore.fetchUser()

    const roles = authStore.roles

    if (roles.includes('ADMIN')) {
      router.push('/admin')
    } else if (
      roles.includes('RECEPCION') ||
      roles.includes('GROOMER')
    ) {
      router.push('/staff')
    } else {
      router.push('/client')
    }

  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Código inválido.'
  } finally {
    loading.value = false
  }
}
</script>