import { createApp } from 'vue'
import { createPinia } from 'pinia'
import './assets/main.css'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import App from './App.vue'
import router from './router'
import { useAuthStore } from '@/stores/authStore'

const app = createApp(App)

const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)

app.use(pinia)
app.use(router)

// Inicializar auth store: si hay token guardado, cargar el usuario
// ANTES de montar la app para que el router guard tenga los roles disponibles
const authStore = useAuthStore()

const init = async () => {
    if (authStore.token) {
        try {
            await authStore.fetchUser()
        } catch {
            // Si falla (token expirado), limpiar sesión
            authStore.token = null
            authStore.user = null
            authStore.roles = []
            authStore.permissions = []
            localStorage.removeItem('token')
        }
    }
    app.mount('#app')
}

init()