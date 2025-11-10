import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import LoginView from '@/views/LoginView.vue'
import { useAuthStore } from '@/stores/auth'

describe('LoginView', () => {
  let wrapper
  let authStore

  beforeEach(() => {
    setActivePinia(createPinia())
    authStore = useAuthStore()
    wrapper = mount(LoginView, {
      global: {
        plugins: [createPinia()],
        stubs: {
          'router-link': true
        }
      }
    })
  })

  it('should render login page', () => {
    expect(wrapper.find('h1').text()).toBe('Connexion')
    expect(wrapper.findAll('.user-card')).toHaveLength(5)
  })

  it('should display all 5 users', () => {
    const userCards = wrapper.findAll('.user-card')
    
    expect(userCards[0].text()).toContain('Marie Dubois')
    expect(userCards[1].text()).toContain('Jean Martin')
    expect(userCards[2].text()).toContain('Sophie Bernard')
    expect(userCards[3].text()).toContain('Pierre Dupont')
    expect(userCards[4].text()).toContain('Thomas Petit')
  })

  it('should show correct roles', () => {
    const roleLabels = wrapper.findAll('.role-label')
    
    expect(roleLabels[0].text()).toBe('Client')
    expect(roleLabels[1].text()).toBe('Client')
    expect(roleLabels[2].text()).toBe('Agent Support')
    expect(roleLabels[3].text()).toBe('Agent Support')
    expect(roleLabels[4].text()).toBe('Manager')
  })

  it('should measure rendering performance', () => {
    const startTime = performance.now()
    
    const testWrapper = mount(LoginView, {
      global: {
        plugins: [createPinia()]
      }
    })
    
    const renderTime = performance.now() - startTime
    
    expect(renderTime).toBeLessThan(50)
    console.log(`✓ LoginView rendered in ${renderTime.toFixed(2)}ms`)
    
    testWrapper.unmount()
  })
})
