<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow">
      <div class="text-center">
        <h2 class="text-3xl font-extrabold text-gray-900">Verificar correo</h2>
        <p class="mt-2 text-sm text-gray-600">Estamos verificando tu email, espera un momento.</p>
      </div>

      <div class="mt-6 text-center">
        <div v-if="loading" class="text-gray-700">Verificando...</div>
        <div v-if="success" class="text-green-600">{{ success }}</div>
        <div v-if="error" class="text-red-600">{{ error }}</div>
      </div>

      <div class="mt-6 text-center">
        <button
          @click="goToLogin"
          class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
        >
          Ir al login
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { verifyEmailRequest } from '@/services/authService'

const route = useRoute()
const router = useRouter()
const loading = ref(true)
const error = ref('')
const success = ref('')

const verifyEmail = async () => {
  loading.value = true
  error.value = ''
  success.value = ''

  try {
    const response = await verifyEmailRequest({
      token: route.query.token,
      id: route.query.id
    })
    success.value = response.data.message || 'Correo verificado correctamente.'
  } catch (err) {
    error.value = err.response?.data?.message || 'No se pudo verificar el correo.'
  } finally {
    loading.value = false
  }
}

const goToLogin = () => {
  router.push('/login')
}

onMounted(() => {
  if (!route.query.token || !route.query.id) {
    error.value = 'Parámetros de verificación faltantes.'
    loading.value = false
    return
  }

  verifyEmail()
})
</script>
