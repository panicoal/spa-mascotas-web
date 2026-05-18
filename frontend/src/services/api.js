import axios from 'axios'

import { useAuthStore }
    from '@/stores/authStore'

const api = axios.create({

    baseURL:
        'http://127.0.0.1:8000/api',

    headers: {

        'Content-Type':
            'application/json',
        'Accept':
            'application/json'
    }
})


api.interceptors.request.use(

    (config) => {

        const authStore =
            useAuthStore()

        if (authStore.token) {

            config.headers.Authorization =
                `Bearer ${authStore.token}`
        }

        return config
    }
)


api.interceptors.response.use(

    response => response,

     async (error) => {

        console.error('API ERROR:', error)

        if (
            error.response?.status === 401
        ) {

            const authStore =
                useAuthStore()

            await authStore.logout()

            window.location.href =
                '/login'
        }

        return Promise.reject(error)
    }
)

export default api