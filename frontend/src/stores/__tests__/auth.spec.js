import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '@/stores/auth'

describe('Auth Store', () => {
  beforeEach(() => {
    // Créer une nouvelle instance de Pinia pour chaque test
    setActivePinia(createPinia())
    // Clear localStorage
    localStorage.clear()
  })

  it('should initialize with no user', () => {
    const store = useAuthStore()
    expect(store.currentUser).toBeNull()
    expect(store.isAuthenticated).toBe(false)
  })

  it('should login a user', () => {
    const store = useAuthStore()
    const user = {
      id: 1,
      name: 'Marie Dubois',
      email: 'marie.dubois@client.com',
      role: 'CLIENT'
    }

    store.login(user)

    expect(store.currentUser).toEqual(user)
    expect(store.isAuthenticated).toBe(true)
    expect(localStorage.getItem('currentUser')).toBeTruthy()
  })

  it('should logout a user', () => {
    const store = useAuthStore()
    const user = {
      id: 1,
      name: 'Marie Dubois',
      email: 'marie.dubois@client.com',
      role: 'CLIENT'
    }

    store.login(user)
    expect(store.isAuthenticated).toBe(true)

    store.logout()

    expect(store.currentUser).toBeNull()
    expect(store.isAuthenticated).toBe(false)
    expect(localStorage.getItem('currentUser')).toBeNull()
  })

  it('should restore session from localStorage', () => {
    const user = {
      id: 1,
      name: 'Marie Dubois',
      email: 'marie.dubois@client.com',
      role: 'CLIENT'
    }

    localStorage.setItem('currentUser', JSON.stringify(user))

    const store = useAuthStore()
    store.restoreSession()

    expect(store.currentUser).toEqual(user)
    expect(store.isAuthenticated).toBe(true)
  })

  it('should identify CLIENT role correctly', () => {
    const store = useAuthStore()
    const user = {
      id: 1,
      name: 'Marie Dubois',
      email: 'marie.dubois@client.com',
      role: 'CLIENT'
    }

    store.login(user)

    expect(store.isClient).toBe(true)
    expect(store.isAgent).toBe(false)
    expect(store.isManager).toBe(false)
  })

  it('should identify AGENT role correctly', () => {
    const store = useAuthStore()
    const user = {
      id: 3,
      name: 'Sophie Bernard',
      email: 'sophie.bernard@support.com',
      role: 'AGENT'
    }

    store.login(user)

    expect(store.isClient).toBe(false)
    expect(store.isAgent).toBe(true)
    expect(store.isManager).toBe(false)
  })

  it('should identify MANAGER role correctly', () => {
    const store = useAuthStore()
    const user = {
      id: 5,
      name: 'Thomas Petit',
      email: 'thomas.petit@manager.com',
      role: 'MANAGER'
    }

    store.login(user)

    expect(store.isClient).toBe(false)
    expect(store.isAgent).toBe(false)
    expect(store.isManager).toBe(true)
  })
})
