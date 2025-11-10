<template>
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
</template>

<script setup>
import Badge from '@/components/ui/Badge.vue'
import { useTicketFormatters } from '@/composables/useTicketFormatters'

defineProps({
  ticket: {
    type: Object,
    required: true
  }
})

const { getStatusLabel, getStatusIcon, getPriorityLabel } = useTicketFormatters()
</script>

<style scoped>
.ticket-header-professional {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem;
  border-radius: 12px 12px 0 0;
  color: white;
  box-shadow: 0 4px 20px rgba(102, 126, 234, 0.25);
}

.header-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 2rem;
}

.title-section {
  flex: 1;
}

.ticket-title {
  font-size: 1.75rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
  color: white;
  line-height: 1.3;
}

.ticket-id {
  font-size: 0.875rem;
  opacity: 0.9;
  font-weight: 500;
  letter-spacing: 0.5px;
}

.badges-section {
  display: flex;
  gap: 0.75rem;
  flex-shrink: 0;
}
</style>
