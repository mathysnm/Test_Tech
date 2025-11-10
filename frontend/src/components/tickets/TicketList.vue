<template>
  <div class="ticket-list-container">
    <div class="list-header">
      <h2 class="list-title">Liste des Demandes</h2>
      
      <div class="list-actions">
        <div class="search-box">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="search-icon">
            <circle cx="11" cy="11" r="8"/>
            <path d="m21 21-4.35-4.35"/>
          </svg>
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Rechercher une demande..." 
            class="search-input"
          />
        </div>

        <select v-model="filterStatus" class="filter-select">
          <option value="">Tous les statuts</option>
          <option value="NEW">Nouvelle</option>
          <option value="IN_PROGRESS">En cours</option>
          <option value="RESOLVED">Résolue</option>
          <option value="CLOSED">Fermée</option>
        </select>

        <select v-model="filterPriority" class="filter-select">
          <option value="">Toutes les priorités</option>
          <option value="HIGH">Haute</option>
          <option value="MEDIUM">Moyenne</option>
          <option value="LOW">Basse</option>
        </select>

        <!-- Bouton Nouvelle Demande uniquement pour les CLIENTs -->
        <button v-if="authStore.isClient" class="btn-primary" @click="createNew">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Nouvelle Demande
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Chargement des demandes...</p>
    </div>

    <div v-else-if="error" class="error-state">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <p>{{ error }}</p>
    </div>

    <div v-else-if="filteredTickets.length === 0" class="empty-state">
      <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M9 11l3 3L22 4"/>
        <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
      </svg>
      <p>Aucune demande trouvée</p>
    </div>

    <div v-else class="table-container">
      <table class="ticket-table">
        <thead>
          <tr>
            <th class="th-id">#</th>
            <th class="th-title">Titre</th>
            <th class="th-status">Statut</th>
            <th class="th-priority">Priorité</th>
            <th class="th-creator">Créateur</th>
            <th class="th-assignee">Assigné à</th>
            <th class="th-date">Date de création</th>
            <th class="th-actions">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="ticket in filteredTickets" :key="ticket.id" class="ticket-row">
            <td class="td-id">
              <span class="ticket-id">#{{ ticket.id }}</span>
            </td>
            <td class="td-title">
              <div class="title-cell">
                <p class="ticket-title">{{ ticket.title }}</p>
                <p class="ticket-description">{{ truncate(ticket.description, 80) }}</p>
              </div>
            </td>
            <td class="td-status">
              <Badge 
                type="status" 
                :variant="ticket.status.toLowerCase()" 
                :label="formatStatus(ticket.status)"
                size="sm"
              />
            </td>
            <td class="td-priority">
              <Badge 
                type="priority" 
                :variant="ticket.priority.toLowerCase()" 
                :label="formatPriority(ticket.priority)"
                size="sm"
              />
            </td>
            <td class="td-creator">
              <div class="user-cell">
                <div class="user-avatar">
                  {{ getInitials(ticket.creator?.name) }}
                </div>
                <div class="user-info">
                  <p class="user-name">{{ ticket.creator?.name || 'N/A' }}</p>
                  <p class="user-role">{{ formatRole(ticket.creator?.role) }}</p>
                </div>
              </div>
            </td>
            <td class="td-assignee">
              <div v-if="ticket.assignee" class="user-cell">
                <div class="user-avatar">
                  {{ getInitials(ticket.assignee?.name) }}
                </div>
                <div class="user-info">
                  <p class="user-name">{{ ticket.assignee?.name }}</p>
                  <p class="user-role">{{ formatRole(ticket.assignee?.role) }}</p>
                </div>
              </div>
              <span v-else class="not-assigned">Non assigné</span>
            </td>
            <td class="td-date">
              <div class="date-cell">
                <p class="date-value">{{ formatDateShort(ticket.createdAt) }}</p>
                <p class="date-time">{{ formatTime(ticket.createdAt) }}</p>
              </div>
            </td>
            <td class="td-actions">
              <div class="action-buttons">
                <button @click="viewTicket(ticket.id)" class="btn-icon" title="Voir les détails">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                </button>
                <button @click="editTicket(ticket.id)" class="btn-icon" title="Modifier">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                  </svg>
                </button>
                <button @click="deleteTicket(ticket.id)" class="btn-icon btn-danger" title="Supprimer">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useTicketStore } from '../../stores/tickets'
import { useAuthStore } from '../../stores/auth'
import { formatDateShort, formatTime, truncate } from '@/utils/formatting'
import { useTicketFormatters } from '@/composables/useTicketFormatters'
import Badge from '../ui/Badge.vue'

const router = useRouter()
const { getStatusLabel: formatStatus, getPriorityLabel: formatPriority, getRoleLabel: formatRole } = useTicketFormatters()
const ticketStore = useTicketStore()
const authStore = useAuthStore()

const searchQuery = ref('')
const filterStatus = ref('')
const filterPriority = ref('')

const loading = computed(() => ticketStore.loading)
const error = computed(() => ticketStore.error)
const tickets = computed(() => ticketStore.tickets)

