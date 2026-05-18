import Setup2FAView
    from '@/views/auth/Setup2FAView.vue'

import {

    createRouter,

    createWebHistory

} from 'vue-router'

import { useAuthStore }
    from '@/stores/authStore'

import LoginView
    from '@/views/auth/LoginView.vue'

import RegisterView
    from '@/views/auth/RegisterView.vue'

import ChangePasswordView
    from '@/views/auth/ChangePasswordView.vue'

import AdminDashboard
    from '@/views/admin/AdminDashboard.vue'

import StaffDashboard
    from '@/views/staff/StaffDashboard.vue'

import ClientDashboard
    from '@/views/client/ClientDashboard.vue'

import VerifyEmailView
    from '@/views/auth/VerifyEmailView.vue'

const routes = [

    {
        path: '/setup-2fa',
        name: 'setup-2fa',
        component: Setup2FAView
    },
    {
        path: '/',

        redirect: '/login'
    },

    {
        path: '/login',

        name: 'login',

        component: LoginView,

        meta: { requiresGuest: true }
    },

    {
        path: '/register',

        name: 'register',

        component: RegisterView,

        meta: { requiresGuest: true }
    },

    {
        path: '/admin',

        name: 'admin',

        component: AdminDashboard,

        meta: {

            requiresAuth: true,

            role: 'ADMIN'
        }
    },

    {
        path: '/staff',

        name: 'staff',

        component: StaffDashboard,

        meta: {

            requiresAuth: true,

            roles: ['RECEPCION', 'GROOMER']
        }
    },

    {
        path: '/client',

        name: 'client',

        component: ClientDashboard,

        meta: {

            requiresAuth: true,

            role: 'CLIENTE'
        }
    },
    {
        path: '/verify-email',

        name: 'verify-email',

        component: VerifyEmailView
    },
    {
        path: '/change-password',

        name: 'change-password',

        component: ChangePasswordView,

        meta: { requiresAuth: true }
    }
]

const router = createRouter({

    history: createWebHistory(),

    routes
})

router.beforeEach((to, from, next) => {

    const authStore = useAuthStore()

    const isAuthenticated = authStore.isAuthenticated

    const userRoles = authStore.roles

    // Si requiere invitado y está autenticado
    if (to.meta.requiresGuest && isAuthenticated) {
        // Si debe cambiar contraseña, ir a esa página
        if (authStore.user?.password_change_required) {
            return next('/change-password')
        }

        if (userRoles.includes('ADMIN')) {

            return next('/admin')

        } else if (userRoles.includes('RECEPCION') || userRoles.includes('GROOMER')) {

            return next('/staff')

        } else {

            return next('/client')

        }
    }

    // Si requiere auth y no está autenticado
    if (to.meta.requiresAuth && !isAuthenticated) {

        return next('/login')
    }

    // Si el usuario debe cambiar contraseña y no está yendo a cambiar-contraseña
    if (isAuthenticated && authStore.user?.password_change_required && to.path !== '/change-password' && to.path !== '/logout') {

        return next('/change-password')
    }

    // Verificar roles específicos
    if (to.meta.role && !userRoles.includes(to.meta.role)) {

        return next('/login')
    }

    if (to.meta.roles && !to.meta.roles.some(role => userRoles.includes(role))) {

        return next('/login')
    }

    next()
})

export default router