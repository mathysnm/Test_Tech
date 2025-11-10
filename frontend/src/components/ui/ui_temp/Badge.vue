<template>
  <span :class="['badge-ui', `badge-${type}`, `badge-${variant}`]" :style="customStyle">
    <i v-if="icon" :class="icon" class="badge-icon"></i>
    <span class="badge-label">{{ label }}</span>
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  /**
   * Type de badge: 'status', 'priority', 'role'
   */
  type: {
    type: String,
    required: true,
    validator: (value) => ['status', 'priority', 'role'].includes(value)
  },
  
  /**
   * Variante du badge (ex: 'open', 'high', 'client')
   */
  variant: {
    type: String,
    required: true
  },
  
  /**
   * Libellé affiché
   */
  label: {
    type: String,
    required: true
  },
  
  /**
   * Icône FontAwesome (optionnel)
   */
  icon: {
    type: String,
    default: ''
  },
  
  /**
   * Taille: 'sm', 'md', 'lg'
   */
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  }
})

const customStyle = computed(() => {
  // Styles inline si besoin de customisation dynamique
  return {}
})
</script>

<style scoped>
.badge-ui {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.4rem 0.8rem;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: all 0.2s;
  border: 1.5px solid transparent;
}

.badge-icon {
  font-size: 0.9em;
}

/* Tailles */
.badge-ui.badge-sm {
  padding: 0.25rem 0.6rem;
  font-size: 0.75rem;
  gap: 0.3rem;
}

.badge-ui.badge-lg {
  padding: 0.6rem 1.2rem;
  font-size: 0.95rem;
  gap: 0.5rem;
}

/* === STATUS BADGES === */
.badge-status.badge-open {
  background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
  color: #5b21b6;
  border-color: #a78bfa;
}

.badge-status.badge-in_progress {
  background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
  color: #1e40af;
  border-color: #60a5fa;
}

.badge-status.badge-resolved {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  color: #065f46;
  border-color: #34d399;
}

.badge-status.badge-closed {
  background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
  color: #374151;
  border-color: #9ca3af;
}

/* === PRIORITY BADGES === */
.badge-priority.badge-high {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  color: #991b1b;
  border-color: #f87171;
}

.badge-priority.badge-medium {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  color: #92400e;
  border-color: #fbbf24;
}

.badge-priority.badge-low {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  color: #065f46;
  border-color: #34d399;
}

/* === ROLE BADGES === */
.badge-role.badge-client {
  background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
  color: #1e40af;
  border-color: #60a5fa;
}

.badge-role.badge-agent {
  background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
  color: #5b21b6;
  border-color: #a78bfa;
}

.badge-role.badge-manager {
  background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);
  color: #9f1239;
  border-color: #f472b6;
}

/* Hover effect */
.badge-ui:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}
</style>
