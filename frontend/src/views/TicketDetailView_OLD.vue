<template>
  <div class="ticket-detail-page">
    <div class="page-wrapper">
      <div class="container">
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
        <!-- En-tête professionnel avec badges -->
        <div class="ticket-header-professional">
          <div class="header-top">
            <div class="title-section">
              <h1 class="ticket-title">{{ ticket.title }}</h1>
              <div class="ticket-id">#{{ ticket.id }}</div>
            </div>
            <div class="badges-section">
              <Badge 
                type="priority" 
                :variant="ticket.priority.toLowerCase()" 
                :label="getPriorityLabel(ticket.priority)"
                icon="fas fa-flag"
                size="md"
              />
              <Badge 
                type="status" 
                :variant="ticket.status.toLowerCase()" 
                :label="getStatusLabel(ticket.status)"
                :icon="getStatusIcon(ticket.status)"
                size="md"
              />
            </div>
          </div>
        </div>

        <!-- Layout 2 colonnes professionnel -->
        <div class="content-layout">
          <!-- Colonne gauche : Informations compactes -->
          <aside class="sidebar-info">
            <!-- Créateur -->
            <div class="info-block">
              <div class="info-header">
                <i class="fas fa-user-circle"></i>
                <h3>Créateur</h3>
              </div>
              <div class="user-card">
                <div class="user-avatar" :style="{ backgroundColor: getUserColor(ticket.creator.name) }">
                  {{ getInitials(ticket.creator.name) }}
                </div>
                <div class="user-details">
                  <p class="user-name">{{ ticket.creator.name }}</p>
                  <span class="user-role" :class="`role-${ticket.creator.role.toLowerCase()}`">
                    {{ getRoleLabel(ticket.creator.role) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Assigné à -->
            <div class="info-block">
              <div class="info-header">
                <i class="fas fa-user-tag"></i>
                <h3>Assigné à</h3>
              </div>
              <div v-if="ticket.assignee" class="user-card">
                <div class="user-avatar" :style="{ backgroundColor: getUserColor(ticket.assignee.name) }">
                  {{ getInitials(ticket.assignee.name) }}
                </div>
                <div class="user-details">
                  <p class="user-name">{{ ticket.assignee.name }}</p>
                  <span class="user-role" :class="`role-${ticket.assignee.role.toLowerCase()}`">
                    {{ getRoleLabel(ticket.assignee.role) }}
                  </span>
                </div>
              </div>
              <div v-else class="no-assignee">
                <i class="fas fa-user-slash"></i>
                <p>Non assigné</p>
              </div>
            </div>

            <!-- Dates -->
            <div class="info-block">
              <div class="info-header">
                <i class="fas fa-calendar-alt"></i>
                <h3>Dates importantes</h3>
              </div>
              <div class="timeline">
                <div class="timeline-item">
                  <i class="fas fa-plus-circle"></i>
                  <div>
                    <span class="timeline-label">Création</span>
                    <span class="timeline-date">{{ formatDateTime(ticket.createdAt) }}</span>
                  </div>
                </div>
                <div class="timeline-item">
                  <i class="fas fa-edit"></i>
                  <div>
                    <span class="timeline-label">Dernière modification</span>
                    <span class="timeline-date">{{ formatDateTime(ticket.updatedAt) }}</span>
                  </div>
                </div>
                <div class="timeline-item" v-if="ticket.closedAt">
                  <i class="fas fa-check-circle"></i>
                  <div>
                    <span class="timeline-label">Clôture</span>
                    <span class="timeline-date">{{ formatDateTime(ticket.closedAt) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions (AGENT/MANAGER) -->
            <div v-if="canUpdateStatus" class="info-block actions-block">
              <div class="info-header">
                <i class="fas fa-cog"></i>
                <h3>Actions</h3>
              </div>
              <div class="action-buttons">
                <button
                  v-for="status in availableStatuses"
                  :key="status.value"
                  @click="changeStatus(status.value)"
                  :disabled="updatingStatus || ticket.status === status.value"
                  class="btn-action"
                  :class="[
                    `btn-${status.value.toLowerCase()}`,
                    { 'active': ticket.status === status.value },
                    { 'disabled': updatingStatus }
                  ]"
                >
                  <i :class="status.icon"></i>
                  <span>{{ status.label }}</span>
                </button>
              </div>
            </div>
          </aside>

          <!-- Colonne droite : Contenu principal -->
          <main class="main-content">
            <!-- Description de la demande -->
            <section class="content-section">
              <div class="section-header">
                <i class="fas fa-file-alt"></i>
                <h2>Description de la demande</h2>
              </div>
              <div class="description-content">
                <p class="description">{{ ticket.description }}</p>
              </div>
            </section>

            <!-- Historique des modifications (placeholder) -->
            <section class="content-section history-section">
              <div class="section-header">
                <i class="fas fa-history"></i>
                <h2>Historique</h2>
              </div>
              <div class="history-content">
                <div class="history-item">
                  <div class="history-icon created">
                    <i class="fas fa-plus"></i>
                  </div>
                  <div class="history-details">
                    <p class="history-action">
                      <strong>{{ ticket.creator.name }}</strong> a créé le ticket
                    </p>
                    <span class="history-date">{{ formatDateTime(ticket.createdAt) }}</span>
                  </div>
                </div>
                <div v-if="ticket.assignee" class="history-item">
                  <div class="history-icon assigned">
                    <i class="fas fa-user-check"></i>
                  </div>
                  <div class="history-details">
                    <p class="history-action">
                      Ticket assigné à <strong>{{ ticket.assignee.name }}</strong>
                    </p>
                    <span class="history-date">{{ formatDateTime(ticket.updatedAt) }}</span>
                  </div>
                </div>
                <div v-if="ticket.status !== 'OPEN'" class="history-item">
                  <div class="history-icon status-changed">
                    <i class="fas fa-sync-alt"></i>
                  </div>
                  <div class="history-details">
                    <p class="history-action">
                      Statut changé en <strong>{{ getStatusLabel(ticket.status) }}</strong>
                    </p>
                    <span class="history-date">{{ formatDateTime(ticket.updatedAt) }}</span>
                  </div>
                </div>
              </div>
            </section>
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
import { getUserColor, getInitials } from '@/utils/userUtils'
import { formatDateTime } from '@/utils/formatting'
import { useTicketFormatters } from '@/composables/useTicketFormatters'
import Badge from '@/components/ui/Badge.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const { getStatusLabel, getStatusIcon, getPriorityLabel, getRoleLabel } = useTicketFormatters()

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
      // Recharger le ticket pour avoir les données à jour
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
/* ===== BASE STYLES ===== */
.ticket-detail-page {
  background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
  min-height: calc(100vh - 64px); /* 64px = hauteur header */
}

.page-wrapper {
  max-width: 1600px;
  margin: 0 auto;
  padding: 2rem;
}

.container {
  max-width: 100%;
  margin: 0;
  padding: 0;
}

/* ===== HEADER ===== */
.page-header {
  margin-bottom: 1.5rem;
}

.btn-back {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  color: #374151;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.btn-back:hover {
  background: #f9fafb;
  border-color: #2563eb;
  color: #2563eb;
  transform: translateX(-4px);
  box-shadow: 0 4px 6px rgba(37, 99, 235, 0.15);
}

/* ===== LOADING & ERROR ===== */
.loading-state,
.error-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e5e7eb;
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-state i {
  font-size: 3rem;
  color: #dc2626;
  margin-bottom: 1rem;
}

.btn-retry {
  margin-top: 1rem;
  padding: 0.75rem 1.5rem;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-retry:hover {
  background: #1d4ed8;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(37, 99, 235, 0.3);
}

/* ===== TICKET CONTENT ===== */
.ticket-content {
  background: transparent;
  padding: 0;
}

/* ===== PROFESSIONAL HEADER ===== */
.ticket-header-professional {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border-left: 6px solid #2563eb;
}

.header-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 2rem;
}

.title-section {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.ticket-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #111827;
  margin: 0;
  line-height: 1.3;
}

.ticket-id {
  padding: 0.4rem 0.8rem;
  background: #f3f4f6;
  color: #6b7280;
  font-size: 0.9rem;
  font-weight: 600;
  border-radius: 8px;
  font-family: 'Courier New', monospace;
}

.badges-section {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.badge-pro {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1.2rem;
  border-radius: 10px;
  font-size: 0.9rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Priority badges */
.badge-pro.priority-high {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  color: #991b1b;
  border: 2px solid #f87171;
}

.badge-pro.priority-medium {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  color: #92400e;
  border: 2px solid #fbbf24;
}

.badge-pro.priority-low {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  color: #065f46;
  border: 2px solid #34d399;
}

/* Status badges */
.badge-pro.status-open {
  background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
  color: #5b21b6;
  border: 2px solid #a78bfa;
}

.badge-pro.status-in_progress {
  background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
  color: #1e40af;
  border: 2px solid #60a5fa;
}

.badge-pro.status-resolved {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  color: #065f46;
  border: 2px solid #34d399;
}

.badge-pro.status-closed {
  background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
  color: #374151;
  border: 2px solid #9ca3af;
}

/* ===== LAYOUT 2 COLONNES ===== */
.content-layout {
  display: grid;
  grid-template-columns: 340px 1fr;
  gap: 1.5rem;
  align-items: start;
}

/* ===== SIDEBAR (Gauche) ===== */
.sidebar-info {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.info-block {
  background: white;
  border-radius: 12px;
  padding: 1.25rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  border: 1px solid #e5e7eb;
}

.info-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #f3f4f6;
}

.info-header i {
  color: #2563eb;
  font-size: 1.1rem;
}

.info-header h3 {
  font-size: 0.95rem;
  font-weight: 700;
  color: #111827;
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* User cards */
.user-card {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-avatar {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.1rem;
  color: white;
  flex-shrink: 0;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.user-details {
  flex: 1;
}

.user-name {
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
  margin: 0 0 0.25rem 0;
}

.user-role {
  display: inline-block;
  padding: 0.25rem 0.6rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.user-role.role-client {
  background: #dbeafe;
  color: #1e40af;
}

.user-role.role-agent {
  background: #ede9fe;
  color: #5b21b6;
}

.user-role.role-manager {
  background: #fce7f3;
  color: #9f1239;
}

.no-assignee {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #9ca3af;
  font-style: italic;
  padding: 0.75rem;
  background: #f9fafb;
  border-radius: 8px;
}

.no-assignee i {
  font-size: 1.2rem;
}

.no-assignee p {
  margin: 0;
}

/* Timeline */
.timeline {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.timeline-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}

.timeline-item i {
  color: #2563eb;
  font-size: 1rem;
  margin-top: 0.2rem;
}

.timeline-item.closed i {
  color: #10b981;
}

.timeline-item div {
  flex: 1;
}

.timeline-label {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  color: #6b7280;
  margin-bottom: 0.15rem;
}

.timeline-date {
  display: block;
  font-size: 0.9rem;
  color: #111827;
  font-weight: 500;
}

/* Action buttons */
.actions-block {
  border-left: 4px solid #2563eb;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.btn-action {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.85rem 1rem;
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  color: #374151;
}

.btn-action:hover:not(:disabled) {
  border-color: #2563eb;
  background: #eff6ff;
  transform: translateX(4px);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
}

.btn-action:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-action.active {
  border-color: #2563eb;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
}

.btn-action.active i {
  color: white;
}

.btn-action i {
  font-size: 1rem;
}

/* ===== MAIN CONTENT (Droite) ===== */
.main-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.content-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  border: 1px solid #e5e7eb;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #f3f4f6;
}

.section-header i {
  color: #2563eb;
  font-size: 1.4rem;
}

.section-header h2 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

/* Description */
.description-content {
  background: #f9fafb;
  border-radius: 10px;
  padding: 1.5rem;
  border-left: 4px solid #2563eb;
}

.description {
  color: #374151;
  line-height: 1.7;
  font-size: 1rem;
  white-space: pre-wrap;
  word-wrap: break-word;
  overflow-wrap: break-word;
  max-width: 100%;
  margin: 0;
}

/* History */
.history-section {
  border-left: 4px solid #10b981;
}

.history-content {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.history-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 10px;
  border-left: 3px solid #e5e7eb;
  transition: all 0.3s;
}

.history-item:hover {
  background: #eff6ff;
  border-left-color: #2563eb;
}

.history-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 1.1rem;
}

.history-icon.created {
  background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
  color: #1e40af;
}

.history-icon.assigned {
  background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
  color: #5b21b6;
}

.history-icon.status-changed {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  color: #065f46;
}

.history-details {
  flex: 1;
}

.history-action {
  font-size: 0.95rem;
  color: #374151;
  margin: 0 0 0.3rem 0;
  line-height: 1.5;
}

.history-action strong {
  color: #111827;
  font-weight: 700;
}

.history-date {
  font-size: 0.85rem;
  color: #6b7280;
  font-style: italic;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
  .content-layout {
    grid-template-columns: 1fr;
  }

  .sidebar-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  }
}

@media (max-width: 768px) {
  .header-top {
    flex-direction: column;
    align-items: flex-start;
  }

  .ticket-title {
    font-size: 1.5rem;
  }

  .sidebar-info {
    grid-template-columns: 1fr;
  }
}
</style>
