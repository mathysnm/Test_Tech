<template>
  <div class="login-page">
    <div class="login-container">
      <div class="login-header">
        <svg class="logo-icon" width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M9 3H4C3.44772 3 3 3.44772 3 4V9C3 9.55228 3.44772 10 4 10H9C9.55228 10 10 9.55228 10 9V4C10 3.44772 9.55228 3 9 3Z" stroke="currentColor" stroke-width="2"/>
          <path d="M20 3H15C14.4477 3 14 3.44772 14 4V9C14 9.55228 14.4477 10 15 10H20C20.5523 10 21 9.55228 21 9V4C21 3.44772 20.5523 3 20 3Z" stroke="currentColor" stroke-width="2"/>
          <path d="M9 14H4C3.44772 14 3 14.4477 3 15V20C3 20.5523 3.44772 21 4 21H9C9.55228 21 10 20.5523 10 20V15C10 14.4477 9.55228 14 9 14Z" stroke="currentColor" stroke-width="2"/>
          <path d="M20 14H15C14.4477 14 14 14.4477 14 15V20C14 20.5523 14.4477 21 15 21H20C20.5523 21 21 20.5523 21 20V15C21 14.4477 20.5523 14 20 14Z" stroke="currentColor" stroke-width="2"/>
        </svg>
        <h1 class="login-title">LegalDesk</h1>
        <p class="login-subtitle">Système de Gestion des Demandes</p>
      </div>

      <div class="login-content">
        <h2 class="selection-title">Sélectionnez votre profil</h2>
        <p class="selection-description">Choisissez le type de compte pour accéder à l'application</p>

        <div class="user-cards">
          <div 
            v-for="user in availableUsers" 
            :key="user.id"
            class="user-card"
            :class="{ 'selected': selectedUser?.id === user.id }"
            @click="selectUser(user)"
          >
            <div class="user-card-icon" :class="`icon-${user.role.toLowerCase()}`">
              <svg v-if="user.role === 'CLIENT'" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
              <svg v-else-if="user.role === 'AGENT'" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                <path d="M16 3.13a4 4 0 010 7.75"/>
              </svg>
              <svg v-else width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <polyline points="16 11 18 13 22 9"/>
              </svg>
            </div>
            <div class="user-card-content">
              <h3 class="user-card-name">{{ user.name }}</h3>
              <p class="user-card-role">{{ getRoleLabel(user.role) }}</p>
              <p class="user-card-email">{{ user.email }}</p>
            </div>
            <div v-if="selectedUser?.id === user.id" class="user-card-check">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </div>
          </div>
        </div>

        <button 
          class="btn-login" 
          :disabled="!selectedUser"
          @click="handleLogin"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/>
            <polyline points="10 17 15 12 10 7"/>
            <line x1="15" y1="12" x2="3" y2="12"/>
          </svg>
          Accéder à l'application
        </button>
      </div>

      <div class="login-footer">
        <p>© 2024 LegalDesk. Tous droits réservés.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const selectedUser = ref(null)

// Utilisateurs disponibles (simulés depuis la base de données)
const availableUsers = ref([
  {
    id: 1,
    name: 'Marie Dubois',
    email: 'marie.dubois@example.com',
    role: 'CLIENT'
  },
  {
    id: 2,
    name: 'Jean Martin',
    email: 'jean.martin@example.com',
    role: 'CLIENT'
  },
  {
    id: 3,
    name: 'Sophie Bernard',
    email: 'sophie.bernard@example.com',
    role: 'AGENT'
  },
  {
    id: 4,
    name: 'Pierre Dupont',
    email: 'pierre.dupont@example.com',
    role: 'AGENT'
  },
  {
    id: 5,
    name: 'Thomas Petit',
    email: 'thomas.petit@example.com',
    role: 'MANAGER'
  }
])

const selectUser = (user) => {
  selectedUser.value = user
}

const getRoleLabel = (role) => {
  const labels = {
    CLIENT: 'Client',
    AGENT: 'Agent Support',
    MANAGER: 'Responsable'
  }
  return labels[role] || role
}

const handleLogin = async () => {
  if (selectedUser.value) {
    authStore.login(selectedUser.value)
    // Attendre un peu que le store soit complètement synchronisé
    await new Promise(resolve => setTimeout(resolve, 50))
    // Puis naviguer vers /tickets
    await router.push('/tickets')
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--color-primary-dark) 0%, var(--color-primary) 100%);
  padding: 2rem;
}

.login-container {
  background: var(--color-bg-surface);
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  max-width: 900px;
  width: 100%;
  overflow: hidden;
}

.login-header {
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
  color: white;
  padding: 3rem 2rem 2rem;
  text-align: center;
}

.logo-icon {
  color: white;
  margin-bottom: 1rem;
}

.login-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  letter-spacing: -0.025em;
}

.login-subtitle {
  font-size: 1.125rem;
  opacity: 0.9;
  font-weight: 500;
}

.login-content {
  padding: 2.5rem 2rem;
}

.selection-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--color-primary);
  margin-bottom: 0.5rem;
  text-align: center;
}

.selection-description {
  font-size: 0.95rem;
  color: var(--color-text-muted);
  text-align: center;
  margin-bottom: 2rem;
}

.user-cards {
  display: grid;
  gap: 1rem;
  margin-bottom: 2rem;
}

.user-card {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  padding: 1.25rem;
  border: 2px solid var(--color-border);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
}

.user-card:hover {
  border-color: var(--color-primary);
  background: var(--color-bg-page);
  transform: translateX(4px);
}

.user-card.selected {
  border-color: var(--color-primary);
  background: var(--color-bg-page);
  box-shadow: 0 4px 12px rgba(30, 58, 95, 0.15);
}

.user-card-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: white;
}

.icon-client {
  background: linear-gradient(135deg, #3182ce 0%, #2c5282 100%);
}

.icon-agent {
  background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
}

.icon-manager {
  background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
}

.user-card-content {
  flex: 1;
}

.user-card-name {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--color-text-primary);
  margin-bottom: 0.25rem;
}

.user-card-role {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-accent);
  text-transform: uppercase;
  letter-spacing: 0.025em;
  margin-bottom: 0.25rem;
}

.user-card-email {
  font-size: 0.875rem;
  color: var(--color-text-muted);
}

.user-card-check {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--color-primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.btn-login {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 1rem 2rem;
  background: var(--color-primary);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-login:hover:not(:disabled) {
  background: var(--color-primary-dark);
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(30, 58, 95, 0.3);
}

.btn-login:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.login-footer {
  background: var(--color-bg-page);
  padding: 1.5rem;
  text-align: center;
  color: var(--color-text-muted);
  font-size: 0.875rem;
  border-top: 1px solid var(--color-border);
}
</style>
