<template>
  <div class="ticket-detail-page">
    <div class="page-wrapper">
      <div class="ticket-container">
        <!-- Header avec retour -->
        <div class="page-header">
          <button @click="goBack" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            Retour à la liste
          </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="loading-state">
          <div class="spinner"></div>
          <p>Chargement du ticket...</p>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="error-state">
          <i class="fas fa-exclamation-triangle"></i>
          <h2>Erreur</h2>
          <p>{{ error }}</p>
          <button @click="loadTicket" class="btn-retry">Réessayer</button>
        </div>

        <!-- Ticket Details -->
        <div v-else-if="ticket" class="ticket-content">
          <!-- Header avec badges -->
          <TicketHeader :ticket="ticket" />

          <!-- Layout 2 colonnes -->
          <div class="content-layout">
            <!-- Sidebar gauche -->
            <TicketSidebar 
              :ticket="ticket" 
              :available-statuses="availableStatuses"
              :updating-status="updatingStatus"
              :can-update-status="canUpdateStatus"
              @change-status="changeStatus"
            />

            <!-- Contenu principal -->
            <main class="main-content">
              <TicketDescription :ticket="ticket" />
              <TicketHistory :ticket="ticket" />
            </main>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { fetchTicket, updateTicketStatus } from '@/services/ticketService'
import TicketHeader from '@/components/tickets/detail/TicketHeader.vue'
import TicketSidebar from '@/components/tickets/detail/TicketSidebar.vue'
import TicketDescription from '@/components/tickets/detail/TicketDescription.vue'
import TicketHistory from '@/components/tickets/detail/TicketHistory.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const ticket = ref(null)
const loading = ref(false)
const error = ref(null)
const updatingStatus = ref(false)

const ticketId = computed(() => parseInt(route.params.id))

const canUpdateStatus = computed(() => {
  return authStore.userRole === 'AGENT' || authStore.userRole === 'MANAGER'
})

const availableStatuses = [
  { value: 'OPEN', label: 'Ouvert', icon: 'fas fa-circle-notch' },
  { value: 'IN_PROGRESS', label: 'En cours', icon: 'fas fa-spinner' },
  { value: 'RESOLVED', label: 'Résolu', icon: 'fas fa-check-circle' },
  { value: 'CLOSED', label: 'Fermé', icon: 'fas fa-times-circle' }
]

async function loadTicket() {
  loading.value = true
  error.value = null
  
  try {
    const response = await fetchTicket(ticketId.value, authStore.currentUser.id)
    if (response.success) {
      ticket.value = response.data
    } else {
      error.value = response.message || 'Impossible de charger le ticket'
    }
  } catch (err) {
    console.error('Error loading ticket:', err)
    error.value = 'Erreur réseau. Impossible de charger le ticket.'
  } finally {
    loading.value = false
  }
}

async function changeStatus(newStatus) {
  if (updatingStatus.value || ticket.value.status === newStatus) return

  updatingStatus.value = true
  
  try {
    const response = await updateTicketStatus(
      ticketId.value,
      newStatus,
      authStore.currentUser.id
    )
    
    if (response.success) {
      await loadTicket()
    } else {
      alert('Erreur: ' + response.message)
    }
  } catch (err) {
    console.error('Error updating status:', err)
    alert('Erreur lors de la mise à jour du statut')
  } finally {
    updatingStatus.value = false
  }
}

function goBack() {
  router.push({ name: 'tickets' })
}

onMounted(() => {
  loadTicket()
})
</script>

<style scoped>
.ticket-detail-page {
  min-height: 100vh;
  background: #f7fafc;
}

.page-wrapper {
  padding: 2rem 0;
  min-height: 100vh;
}

.ticket-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2rem;
  padding-bottom: 2rem;
}

.page-header {
  margin-bottom: 2rem;
}

.btn-back {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-weight: 600;
  color: #4a5568;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-back:hover {
  border-color: #667eea;
  color: #667eea;
  transform: translateX(-4px);
}

.loading-state,
.error-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e2e8f0;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-state p {
  color: #718096;
  font-size: 1.125rem;
}

.error-state i {
  font-size: 3rem;
  color: #f56565;
  margin-bottom: 1rem;
}

.error-state h2 {
  font-size: 1.5rem;
  color: #2d3748;
  margin-bottom: 0.5rem;
}

.error-state p {
  color: #718096;
  margin-bottom: 1.5rem;
}

.btn-retry {
  padding: 0.75rem 1.5rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-retry:hover {
  background: #5568d3;
  transform: translateY(-2px);
}

.ticket-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.content-layout {
  display: grid;
  grid-template-columns: 350px 1fr;
  gap: 1.5rem;
}

.main-content {
  display: flex;
  flex-direction: column;
}

@media (max-width: 1024px) {
  .content-layout {
    grid-template-columns: 1fr;
  }
}
</style>
