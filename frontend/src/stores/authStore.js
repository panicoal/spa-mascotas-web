import { defineStore }
    from 'pinia'

import {

    loginRequest,

    getMe,

    logoutRequest

} from '@/services/authService'

export const useAuthStore =
    defineStore('auth', {

        state: () => ({

            token: localStorage.getItem('token') || null,

            user: null,

            roles: [],

            permissions: []
        }),

        getters: {

            isAuthenticated:
                (state) => !!state.token,

            isAdmin:
                (state) =>
                    state.roles.includes(
                        'ADMIN'
                    ),

            isStaff:
                (state) =>
                    state.roles.includes('RECEPCION') ||
                    state.roles.includes('GROOMER'),

            isClient:
                (state) =>
                    state.roles.includes('CLIENTE')
        },

        actions: {

            async login(credentials) {

                const response =
                    await loginRequest(
                        credentials
                    )

                if (response.data.token) {
                    this.setToken(response.data.token)
                    await this.fetchUser()
                }

                return response
            },

            async loginSuccess(data) {
                this.setToken(data.token)
                this.user = data.user
                this.roles = data.roles || []
                this.permissions = data.permissions || []

                if (!this.roles.length) {
                    await this.fetchUser()
                }
            },

            setToken(token) {
                this.token = token
                localStorage.setItem('token', token)
            },

            async fetchUser() {

                try {

                    const response =
                        await getMe()

                    this.user =
                        response.data.user

                    this.roles =
                        response.data.roles

                    this.permissions =
                        response.data.permissions

                } catch (error) {

                    console.error(error)
                }
            },

            

            async logout() {

                try {

                    await logoutRequest()

                } catch (error) {

                    console.error(error)
                }

                this.token = null

                this.user = null

                this.roles = []

                this.permissions = []

                localStorage.removeItem('token')
            }
        },

        persist: true
    })