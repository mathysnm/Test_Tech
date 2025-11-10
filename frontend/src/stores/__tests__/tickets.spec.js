import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useTicketStore } from '@/stores/tickets'
import { useAuthStore } from '@/stores/auth'
import axios from 'axios'
import MockAdapter from 'axios-mock-adapter'

describe('Ticket Store', () => {
  let mock
  let authStore
  let ticketStore

  beforeEach(() => {
    setActivePinia(createPinia())
    mock = new MockAdapter(axios)
    authStore = useAuthStore()
    ticketStore = useTicketStore()
    localStorage.clear()

    // Login as MANAGER by default
    authStore.login({
      id: 5,
      name: 'Thomas Petit',
      email: 'thomas.petit@manager.com',
      role: 'MANAGER'
    })
  })

  afterEach(() => {
    mock.restore()
  })

  describe('fetchTickets', () => {
    it('should fetch tickets successfully with performance tracking', async () => {
      const mockTickets = [
        {
          id: 1,
          title: 'Test Ticket 1',
          status: 'OPEN',
          priority: 'HIGH',
          creator: { id: 1, name: 'Marie' },
          assignee: { id: 3, name: 'Sophie' }
        },
        {
          id: 2,
          title: 'Test Ticket 2',
          status: 'IN_PROGRESS',
          priority: 'MEDIUM',
          creator: { id: 2, name: 'Jean' },
          assignee: { id: 4, name: 'Pierre' }
        }
      ]

      mock.onGet('http://localhost:8000/api/tickets', { params: { user_id: 5 } })
        .reply(200, {
          success: true,
          count: 2,
          data: mockTickets,
          user_role: 'MANAGER'
        })

      const startTime = performance.now()
      await ticketStore.fetchTickets()
      const duration = performance.now() - startTime

      expect(ticketStore.tickets).toEqual(mockTickets)
      expect(ticketStore.loading).toBe(false)
      expect(ticketStore.error).toBeNull()
      
      // Performance: should complete in less than 100ms (mock)
      expect(duration).toBeLessThan(100)
      console.log(`✓ fetchTickets completed in ${duration.toFixed(2)}ms`)
    })

    it('should handle authentication error', async () => {
      authStore.logout()

      await ticketStore.fetchTickets()

      expect(ticketStore.error).toBe('User not authenticated')
      expect(ticketStore.tickets).toEqual([])
    })

    it('should handle API error', async () => {
      mock.onGet('http://localhost:8000/api/tickets').reply(500, {
        success: false,
        message: 'Internal server error'
      })

      await ticketStore.fetchTickets()

      expect(ticketStore.error).toBeTruthy()
      expect(ticketStore.loading).toBe(false)
    })
  })

  describe('fetchStats', () => {
    it('should fetch stats successfully with performance tracking', async () => {
      const mockStats = {
        total: 8,
        by_status: {
          open: 2,
          in_progress: 3,
          resolved: 2,
          closed: 1
        },
        by_priority: {
          high: 3,
          medium: 3,
          low: 2
        }
      }

      mock.onGet('http://localhost:8000/api/tickets/stats', { params: { user_id: 5 } })
        .reply(200, {
          success: true,
          data: mockStats,
          user_role: 'MANAGER'
        })

      const startTime = performance.now()
      await ticketStore.fetchStats()
      const duration = performance.now() - startTime

      expect(ticketStore.stats.total).toBe(8)
      expect(ticketStore.stats.byStatus.NEW).toBe(2)
      expect(ticketStore.stats.byStatus.IN_PROGRESS).toBe(3)
      expect(ticketStore.stats.byPriority.HIGH).toBe(3)
      
      // Performance: should complete in less than 100ms (mock)
      expect(duration).toBeLessThan(100)
      console.log(`✓ fetchStats completed in ${duration.toFixed(2)}ms`)
    })

    it('should handle authentication error', async () => {
      authStore.logout()

      await ticketStore.fetchStats()

      // Should not throw, just log error
      expect(ticketStore.stats.total).toBe(0)
    })
  })

  describe('getters', () => {
    beforeEach(() => {
      ticketStore.tickets = [
        { id: 1, status: 'OPEN', priority: 'HIGH' },
        { id: 2, status: 'IN_PROGRESS', priority: 'MEDIUM' },
        { id: 3, status: 'RESOLVED', priority: 'HIGH' },
        { id: 4, status: 'CLOSED', priority: 'LOW' }
      ]
    })

    it('should filter tickets by status', () => {
      expect(ticketStore.ticketsByStatus('OPEN')).toHaveLength(1)
      expect(ticketStore.ticketsByStatus('IN_PROGRESS')).toHaveLength(1)
      expect(ticketStore.ticketsByStatus('RESOLVED')).toHaveLength(1)
    })

    it('should filter tickets by priority', () => {
      expect(ticketStore.ticketsByPriority('HIGH')).toHaveLength(2)
      expect(ticketStore.ticketsByPriority('MEDIUM')).toHaveLength(1)
      expect(ticketStore.ticketsByPriority('LOW')).toHaveLength(1)
    })

    it('should return open tickets', () => {
      const openTickets = ticketStore.openTickets
      expect(openTickets).toHaveLength(2)
      expect(openTickets.every(t => t.status === 'OPEN' || t.status === 'IN_PROGRESS')).toBe(true)
    })

    it('should return closed tickets', () => {
      const closedTickets = ticketStore.closedTickets
      expect(closedTickets).toHaveLength(2)
      expect(closedTickets.every(t => t.status === 'RESOLVED' || t.status === 'CLOSED')).toBe(true)
    })
  })

  describe('createTicket', () => {
    it('should create ticket and measure performance', async () => {
      const newTicket = {
        title: 'New Test Ticket',
        description: 'Test description',
        priority: 'HIGH'
      }

      mock.onPost('http://localhost:8000/api/tickets').reply(201, {
        success: true,
        message: 'Ticket created successfully',
        data: { id: 9, ...newTicket, status: 'OPEN' }
      })

      const startTime = performance.now()
      const result = await ticketStore.createTicket(newTicket)
      const duration = performance.now() - startTime

      expect(result).toBeTruthy()
      expect(result.id).toBe(9)
      
      expect(duration).toBeLessThan(100)
      console.log(`✓ createTicket completed in ${duration.toFixed(2)}ms`)
    })
  })
})