const filteredTickets = computed(() => {
  let result = tickets.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(ticket => 
      ticket.title.toLowerCase().includes(query) ||
      ticket.description.toLowerCase().includes(query)
    )
  }

  if (filterStatus.value) {
    result = result.filter(ticket => ticket.status === filterStatus.value)
  }

  if (filterPriority.value) {
    result = result.filter(ticket => ticket.priority === filterPriority.value)
  }

  // Trier par date de création décroissante (plus récents en premier)
  return result.sort((a, b) => {
    const dateA = new Date(a.createdAt)
    const dateB = new Date(b.createdAt)
    return dateB - dateA // Ordre décroissant
  })
})

onMounted(async () => {
  // Attendre que le store auth soit complètement prêt
  await new Promise(resolve => setTimeout(resolve, 200))
  
  if (authStore.isAuthenticated && authStore.currentUser?.id) {
    await ticketStore.fetchTickets()
    await ticketStore.fetchStats()
  }
})

const getInitials = (name) => {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const createNew = () => {
  router.push({ name: 'create-ticket' })
}

const viewTicket = (id) => {
  router.push({ name: 'ticket-detail', params: { id } })
}

const editTicket = (id) => {
  router.push({ name: 'ticket-detail', params: { id } })
}

const deleteTicket = async (id) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cette demande ?')) {
    await ticketStore.deleteTicket(id)
  }
}
</script>

<style scoped>
.ticket-list-container {
  background: var(--color-bg-surface);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  overflow: hidden;
}

.list-header {
  padding: 1.5rem 2rem;
  border-bottom: 1px solid var(--color-border);
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.list-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--color-primary);
  letter-spacing: -0.025em;
}

.list-actions {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  flex-wrap: wrap;
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 280px;
}

.search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--color-text-muted);
}

.search-input {
  width: 100%;
  padding: 0.625rem 1rem 0.625rem 2.75rem;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
}

.filter-select {
  padding: 0.625rem 1rem;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  background: var(--color-bg-surface);
  color: var(--color-text-primary);
  cursor: pointer;
  transition: all 0.2s;
}

.filter-select:hover {
  border-color: var(--color-primary);
}

.filter-select:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
}

.btn-primary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  background: var(--color-primary);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary:hover {
  background: var(--color-primary-dark);
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

.loading-state, .error-state, .empty-state {
  padding: 4rem 2rem;
  text-align: center;
  color: var(--color-text-muted);
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid var(--color-border);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-state svg {
  color: var(--color-error);
  margin-bottom: 1rem;
}

.empty-state svg {
  color: var(--color-text-muted);
  margin-bottom: 1rem;
  opacity: 0.5;
}

.table-container {
  overflow-x: auto;
}

.ticket-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.875rem;
}

.ticket-table thead {
  background: var(--color-bg-page);
  border-bottom: 1px solid var(--color-border);
}

.ticket-table th {
  padding: 0.875rem 1rem;
  text-align: left;
  font-weight: 600;
  color: var(--color-text-secondary);
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.05em;
}

.ticket-table tbody tr {
  border-bottom: 1px solid var(--color-border-light);
  transition: background 0.15s;
}

.ticket-table tbody tr:hover {
  background: var(--color-bg-page);
}

.ticket-table td {
  padding: 1rem;
  vertical-align: middle;
}

.th-id, .td-id {
  width: 80px;
}

.ticket-id {
  font-weight: 600;
  color: var(--color-text-muted);
  font-size: 0.8rem;
}

.title-cell {
  max-width: 350px;
}

.ticket-title {
  font-weight: 600;
  color: var(--color-text-primary);
  margin-bottom: 0.25rem;
  line-height: 1.4;
}

.ticket-description {
  font-size: 0.8rem;
  color: var(--color-text-muted);
  line-height: 1.4;
}

.badge {
  display: inline-flex;
  align-items: center;
  padding: 0.375rem 0.75rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.025em;
  white-space: nowrap;
}

.badge-new {
  background: #dbeafe;
  color: #1e40af;
}

.badge-in_progress {
  background: #fef3c7;
  color: #92400e;
}

.badge-resolved {
  background: #d1fae5;
  color: #065f46;
}

.badge-closed {
  background: #e5e7eb;
  color: #374151;
}

.badge-priority-high {
  background: #fee2e2;
  color: #991b1b;
}

.badge-priority-medium {
  background: #fef3c7;
  color: #92400e;
}

.badge-priority-low {
  background: #d1fae5;
  color: #065f46;
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 600;
  flex-shrink: 0;
}

.user-info {
  min-width: 0;
}

.user-name {
  font-weight: 600;
  color: var(--color-text-primary);
  line-height: 1.3;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-role {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  line-height: 1.3;
}

.not-assigned {
  color: var(--color-text-muted);
  font-style: italic;
  font-size: 0.875rem;
}

.date-cell {
  text-align: left;
}

.date-value {
  font-weight: 500;
  color: var(--color-text-primary);
  line-height: 1.3;
}

.date-time {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  line-height: 1.3;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.btn-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  padding: 0;
  background: var(--color-bg-page);
  border: 1px solid var(--color-border);
  border-radius: 6px;
  color: var(--color-text-secondary);
  cursor: pointer;
  transition: all 0.2s;
}

.btn-icon:hover {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: white;
  transform: translateY(-1px);
}

.btn-icon.btn-danger:hover {
  background: var(--color-error);
  border-color: var(--color-error);
}
</style>
