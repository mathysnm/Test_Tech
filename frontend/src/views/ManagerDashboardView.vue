<template>
  <div class="manager-dashboard">
    <!-- Header épuré -->
    <div class="dashboard-header">
      <div class="header-content">
        <div>
          <h1 class="dashboard-title">Tableau de Bord</h1>
          <p class="dashboard-subtitle">Analyse et supervision de l'activité</p>
        </div>
        <div class="header-actions">
          <button @click="loadDashboardData" class="btn-refresh" :disabled="loading">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="23 4 23 10 17 10"></polyline>
              <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
            </svg>
            Actualiser
          </button>
        </div>
      </div>
    </div>

    <!-- Messages d'état -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Chargement des données analytiques...</p>
    </div>

    <div v-if="error" class="error-message">
      <div class="error-icon">⚠️</div>
      <p>{{ error }}</p>
      <button @click="loadDashboardData" class="btn-retry">Réessayer</button>
    </div>

    <!-- Dashboard content -->
    <div v-if="!loading && !error" class="dashboard-content">
      
      <!-- KPIs Principaux -->
      <div class="kpi-grid">
        <!-- Temps de résolution moyen -->
        <div class="kpi-card">
          <div class="kpi-header">
            <span class="kpi-label">Temps Moyen de Résolution</span>
            <div class="kpi-icon-wrapper kpi-time">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
              </svg>
            </div>
          </div>
          <div class="kpi-value">{{ formatDuration(analytics.avgResolutionTime) }}</div>
          <div class="kpi-change" :class="analytics.resolutionTrend > 0 ? 'negative' : 'positive'">
            {{ analytics.resolutionTrend > 0 ? '+' : '' }}{{ analytics.resolutionTrend }}%
            <span class="kpi-change-label">vs. semaine précédente</span>
          </div>
        </div>

        <!-- Tickets par jour -->
        <div class="kpi-card">
          <div class="kpi-header">
            <span class="kpi-label">Tickets Aujourd'hui</span>
            <div class="kpi-icon-wrapper kpi-tickets">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
              </svg>
            </div>
          </div>
          <div class="kpi-value">{{ analytics.ticketsToday }}</div>
          <div class="kpi-change neutral">
            Moyenne : {{ analytics.avgTicketsPerDay }} tickets/jour
          </div>
        </div>

        <!-- Taux de résolution équipe -->
        <div class="kpi-card">
          <div class="kpi-header">
            <span class="kpi-label">Taux de Résolution</span>
            <div class="kpi-icon-wrapper kpi-success">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
            </div>
          </div>
          <div class="kpi-value">{{ analytics.resolutionRate }}%</div>
          <div class="kpi-change positive">
            {{ analytics.resolvedToday }} résolus aujourd'hui
          </div>
        </div>

        <!-- Tickets en attente -->
        <div class="kpi-card">
          <div class="kpi-header">
            <span class="kpi-label">En Attente</span>
            <div class="kpi-icon-wrapper kpi-pending">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
              </svg>
            </div>
          </div>
          <div class="kpi-value">{{ (stats.by_status?.OPEN || 0) + (stats.by_status?.IN_PROGRESS || 0) }}</div>
          <div class="kpi-change" :class="((stats.by_status?.OPEN || 0) + (stats.by_status?.IN_PROGRESS || 0)) > 10 ? 'warning' : 'neutral'">
            {{ ((stats.by_status?.OPEN || 0) + (stats.by_status?.IN_PROGRESS || 0)) > 10 ? 'Attention requise' : 'Sous contrôle' }}
          </div>
        </div>
      </div>

      <!-- Performance de l'équipe -->
      <div class="section-card">
        <div class="section-header">
          <h2 class="section-title">Performance de l'Équipe</h2>
        </div>
        <div v-if="teamPerformance.length === 0" class="empty-state">
          Aucune donnée de performance disponible
        </div>
        <div v-else class="team-performance">
          <div v-for="agent in teamPerformance" :key="agent.id" class="agent-card">
            <div class="agent-info">
              <div class="agent-avatar">{{ getInitials(agent.name) }}</div>
              <div class="agent-details">
                <span class="agent-name">{{ agent.name }}</span>
                <span class="agent-role">Agent Support</span>
              </div>
            </div>
            <div class="agent-stats">
              <div class="agent-stat">
                <span class="stat-value-sm">{{ agent.activeTickets }}</span>
                <span class="stat-label-sm">Actifs</span>
              </div>
              <div class="agent-stat">
                <span class="stat-value-sm">{{ agent.resolvedToday }}</span>
                <span class="stat-label-sm">Résolus</span>
              </div>
              <div class="agent-stat">
                <span class="stat-value-sm">{{ agent.avgTime }}h</span>
                <span class="stat-label-sm">Temps moy.</span>
              </div>
            </div>
            <div class="agent-progress">
              <div class="progress-bar">
                <div class="progress-fill" :style="{ width: agent.efficiency + '%' }"></div>
              </div>
              <span class="progress-label">{{ agent.efficiency }}% efficacité</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Graphiques d'activité -->
      <div class="charts-grid">
        <!-- Évolution des tickets (7 derniers jours) -->
        <div class="section-card">
          <div class="section-header">
            <h2 class="section-title">Activité des 7 derniers jours</h2>
          </div>
          <div class="chart-container">
            <div class="simple-chart">
              <div v-for="(day, index) in weeklyActivity" :key="index" class="chart-bar">
                <div class="bar-wrapper">
                  <div 
                    class="bar-fill" 
                    :style="{ height: getBarHeight(day.count) + '%' }"
                    :title="`${day.count} tickets`"
                  >
                    <span class="bar-label">{{ day.count }}</span>
                  </div>
                </div>
                <span class="bar-day">{{ day.label }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Distribution par priorité -->
        <div class="section-card">
          <div class="section-header">
            <h2 class="section-title">Distribution par Priorité</h2>
          </div>
          <div class="priority-distribution">
            <div v-for="(count, priority) in stats.by_priority" :key="priority" class="priority-item">
              <div class="priority-info">
                <span :class="['priority-dot', priority.toLowerCase()]"></span>
                <span class="priority-name">{{ formatPriority(priority) }}</span>
              </div>
              <div class="priority-stats">
                <span class="priority-count">{{ count }}</span>
                <div class="priority-bar">
                  <div 
                    class="priority-bar-fill" 
                    :class="priority.toLowerCase()"
                    :style="{ width: getPercentage(count, stats.total) + '%' }"
                  ></div>
                </div>
                <span class="priority-percent">{{ getPercentage(count, stats.total) }}%</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tickets critiques -->
      <div class="section-card" v-if="criticalTickets.length > 0">
        <div class="section-header">
          <h2 class="section-title">Tickets Critiques</h2>
          <span class="badge-alert">{{ criticalTickets.length }}</span>
        </div>
        <div class="critical-tickets-list">
          <div v-for="ticket in criticalTickets" :key="ticket.id" class="critical-ticket-item" @click="goToTicket(ticket.id)">
            <div class="ticket-priority-indicator critical"></div>
            <div class="ticket-info">
              <span class="ticket-id">#{{ ticket.id }}</span>
              <span class="ticket-title">{{ ticket.title }}</span>
            </div>
            <div class="ticket-meta">
              <span class="ticket-assignee">{{ ticket.assignee?.name || 'Non assigné' }}</span>
              <span class="ticket-time">{{ formatTimeAgo(ticket.createdAt) }}</span>
            </div>
            <span :class="['ticket-status-badge', ticket.status.toLowerCase()]">
              {{ formatStatus(ticket.status) }}
            </span>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import ticketService from '../services/ticketService'
import axios from 'axios'

const router = useRouter()

// États
const loading = ref(true)
const error = ref(null)

// Stats de base
const stats = ref({
  total: 0,
  by_status: {},
  by_priority: {}
})

// Tickets critiques
const criticalTickets = ref([])

// Analytics calculés
const analytics = ref({
  avgResolutionTime: 0,
  resolutionTrend: 0,
  ticketsToday: 0,
  avgTicketsPerDay: 0,
  resolutionRate: 0,
  resolvedToday: 0
})

// Performance équipe
const teamPerformance = ref([])

// Activité hebdomadaire
const weeklyActivity = ref([])

onMounted(async () => {
  await loadDashboardData()
})

async function loadDashboardData() {
  try {
    loading.value = true
    error.value = null

    const userId = 5 // MANAGER ID - Thomas Petit

    // Charger les stats générales
    const statsResponse = await ticketService.getStats(userId)
    if (statsResponse.success) {
      stats.value = statsResponse.data
    } else {
      stats.value = statsResponse
    }

    // Charger tous les tickets pour calculs
    const ticketsResponse = await ticketService.getTickets(userId)
    const allTickets = ticketsResponse.data || ticketsResponse

    // Tickets critiques
    criticalTickets.value = allTickets
      .filter(t => t.priority === 'HIGH' && ['OPEN', 'IN_PROGRESS'].includes(t.status))
      .slice(0, 5)

    // Calculer les analytics
    calculateAnalytics(allTickets)
    calculateTeamPerformance(allTickets)
    calculateWeeklyActivity(allTickets)

    loading.value = false

  } catch (err) {
    console.error('Erreur chargement dashboard:', err)
    error.value = 'Impossible de charger les données du dashboard'
    loading.value = false
  }
}

function calculateAnalytics(tickets) {
  const now = new Date()
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
  const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000)
  const twoWeeksAgo = new Date(today.getTime() - 14 * 24 * 60 * 60 * 1000)

  analytics.value.ticketsToday = tickets.filter(t => new Date(t.createdAt) >= today).length

  const last7Days = tickets.filter(t => new Date(t.createdAt) >= weekAgo)
  analytics.value.avgTicketsPerDay = Math.round(last7Days.length / 7)

  const resolvedTickets = tickets.filter(t => t.status === 'RESOLVED' || t.status === 'CLOSED')
  
  analytics.value.resolutionRate = tickets.length > 0 
    ? Math.round((resolvedTickets.length / tickets.length) * 100)
    : 0

  analytics.value.resolvedToday = resolvedTickets.filter(t => new Date(t.updatedAt) >= today).length

  const resolvedWithTime = resolvedTickets.filter(t => t.updatedAt && t.createdAt)
  if (resolvedWithTime.length > 0) {
    const totalMinutes = resolvedWithTime.reduce((sum, ticket) => {
      const created = new Date(ticket.createdAt).getTime()
      const resolved = new Date(ticket.updatedAt).getTime()
      return sum + (resolved - created) / (1000 * 60)
    }, 0)
    analytics.value.avgResolutionTime = Math.round(totalMinutes / resolvedWithTime.length)
  }

  const thisWeekResolved = resolvedTickets.filter(t => new Date(t.updatedAt) >= weekAgo)
  const lastWeekResolved = resolvedTickets.filter(t => {
    const date = new Date(t.updatedAt)
    return date >= twoWeeksAgo && date < weekAgo
  })
  
  if (lastWeekResolved.length > 0) {
    analytics.value.resolutionTrend = Math.round(
      ((thisWeekResolved.length - lastWeekResolved.length) / lastWeekResolved.length) * 100
    )
  }
}

