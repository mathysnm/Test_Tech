/**
 * Store Pinia - Tickets
 * Gestion de l'état des tickets avec services séparés
 */

import { defineStore } from 'pinia'
import { useAuthStore } from './auth'
import * as ticketService from '@/services/ticketService'

export const useTicketStore = defineStore('tickets', {
  state: () => ({
    tickets: [],
    currentTicket: null,
    stats: {
      total: 0,
      byStatus: {},
      byPriority: {}
    },
    loading: false,
    error: null
  }),

  getters: {
    /**
     * Filtrer les tickets par statut
     */
    ticketsByStatus: (state) => (status) => {
      return state.tickets.filter(ticket => ticket.status === status)
    },

    /**
     * Filtrer les tickets par priorité
     */
    ticketsByPriority: (state) => (priority) => {
      return state.tickets.filter(ticket => ticket.priority === priority)
    },

    /**
     * Tickets ouverts (NEW + IN_PROGRESS)
     */
    openTickets: (state) => {
      return state.tickets.filter(ticket => 
        ticket.status === 'NEW' || ticket.status === 'IN_PROGRESS'
      )
    },

    /**
     * Tickets fermés (RESOLVED + CLOSED)
     */
    closedTickets: (state) => {
      return state.tickets.filter(ticket => 
        ticket.status === 'RESOLVED' || ticket.status === 'CLOSED'
      )
    }
  },

  actions: {
    /**
     * Récupère tous les tickets filtrés selon le rôle de l'utilisateur
     * @throws {Error} Si l'utilisateur n'est pas authentifié
     */
    async fetchTickets() {
      this.loading = true
      this.error = null

      try {
        const authStore = useAuthStore()
        const userId = authStore.currentUser?.id

        if (!userId) {
          throw new Error('User not authenticated')
        }

        const data = await ticketService.fetchTickets(userId)
        
        if (data.success) {
          this.tickets = data.data
        } else {
          throw new Error(data.message || 'Failed to fetch tickets')
        }
      } catch (error) {
        this.error = error.response?.data?.message || error.message
        console.error('Error fetching tickets:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Récupère les statistiques des tickets
     * @throws {Error} Si l'utilisateur n'est pas authentifié
     */
    async fetchStats() {
      try {
        const authStore = useAuthStore()
        const userId = authStore.currentUser?.id

        if (!userId) {
          throw new Error('User not authenticated')
        }

        const data = await ticketService.fetchStats(userId)
        
        if (data.success) {
          // Transformer les clés snake_case en PascalCase pour correspondre au composant
          this.stats = {
            total: data.data.total,
            byStatus: {
              NEW: data.data.by_status?.open || 0,
              IN_PROGRESS: data.data.by_status?.in_progress || 0,
              RESOLVED: data.data.by_status?.resolved || 0,
              CLOSED: data.data.by_status?.closed || 0
            },
            byPriority: {
              HIGH: data.data.by_priority?.high || 0,
              MEDIUM: data.data.by_priority?.medium || 0,
              LOW: data.data.by_priority?.low || 0
            }
          }
        } else {
          throw new Error(data.message || 'Failed to fetch stats')
        }
      } catch (error) {
        console.error('Error fetching stats:', error)
        throw error
      }
    },

    /**
     * Récupère un ticket spécifique
     * @param {number} ticketId - ID du ticket
     */
    async fetchTicket(ticketId) {
      this.loading = true
      this.error = null

      try {
        const authStore = useAuthStore()
        const userId = authStore.currentUser?.id

        if (!userId) {
          throw new Error('User not authenticated')
        }

        const data = await ticketService.fetchTicket(ticketId, userId)
        
        if (data.success) {
          this.currentTicket = data.data
        } else {
          throw new Error(data.message || 'Failed to fetch ticket')
        }
      } catch (error) {
        this.error = error.response?.data?.message || error.message
        console.error('Error fetching ticket:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Crée un nouveau ticket
     * @param {Object} ticketData - Données du ticket
     */
    async createTicket(ticketData) {
      this.loading = true
      this.error = null

      try {
        const data = await ticketService.createTicket(ticketData)
        
        if (data.success) {
          this.tickets.unshift(data.data) // Ajouter au début
          return data.data
        } else {
          throw new Error(data.message || 'Failed to create ticket')
        }
      } catch (error) {
        this.error = error.response?.data?.message || error.message
        console.error('Error creating ticket:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Met à jour un ticket
     * @param {number} ticketId - ID du ticket
     * @param {Object} ticketData - Données à mettre à jour
     */
    async updateTicket(ticketId, ticketData) {
      this.loading = true
      this.error = null

      try {
        const authStore = useAuthStore()
        const userId = authStore.currentUser?.id

        if (!userId) {
          throw new Error('User not authenticated')
        }

        const data = await ticketService.updateTicket(ticketId, ticketData, userId)
        
        if (data.success) {
          // Mettre à jour dans la liste
          const index = this.tickets.findIndex(t => t.id === ticketId)
          if (index !== -1) {
            this.tickets[index] = data.data
          }
          // Mettre à jour le ticket courant si c'est le même
          if (this.currentTicket?.id === ticketId) {
            this.currentTicket = data.data
          }
          return data.data
        } else {
          throw new Error(data.message || 'Failed to update ticket')
        }
      } catch (error) {
        this.error = error.response?.data?.message || error.message
        console.error('Error updating ticket:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Supprime un ticket
     * @param {number} ticketId - ID du ticket
     */
    async deleteTicket(ticketId) {
      this.loading = true
      this.error = null

      try {
        const authStore = useAuthStore()
        const userId = authStore.currentUser?.id

        if (!userId) {
          throw new Error('User not authenticated')
        }

        const data = await ticketService.deleteTicket(ticketId, userId)
        
        if (data.success) {
          // Retirer de la liste
          this.tickets = this.tickets.filter(t => t.id !== ticketId)
          // Vider le ticket courant si c'est le même
          if (this.currentTicket?.id === ticketId) {
            this.currentTicket = null
          }
        } else {
          throw new Error(data.message || 'Failed to delete ticket')
        }
      } catch (error) {
        this.error = error.response?.data?.message || error.message
        console.error('Error deleting ticket:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Réinitialise l'état
     */
    resetState() {
      this.tickets = []
      this.currentTicket = null
      this.stats = {
        total: 0,
        byStatus: {},
        byPriority: {}
      }
      this.loading = false
      this.error = null
    }
  }
})
