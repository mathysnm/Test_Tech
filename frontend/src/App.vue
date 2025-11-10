<template>
  <div id="app">
    <header v-if="isAuthenticated" class="app-header">
      <div class="container">
        <div class="logo-section" @click="goToHome" style="cursor: pointer;">
          <svg class="logo-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9 3H4C3.44772 3 3 3.44772 3 4V9C3 9.55228 3.44772 10 4 10H9C9.55228 10 10 9.55228 10 9V4C10 3.44772 9.55228 3 9 3Z" stroke="currentColor" stroke-width="2"/>
            <path d="M20 3H15C14.4477 3 14 3.44772 14 4V9C14 9.55228 14.4477 10 15 10H20C20.5523 10 21 9.55228 21 9V4C21 3.44772 20.5523 3 20 3Z" stroke="currentColor" stroke-width="2"/>
            <path d="M9 14H4C3.44772 14 3 14.4477 3 15V20C3 20.5523 3.44772 21 4 21H9C9.55228 21 10 20.5523 10 20V15C10 14.4477 9.55228 14 9 14Z" stroke="currentColor" stroke-width="2"/>
            <path d="M20 14H15C14.4477 14 14 14.4477 14 15V20C14 20.5523 14.4477 21 15 21H20C20.5523 21 21 20.5523 21 20V15C21 14.4477 20.5523 14 20 14Z" stroke="currentColor" stroke-width="2"/>
          </svg>
          <div class="logo-text">
            <h1 class="logo-title">LegalDesk</h1>
            <span class="logo-subtitle">Gestion des Demandes</span>
          </div>
        </div>
        <nav class="nav">
          <router-link v-if="isManager" to="/dashboard" class="nav-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="7" height="7"/>
              <rect x="14" y="3" width="7" height="7"/>
              <rect x="14" y="14" width="7" height="7"/>
              <rect x="3" y="14" width="7" height="7"/>
            </svg>
            Dashboard
          </router-link>
          <router-link to="/tickets" class="nav-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 11l3 3L22 4"/>
              <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
            </svg>
            Demandes
          </router-link>
          <router-link v-if="isClient" to="/tickets/new" class="nav-link nav-link-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" y1="5" x2="12" y2="19"/>
              <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nouvelle Demande
          </router-link>
        </nav>
        <div class="header-actions">
          <NotificationBell />
        </div>
        <div class="user-section" @click="toggleUserMenu">
          <div class="user-info">
            <span class="user-name">{{ currentUser?.name }}</span>
            <span class="user-role">{{ getRoleLabel(currentUser?.role) }}</span>
          </div>
          <div class="user-avatar">
            {{ getUserInitials(currentUser?.name) }}
          </div>
          
          <!-- Menu dropdown -->
          <div v-if="showUserMenu" class="user-menu">
            <div class="user-menu-header">
              <p class="user-menu-name">{{ currentUser?.name }}</p>
              <p class="user-menu-email">{{ currentUser?.email }}</p>
            </div>
            <button @click="handleLogout" class="user-menu-logout">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
              </svg>
              Se déconnecter
            </button>
          </div>
        </div>
      </div>
    </header>

    <main class="app-main">
      <router-view />
    </main>

    <footer v-if="isAuthenticated" class="app-footer">
      <div class="container">
        <div class="footer-content">
          <p>&copy; 2024 LegalDesk. Tous droits réservés.</p>
          <div class="footer-links">
            <a href="#">Confidentialité</a>
            <a href="#">Conditions d'utilisation</a>
            <a href="#">Support</a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'
import NotificationBell from './components/notifications/NotificationBell.vue'

const router = useRouter()
const authStore = useAuthStore()

const showUserMenu = ref(false)

const isAuthenticated = computed(() => authStore.isAuthenticated)
const currentUser = computed(() => authStore.currentUser)
const isManager = computed(() => authStore.currentUser?.role === 'MANAGER')
const isClient = computed(() => authStore.currentUser?.role === 'CLIENT')

const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value
}

const goToHome = () => {
  router.push('/tickets')
}

const getRoleLabel = (role) => {
  const labels = {
    CLIENT: 'Client',
    AGENT: 'Agent Support',
    MANAGER: 'Responsable'
  }
  return labels[role] || role
}

