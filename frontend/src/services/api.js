import axios from 'axios'
import { useAuthStore } from '@/stores/authStore'

const api = axios.create({
    baseURL: 'http://127.0.0.1:8000/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
})

api.interceptors.request.use(
    (config) => {
        const authStore = useAuthStore()
        if (authStore.token) {
            config.headers.Authorization = `Bearer ${authStore.token}`
        }
        return config
    }
)

api.interceptors.response.use(
    response => response,
    async (error) => {
        // Solo logueamos errores que no sean 401 normales (para no llenar la consola)
        if (error.response?.status !== 401) {
            console.error('API ERROR:', error.response?.status, error.config?.url)
        }

        if (error.response?.status === 401) {
            const authStore = useAuthStore()
            // Limpiar el estado localmente sin hacer petición al servidor
            authStore.token = null
            authStore.user = null
            authStore.roles = []
            authStore.permissions = []
            localStorage.removeItem('token')

            // Solo redirigir si no estamos ya en login/register
            const currentPath = window.location.pathname
            if (currentPath !== '/login' && currentPath !== '/register') {
                window.location.href = '/login'
            }
        }

        return Promise.reject(error)
    }
)

export default api