function calculateTeamPerformance(tickets) {
  const agentMap = new Map()

  tickets.forEach(ticket => {
    if (ticket.assignee) {
      const agentId = ticket.assignee.id
      if (!agentMap.has(agentId)) {
        agentMap.set(agentId, {
          id: agentId,
          name: ticket.assignee.name,
          activeTickets: 0,
          resolvedToday: 0,
          resolvedTotal: 0,
          totalTime: 0,
          resolvedWithTime: 0
        })
      }

      const agent = agentMap.get(agentId)

      if (['OPEN', 'IN_PROGRESS'].includes(ticket.status)) {
        agent.activeTickets++
      }

      if (['RESOLVED', 'CLOSED'].includes(ticket.status)) {
        agent.resolvedTotal++

        const updatedDate = new Date(ticket.updatedAt)
        const today = new Date()
        if (updatedDate.toDateString() === today.toDateString()) {
          agent.resolvedToday++
        }

        if (ticket.updatedAt && ticket.createdAt) {
          const created = new Date(ticket.createdAt).getTime()
          const resolved = new Date(ticket.updatedAt).getTime()
          const minutes = (resolved - created) / (1000 * 60)
          agent.totalTime += minutes
          agent.resolvedWithTime++
        }
      }
    }
  })

  teamPerformance.value = Array.from(agentMap.values())
    .map(agent => ({
      ...agent,
      avgTime: agent.resolvedWithTime > 0 
        ? Math.round((agent.totalTime / agent.resolvedWithTime) / 60)
        : 0,
      efficiency: agent.resolvedTotal > 0
        ? Math.min(100, Math.round((agent.resolvedTotal / (agent.activeTickets + agent.resolvedTotal)) * 100))
        : 0
    }))
    .sort((a, b) => b.resolvedTotal - a.resolvedTotal)
}

