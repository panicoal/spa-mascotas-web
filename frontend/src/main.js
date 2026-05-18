import { createApp } from 'vue'

import { createPinia } from 'pinia'

import './assets/main.css'

import piniaPluginPersistedstate
    from 'pinia-plugin-persistedstate'

import App from './App.vue'

import router from './router'

import { useAuthStore } from '@/stores/authStore'

const app = createApp(App)

const pinia = createPinia()

pinia.use(
    piniaPluginPersistedstate
)

app.use(pinia)

app.use(router)

// Inicializar auth store
const authStore = useAuthStore()
if (authStore.token) {
    authStore.fetchUser()
}

app.mount('#app')