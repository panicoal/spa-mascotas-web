import Setup2FAView from '@/views/auth/Setup2FAView.vue'

import {
    createRouter,
    createWebHistory
} from 'vue-router'

import { useAuthStore } from '@/stores/authStore'

import LoginView from '@/views/auth/LoginView.vue'
import RegisterView from '@/views/auth/RegisterView.vue'
import ChangePasswordView from '@/views/auth/ChangePasswordView.vue'
import AdminDashboard from '@/views/admin/AdminDashboard.vue'
import ReportesAdmin from '@/views/admin/ReportesAdmin.vue'
import StaffDashboard from '@/views/staff/StaffDashboard.vue'
import ClientDashboard from '@/views/client/ClientDashboard.vue'
import VerifyEmailView from '@/views/auth/VerifyEmailView.vue'

const routes = [
    {
        path: '/admin/inventario',
        name: 'admin-inventario',
        component: () => import('@/views/admin/AdminInventarioView.vue'),
        meta: {
            requiresAuth: true,
            roles: ['ADMIN', 'RECEPCION']
        }
    },
    {
        path: '/admin/reportes',
        name: 'admin-reportes',
        component: ReportesAdmin,
        meta: {
            requiresAuth: true,
            role: 'ADMIN'
        }
    },
    {
        path: '/admin/services',
        component: () => import('@/views/admin/AdminServicesView.vue'),
        meta: {
            requiresAuth: true,
            role: 'ADMIN'
        }
    },
    {
        path: '/tienda',
        name: 'shop',
        component: () => import('@/views/client/ShopView.vue'),
        meta: {
            requiresAuth: true,
            role: 'CLIENTE'
        }
    },
    {
        path: '/pets',
        component: () => import('@/views/client/MyPetsView.vue'),
        meta: {
            requiresAuth: true,
            role: 'CLIENTE'
        }
    },
    {
        path: '/pets/:id',
        component: () => import('@/views/client/PetDetailsView.vue'),
        meta: {
            requiresAuth: true,
            role: 'CLIENTE'
        }
    },
    {
        path: '/setup-2fa',
        name: 'setup-2fa',
        component: Setup2FAView,
        meta: { requiresAuth: true }
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
        path: '/client/appointments',
        name: 'client-appointments',
        component: () => import('@/views/client/ClientAppointmentsView.vue'),
        meta: {
            requiresAuth: true,
            role: 'CLIENTE'
        }
    },
    {
        path: '/reception/calendar',
        name: 'reception-calendar',
        component: () => import('@/views/admin/ReceptionCalendarView.vue'),
        meta: {
            requiresAuth: true,
            roles: ['RECEPCION', 'ADMIN']
        }
    },
    {
        path: '/reception/cierre-caja',
        name: 'reception-cierre-caja',
        component: () => import('@/views/admin/ReceptionCashCloseView.vue'),
        meta: {
            requiresAuth: true,
            roles: ['RECEPCION', 'ADMIN']
        }
    },
    {
        path: '/groomer/workspace',
        name: 'groomer-workspace',
        component: () => import('@/views/staff/GroomerWorkspaceView.vue'),
        meta: {
            requiresAuth: true,
            roles: ['GROOMER', 'ADMIN']
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

router.beforeEach((to) => {
    const authStore = useAuthStore()
    const isAuthenticated = authStore.isAuthenticated
    const userRoles = authStore.roles

    // Si requiere invitado y está autenticado → redirigir al dashboard correcto
    if (to.meta.requiresGuest && isAuthenticated) {
        // Si los roles aún no están cargados, no redirigir para evitar loops
        // (main.js fetchUser garantiza que los roles están disponibles al montar)
        if (userRoles.length === 0) {
            // Dejar pasar temporalmente hasta que los roles se carguen
            return true
        }
        if (authStore.user?.password_change_required) {
            return { name: 'change-password' }
        }
        if (userRoles.includes('ADMIN')) {
            return { name: 'admin' }
        } else if (userRoles.includes('RECEPCION') || userRoles.includes('GROOMER')) {
            return { name: 'staff' }
        } else {
            return { name: 'client' }
        }
    }

    // Si requiere auth y no está autenticado → ir al login
    if (to.meta.requiresAuth && !isAuthenticated) {
        return { name: 'login' }
    }

    // Si el usuario debe cambiar contraseña
    if (
        isAuthenticated &&
        authStore.user?.password_change_required &&
        to.name !== 'change-password'
    ) {
        return { name: 'change-password' }
    }

    // Verificar rol específico (singular)
    // Solo verificar si los roles ya están cargados (evita loops durante inicialización)
    if (to.meta.role && userRoles.length > 0 && !userRoles.includes(to.meta.role)) {
        if (to.name !== 'setup-2fa') {
            return { name: 'login' }
        }
    }

    // Verificar roles múltiples (solo si ya están cargados)
    if (to.meta.roles && userRoles.length > 0 && !to.meta.roles.some(role => userRoles.includes(role))) {
        return { name: 'login' }
    }

    return true
})

export default router