function calculateWeeklyActivity(tickets) {
  const days = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam']
  const activity = []

  for (let i = 6; i >= 0; i--) {
    const date = new Date()
    date.setDate(date.getDate() - i)
    date.setHours(0, 0, 0, 0)

    const dayEnd = new Date(date)
    dayEnd.setHours(23, 59, 59, 999)

    const count = tickets.filter(t => {
      const created = new Date(t.createdAt)
      return created >= date && created <= dayEnd
    }).length

    activity.push({
      label: i === 0 ? "Auj." : days[date.getDay()],
      count,
      date
    })
  }

  weeklyActivity.value = activity
}

function formatDuration(minutes) {
  if (minutes < 60) return `${minutes}min`
  if (minutes < 1440) return `${Math.round(minutes / 60)}h`
  return `${Math.round(minutes / 1440)}j`
}

function formatStatus(status) {
  const statusMap = {
    'OPEN': 'Ouvert',
    'IN_PROGRESS': 'En cours',
    'RESOLVED': 'Résolu',
    'CLOSED': 'Fermé'
  }
  return statusMap[status] || status
}

function formatPriority(priority) {
  const priorityMap = {
    'LOW': 'Basse',
    'MEDIUM': 'Moyenne',
    'HIGH': 'Haute',
    'CRITICAL': 'Critique'
  }
  return priorityMap[priority] || priority
}

