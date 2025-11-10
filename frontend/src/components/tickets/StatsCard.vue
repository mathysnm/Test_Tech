<template>
  <div class="stats-dashboard">
    <h2 class="dashboard-title">Tableau de Bord</h2>
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Chargement des statistiques...</p>
    </div>
    <div v-else class="stats-grid">
      <!-- Total -->
      <div class="stat-card stat-total">
        <div class="stat-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 11l3 3L22 4"/>
            <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
          </svg>
        </div>
        <div class="stat-content">
          <p class="stat-label">Total des Demandes</p>
          <p class="stat-value">{{ stats.total || 0 }}</p>
        </div>
      </div>

      <!-- Par Statut -->
      <div class="stat-card stat-new">
        <div class="stat-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 6v6l4 2"/>
          </svg>
        </div>
        <div class="stat-content">
          <p class="stat-label">Nouvelles</p>
          <p class="stat-value">{{ stats.byStatus?.NEW || 0 }}</p>
        </div>
      </div>

      <div class="stat-card stat-in-progress">
        <div class="stat-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
          </svg>
        </div>
        <div class="stat-content">
          <p class="stat-label">En Cours</p>
          <p class="stat-value">{{ stats.byStatus?.IN_PROGRESS || 0 }}</p>
        </div>
      </div>

      <div class="stat-card stat-resolved">
        <div class="stat-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
        </div>
        <div class="stat-content">
          <p class="stat-label">Résolues</p>
          <p class="stat-value">{{ stats.byStatus?.RESOLVED || 0 }}</p>
        </div>
      </div>

      <div class="stat-card stat-closed">
        <div class="stat-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 4H8l-7 8 7 8h13a2 2 0 002-2V6a2 2 0 00-2-2z"/>
            <line x1="18" y1="9" x2="12" y2="15"/>
            <line x1="12" y1="9" x2="18" y2="15"/>
          </svg>
        </div>
        <div class="stat-content">
          <p class="stat-label">Fermées</p>
          <p class="stat-value">{{ stats.byStatus?.CLOSED || 0 }}</p>
        </div>
      </div>

      <!-- Par Priorité -->
      <div class="stat-card stat-high">
        <div class="stat-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
          </svg>
        </div>
        <div class="stat-content">
          <p class="stat-label">Priorité Haute</p>
          <p class="stat-value">{{ stats.byPriority?.HIGH || 0 }}</p>
        </div>
      </div>

      <div class="stat-card stat-medium">
        <div class="stat-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="16" x2="12" y2="12"/>
            <line x1="12" y1="8" x2="12.01" y2="8"/>
          </svg>
        </div>
        <div class="stat-content">
          <p class="stat-label">Priorité Moyenne</p>
          <p class="stat-value">{{ stats.byPriority?.MEDIUM || 0 }}</p>
        </div>
      </div>

      <div class="stat-card stat-low">
        <div class="stat-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6 9 12 15 18 9"/>
          </svg>
        </div>
        <div class="stat-content">
          <p class="stat-label">Priorité Basse</p>
          <p class="stat-value">{{ stats.byPriority?.LOW || 0 }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useTicketStore } from '../../stores/tickets'

const ticketStore = useTicketStore()
const stats = computed(() => ticketStore.stats)
const loading = computed(() => ticketStore.loading)
</script>

<style scoped>
.stats-dashboard {
  margin-bottom: 2rem;
}

.dashboard-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--color-primary);
  margin-bottom: 1.5rem;
  letter-spacing: -0.025em;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.25rem;
}

.stat-card {
  background: var(--color-bg-surface);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  transition: all 0.2s ease;
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: currentColor;
  opacity: 0.8;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
  border-color: currentColor;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  background: currentColor;
  color: white;
  opacity: 0.95;
}

.stat-content {
  flex: 1;
}

.stat-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-text-secondary);
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.025em;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: var(--color-text-primary);
  line-height: 1;
  letter-spacing: -0.025em;
}

/* Couleurs par type */
.stat-total {
  color: var(--color-primary);
}

.stat-new {
  color: #3182ce;
}

.stat-in-progress {
  color: #d97706;
}

.stat-resolved {
  color: #38a169;
}

.stat-closed {
  color: #718096;
}

.stat-high {
  color: #dc2626;
}

.stat-medium {
  color: #d97706;
}

.stat-low {
  color: #10b981;
}

.loading-state {
  padding: 4rem 2rem;
  text-align: center;
  color: var(--color-text-muted);
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
</style>
