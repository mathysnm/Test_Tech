<template>
  <aside class="sidebar">
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

    <!-- Actions de statut - Uniquement pour AGENT et MANAGER -->
    <div class="info-block" v-if="canUpdateStatus">
      <div class="info-header">
        <i class="fas fa-sliders-h"></i>
        <h3>Changer le statut</h3>
      </div>
      <div class="status-buttons">
        <button 
          v-for="status in availableStatuses" 
          :key="status.value"
          @click="$emit('change-status', status.value)"
          :class="['status-btn', { active: ticket.status === status.value }]"
          :disabled="ticket.status === status.value || updatingStatus"
        >
          <i :class="status.icon"></i>
          {{ status.label }}
        </button>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { getUserColor, getInitials } from '@/utils/userUtils'
import { formatDateTime } from '@/utils/formatting'
import { useTicketFormatters } from '@/composables/useTicketFormatters'
import { toRefs } from 'vue'

const props = defineProps({
  ticket: {
    type: Object,
    required: true
  },
  availableStatuses: {
    type: Array,
    required: true
  },
  updatingStatus: {
    type: Boolean,
    default: false
  },
  canUpdateStatus: {
    type: Boolean,
    default: false
  }
})

const { canUpdateStatus } = toRefs(props)

defineEmits(['change-status'])

const { getRoleLabel } = useTicketFormatters()
</script>

<style scoped>
.sidebar {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.info-block {
  margin-bottom: 2rem;
}

.info-block:last-child {
  margin-bottom: 0;
}

.info-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #f0f0f0;
}

.info-header i {
  color: #667eea;
  font-size: 1.125rem;
}

.info-header h3 {
  font-size: 1rem;
  font-weight: 600;
  color: #2d3748;
  margin: 0;
}

.user-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.user-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 1rem;
  flex-shrink: 0;
}

.user-details {
  flex: 1;
}

.user-name {
  font-weight: 600;
  color: #2d3748;
  margin: 0 0 0.25rem 0;
}

.user-role {
  display: inline-block;
  font-size: 0.75rem;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.role-client {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.role-agent {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
}

.role-manager {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  color: white;
}

.no-assignee {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
  color: #718096;
}

.no-assignee i {
  font-size: 1.5rem;
}

.timeline {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.timeline-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.timeline-item i {
  color: #667eea;
  margin-top: 0.25rem;
}

.timeline-item > div {
  flex: 1;
}

.timeline-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #4a5568;
  margin-bottom: 0.25rem;
}

.timeline-date {
  display: block;
  font-size: 0.8125rem;
  color: #718096;
}

.status-buttons {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.status-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-weight: 600;
  color: #4a5568;
  cursor: pointer;
  transition: all 0.2s;
}

.status-btn:hover:not(:disabled) {
  border-color: #667eea;
  background: #f7fafc;
  transform: translateX(4px);
}

.status-btn.active {
  background: #667eea;
  border-color: #667eea;
  color: white;
}

.status-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
