import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    currentUser: null,
    isAuthenticated: false
  }),

  getters: {
    userRole: (state) => state.currentUser?.role,
    isClient: (state) => state.currentUser?.role === 'CLIENT',
    isAgent: (state) => state.currentUser?.role === 'AGENT',
    isManager: (state) => state.currentUser?.role === 'MANAGER'
  },

  actions: {
    login(user) {
      this.currentUser = user
      this.isAuthenticated = true
      // Sauvegarder dans le localStorage
      localStorage.setItem('currentUser', JSON.stringify(user))
    },

    logout() {
      this.currentUser = null
      this.isAuthenticated = false
      localStorage.removeItem('currentUser')
    },

    // Restaurer la session depuis le localStorage
    restoreSession() {
      const savedUser = localStorage.getItem('currentUser')
      if (savedUser) {
        this.currentUser = JSON.parse(savedUser)
        this.isAuthenticated = true
      }
    }
  }
})