function formatTimeAgo(dateString) {
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)

  if (diffMins < 1) return "À l'instant"
  if (diffMins < 60) return `${diffMins}min`
  if (diffHours < 24) return `${diffHours}h`
  return `${diffDays}j`
}

function getPercentage(value, total) {
  if (total === 0) return 0
  return Math.round((value / total) * 100)
}

function getBarHeight(count) {
  const max = Math.max(...weeklyActivity.value.map(d => d.count))
  if (max === 0) return 0
  return (count / max) * 100
}

function getInitials(name) {
  return name
    .split(' ')
    .map(word => word[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

function goToTicket(ticketId) {
  router.push(`/tickets/${ticketId}`)
}
</script>

<style scoped>
.manager-dashboard {
  padding: 1.5rem;
  max-width: 1600px;
  margin: 0 auto;
  background: #f9fafb;
  min-height: calc(100vh - 80px);
}

.dashboard-header {
  margin-bottom: 2rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.dashboard-title {
  font-size: 1.875rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 0.25rem;
  letter-spacing: -0.025em;
}

.dashboard-subtitle {
  color: #6b7280;
  font-size: 0.875rem;
  margin: 0;
}

.btn-refresh {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1rem;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  color: #374151;
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-refresh:hover:not(:disabled) {
  background: #f9fafb;
  border-color: #2563eb;
  color: #2563eb;
}

.btn-refresh:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.loading-state {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding: 4rem;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #e5e7eb;
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-message {
  background: #fee2e2;
  border: 1px solid #fecaca;
  border-radius: 8px;
  padding: 1.5rem;
  text-align: center;
  color: #991b1b;
}

.btn-retry {
  margin-top: 1rem;
  padding: 0.5rem 1.5rem;
  background: #dc2626;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

.dashboard-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.25rem;
}

.kpi-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.5rem;
  transition: all 0.2s;
}

.kpi-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transform: translateY(-2px);
}

.kpi-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.kpi-label {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

.kpi-icon-wrapper {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.kpi-time {
  background: #dbeafe;
  color: #1e40af;
}

.kpi-tickets {
  background: #fef3c7;
  color: #92400e;
}

.kpi-success {
  background: #d1fae5;
  color: #065f46;
}

.kpi-pending {
  background: #e0e7ff;
  color: #3730a3;
}

.kpi-value {
  font-size: 2.25rem;
  font-weight: 700;
  color: #111827;
  line-height: 1;
  margin-bottom: 0.5rem;
}

.kpi-change {
  font-size: 0.8125rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.kpi-change.positive {
  color: #059669;
}

.kpi-change.negative {
  color: #dc2626;
}

.kpi-change.neutral {
  color: #6b7280;
}

.kpi-change.warning {
  color: #d97706;
}

.kpi-change-label {
  font-weight: 400;
  color: #9ca3af;
}

.section-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.5rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.section-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
}

.badge-alert {
  background: #fee2e2;
  color: #991b1b;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 600;
}

.empty-state {
  text-align: center;
  padding: 2rem;
  color: #9ca3af;
}

.team-performance {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1rem;
}

.agent-card {
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 1.25rem;
  transition: all 0.2s;
}

.agent-card:hover {
  border-color: #2563eb;
  background: #f9fafb;
}

.agent-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.agent-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.875rem;
}

.agent-details {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.agent-name {
  font-weight: 600;
  color: #111827;
  font-size: 0.9375rem;
}

.agent-role {
  font-size: 0.8125rem;
  color: #6b7280;
}

.agent-stats {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 1rem;
}

.agent-stat {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.stat-value-sm {
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
}

.stat-label-sm {
  font-size: 0.75rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.agent-progress {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background: #e5e7eb;
  border-radius: 9999px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #2563eb 0%, #1d4ed8 100%);
  border-radius: 9999px;
  transition: width 0.4s ease;
}

.progress-label {
  font-size: 0.8125rem;
  color: #6b7280;
  font-weight: 500;
}

.charts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
  gap: 1.5rem;
}

.chart-container {
  padding: 1rem 0;
}

.simple-chart {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 1rem;
  height: 200px;
  padding: 1rem 0;
}

.chart-bar {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.bar-wrapper {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: flex-end;
  justify-content: center;
}

.bar-fill {
  width: 100%;
  max-width: 48px;
  background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%);
  border-radius: 6px 6px 0 0;
  position: relative;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding-top: 0.5rem;
  transition: all 0.3s ease;
  cursor: pointer;
}

.bar-fill:hover {
  background: linear-gradient(180deg, #60a5fa 0%, #3b82f6 100%);
}

.bar-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
}

.bar-day {
  font-size: 0.8125rem;
  color: #6b7280;
  font-weight: 500;
}

.priority-distribution {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.priority-item {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.priority-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.priority-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
}

.priority-dot.low {
  background: #10b981;
}

.priority-dot.medium {
  background: #f59e0b;
}

.priority-dot.high {
  background: #ef4444;
}

.priority-dot.critical {
  background: #dc2626;
}

.priority-name {
  font-weight: 500;
  color: #374151;
  font-size: 0.9375rem;
}

.priority-stats {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.priority-count {
  min-width: 32px;
  font-weight: 700;
  color: #111827;
  font-size: 1.125rem;
}

.priority-bar {
  flex: 1;
  height: 8px;
  background: #f3f4f6;
  border-radius: 9999px;
  overflow: hidden;
}

.priority-bar-fill {
  height: 100%;
  transition: width 0.4s ease;
  border-radius: 9999px;
}

.priority-bar-fill.low {
  background: #10b981;
}

.priority-bar-fill.medium {
  background: #f59e0b;
}

.priority-bar-fill.high {
  background: #ef4444;
}

.priority-bar-fill.critical {
  background: #dc2626;
}

.priority-percent {
  min-width: 42px;
  text-align: right;
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

.critical-tickets-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.critical-ticket-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.critical-ticket-item:hover {
  border-color: #dc2626;
  background: #fef2f2;
}

.ticket-priority-indicator {
  width: 4px;
  height: 40px;
  border-radius: 2px;
}

.ticket-priority-indicator.critical {
  background: #dc2626;
}

.ticket-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.ticket-id {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #6b7280;
}

.ticket-title {
  font-weight: 500;
  color: #111827;
  font-size: 0.9375rem;
}

.ticket-meta {
  display: flex;
  align-items: center;
  gap: 1rem;
  font-size: 0.8125rem;
  color: #6b7280;
}

.ticket-status-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 6px;
  font-size: 0.8125rem;
  font-weight: 600;
  white-space: nowrap;
}

.ticket-status-badge.open {
  background: #dbeafe;
  color: #1e40af;
}

.ticket-status-badge.in_progress {
  background: #fef3c7;
  color: #92400e;
}

@media (max-width: 768px) {
  .manager-dashboard {
    padding: 1rem;
  }

  .kpi-grid,
  .charts-grid {
    grid-template-columns: 1fr;
  }

  .team-performance {
    grid-template-columns: 1fr;
  }

  .dashboard-title {
    font-size: 1.5rem;
  }
}
</style>
