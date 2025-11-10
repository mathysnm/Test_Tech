import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import TicketsView from '../views/TicketsView.vue'
import TicketDetailView from '../views/TicketDetailView.vue'
import CreateTicketView from '../views/CreateTicketView.vue'
import ManagerDashboardView from '../views/ManagerDashboardView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: () => {
        // Vérifier si l'utilisateur est connecté au chargement initial
        const savedUser = localStorage.getItem('currentUser')
        return savedUser ? '/tickets' : '/login'
      }
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { requiresGuest: true }
    },
    {
      path: '/tickets',
      name: 'tickets',
      component: TicketsView,
      meta: { requiresAuth: true }
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: ManagerDashboardView,
      meta: { requiresAuth: true, requiresManager: true }
    },
    {
      path: '/tickets/new',
      name: 'create-ticket',
      component: CreateTicketView,
      meta: { requiresAuth: true, requiresClient: true }
    },
    {
      path: '/tickets/:id',
      name: 'ticket-detail',
      component: TicketDetailView,
      meta: { requiresAuth: true }
    }
  ]
})

// Navigation guard pour protéger les routes
router.beforeEach((to, from, next) => {
  // Vérifier directement dans le localStorage
  const savedUser = localStorage.getItem('currentUser')
  const isAuthenticated = !!savedUser
  const user = savedUser ? JSON.parse(savedUser) : null

  if (to.meta.requiresAuth && !isAuthenticated) {
    // Rediriger vers login si non authentifié
    next('/login')
  } else if (to.meta.requiresGuest && isAuthenticated) {
    // Rediriger vers tickets si déjà authentifié
    next('/tickets')
  } else if (to.meta.requiresClient && user?.role !== 'CLIENT') {
    // Rediriger vers tickets si pas CLIENT
    next('/tickets')
  } else if (to.meta.requiresManager && user?.role !== 'MANAGER') {
    // Rediriger vers tickets si pas MANAGER
    next('/tickets')
  } else {
    next()
  }
})

export default router
