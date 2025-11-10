<template>
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
</template>

<script setup>
import { formatDateTime } from '@/utils/formatting'
import { useTicketFormatters } from '@/composables/useTicketFormatters'

defineProps({
  ticket: {
    type: Object,
    required: true
  }
})

const { getStatusLabel } = useTicketFormatters()
</script>

<style scoped>
.content-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.section-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #f0f0f0;
}

.section-header i {
  color: #667eea;
  font-size: 1.25rem;
}

.section-header h2 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #2d3748;
  margin: 0;
}

.history-content {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.history-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
  transition: all 0.2s;
}

.history-item:hover {
  background: #f1f3f5;
  transform: translateX(4px);
}

.history-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 1rem;
}

.history-icon.created {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.history-icon.assigned {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
}

.history-icon.status-changed {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  color: white;
}

.history-details {
  flex: 1;
}

.history-action {
  font-size: 0.9375rem;
  color: #2d3748;
  margin: 0 0 0.5rem 0;
  line-height: 1.5;
}

.history-action strong {
  color: #667eea;
  font-weight: 600;
}

.history-date {
  font-size: 0.8125rem;
  color: #718096;
}
</style>