const getUserInitials = (name) => {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const handleLogout = () => {
  showUserMenu.value = false
  authStore.logout()
  router.push('/login')
}

// Fermer le menu si on clique ailleurs
document.addEventListener('click', (e) => {
  if (!e.target.closest('.user-section')) {
    showUserMenu.value = false
  }
})
</script>

<style>
:root {
  --color-primary: #1e3a5f;
  --color-primary-dark: #152942;
  --color-primary-light: #2c5282;
  --color-accent: #c59d5f;
  --color-accent-light: #d4b276;
  --color-text-primary: #1a202c;
  --color-text-secondary: #4a5568;
  --color-text-muted: #718096;
  --color-bg-page: #f7fafc;
  --color-bg-surface: #ffffff;
  --color-border: #e2e8f0;
  --color-border-light: #edf2f7;
  --color-success: #38a169;
  --color-warning: #d97706;
  --color-error: #dc2626;
  --color-info: #3182ce;
  
  --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  background: var(--color-bg-page);
  color: var(--color-text-primary);
  line-height: 1.6;
  font-size: 14px;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.app-header {
  background: var(--color-bg-surface);
  border-bottom: 1px solid var(--color-border);
  padding: 0;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: var(--shadow-sm);
}

.container {
  max-width: 1600px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 64px;
}

.logo-section {
  display: flex;
  align-items: center;
  gap: 0.875rem;
}

.logo-icon {
  color: var(--color-primary);
  flex-shrink: 0;
}

.logo-text {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.logo-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--color-primary);
  letter-spacing: -0.025em;
  line-height: 1.2;
}

.logo-subtitle {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  font-weight: 500;
  letter-spacing: 0.025em;
  text-transform: uppercase;
}

.nav {
  display: flex;
  gap: 0.5rem;
  flex: 1;
  justify-content: center;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-right: 1rem;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  color: var(--color-text-secondary);
  font-weight: 500;
  font-size: 0.9rem;
  padding: 0.625rem 1.25rem;
  border-radius: 6px;
  transition: all 0.2s ease;
  position: relative;
}

.nav-link svg {
  opacity: 0.7;
  transition: opacity 0.2s;
}

.nav-link:hover {
  color: var(--color-primary);
  background: var(--color-border-light);
}

.nav-link:hover svg {
  opacity: 1;
}

.nav-link.router-link-active {
  color: var(--color-primary);
  background: var(--color-border-light);
  font-weight: 600;
}

.nav-link.router-link-active svg {
  opacity: 1;
}

.user-section {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 0.5rem;
  border-radius: 8px;
  transition: background 0.2s;
  cursor: pointer;
  position: relative;
}

.user-section:hover {
  background: var(--color-border-light);
}

.user-info {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0;
}

.user-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-text-primary);
  line-height: 1.3;
}

.user-role {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  line-height: 1.3;
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
  font-size: 0.875rem;
  font-weight: 600;
}

.user-menu {
  position: absolute;
  top: calc(100% + 0.5rem);
  right: 0;
  background: var(--color-bg-surface);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  box-shadow: var(--shadow-lg);
  min-width: 240px;
  z-index: 1000;
  overflow: hidden;
}

.user-menu-header {
  padding: 1rem;
  border-bottom: 1px solid var(--color-border);
}

.user-menu-name {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--color-text-primary);
  margin-bottom: 0.25rem;
}

.user-menu-email {
  font-size: 0.8rem;
  color: var(--color-text-muted);
}

.user-menu-logout {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.875rem 1rem;
  background: none;
  border: none;
  color: var(--color-error);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
  text-align: left;
}

.user-menu-logout:hover {
  background: var(--color-bg-page);
}

.app-main {
  min-height: calc(100vh - 128px);
  padding: 0;
}

.app-footer {
  background: var(--color-primary-dark);
  color: rgba(255, 255, 255, 0.8);
  border-top: 3px solid var(--color-accent);
  margin-top: 4rem;
}

.app-footer .container {
  height: auto;
  padding: 1.5rem 2rem;
}

.footer-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.footer-content p {
  font-size: 0.875rem;
  margin: 0;
}

.footer-links {
  display: flex;
  gap: 2rem;
}

.footer-links a {
  color: rgba(255, 255, 255, 0.7);
  text-decoration: none;
  font-size: 0.875rem;
  transition: color 0.2s;
}

.footer-links a:hover {
  color: var(--color-accent-light);
}
</style>
