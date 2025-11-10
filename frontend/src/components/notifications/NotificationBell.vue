<template>
  <div class="notification-bell">
    <button 
      class="bell-button" 
      @click="toggleDropdown"
      :class="{ 'has-unread': unreadCount > 0 }"
      aria-label="Notifications"
    >
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
      </svg>
      <span v-if="unreadCount > 0" class="badge">{{ displayCount }}</span>
    </button>

    <transition name="dropdown">
      <div v-if="isOpen" class="dropdown" @click.stop>
        <div class="dropdown-header">
          <h3>Notifications</h3>
          <button 
            v-if="unreadCount > 0" 
            @click="markAllRead" 
            class="mark-all-btn"
            :disabled="loading"
          >
            Tout marquer comme lu
          </button>
        </div>

        <div v-if="loading && notifications.length === 0" class="loading-state">
          <div class="spinner"></div>
          <p>Chargement...</p>
        </div>

        <div v-else-if="notifications.length === 0" class="empty-state">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
          </svg>
          <p>Aucune notification</p>
        </div>

        <div v-else class="notifications-list">
          <div 
            v-for="notification in notifications" 
            :key="notification.id"
            class="notification-item"
            :class="{ 'unread': !notification.isRead }"
            @click="markRead(notification)"
          >
            <div class="notification-icon" :class="getIconClass(notification.type)">
              <component :is="getIcon(notification.type)" />
            </div>
            <div class="notification-content">
              <p class="notification-message">{{ notification.message }}</p>
              <span class="notification-time">{{ formatTime(notification.createdAt) }}</span>
            </div>
            <span v-if="!notification.isRead" class="unread-dot"></span>
          </div>
        </div>

        <div v-if="notifications.length > 0" class="dropdown-footer">
          <button @click="closeDropdown" class="view-all-btn">
            Fermer
          </button>
        </div>
      </div>
    </transition>

    <!-- Overlay pour fermer le dropdown -->
    <div v-if="isOpen" class="overlay" @click="closeDropdown"></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, h } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { 
  getNotifications, 
  markNotificationAsRead, 
  markAllNotificationsAsRead 
} from '@/services/notificationService'

const authStore = useAuthStore()

const isOpen = ref(false)
const notifications = ref([])
const unreadCount = ref(0)
const loading = ref(false)
let pollInterval = null

// Circuit breaker: désactive le polling après trop d'erreurs consécutives
let consecutiveErrors = 0
const MAX_ERRORS = 5
let pollingEnabled = true

const displayCount = computed(() => {
  return unreadCount.value > 99 ? '99+' : unreadCount.value
})

// Icônes pour les différents types de notifications
function getIcon(type) {
  const icons = {
    TICKET_CREATED: () => h('svg', { width: 16, height: 16, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 2 }, [
      h('path', { d: 'M12 5v14' }),
      h('path', { d: 'M5 12h14' })
    ]),
    TICKET_ASSIGNED: () => h('svg', { width: 16, height: 16, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 2 }, [
      h('path', { d: 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2' }),
      h('circle', { cx: 9, cy: 7, r: 4 })
    ]),
    TICKET_STATUS_CHANGED: () => h('svg', { width: 16, height: 16, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 2 }, [
      h('path', { d: 'M21.5 2v6h-6' }),
      h('path', { d: 'M2.5 22v-6h6' }),
      h('path', { d: 'M2 11.5a10 10 0 0 1 18.8-4.3' }),
      h('path', { d: 'M22 12.5a10 10 0 0 1-18.8 4.3' })
    ]),
    TICKET_RESOLVED: () => h('svg', { width: 16, height: 16, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 2 }, [
      h('path', { d: 'M22 11.08V12a10 10 0 1 1-5.93-9.14' }),
      h('polyline', { points: '22 4 12 14.01 9 11.01' })
    ]),
    default: () => h('svg', { width: 16, height: 16, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 2 }, [
      h('circle', { cx: 12, cy: 12, r: 10 }),
      h('line', { x1: 12, y1: 16, x2: 12, y2: 12 }),
      h('line', { x1: 12, y1: 8, x2: 12.01, y2: 8 })
    ])
  }
  
  return icons[type] || icons.default
}

function getIconClass(type) {
  const classes = {
    TICKET_CREATED: 'icon-blue',
    TICKET_ASSIGNED: 'icon-green',
    TICKET_STATUS_CHANGED: 'icon-orange',
    TICKET_RESOLVED: 'icon-green',
    default: 'icon-gray'
  }
  
  return classes[type] || classes.default
}

function formatTime(dateString) {
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)
  
  if (diffMins < 1) return 'À l\'instant'
  if (diffMins < 60) return `Il y a ${diffMins} min`
  if (diffHours < 24) return `Il y a ${diffHours}h`
  if (diffDays < 7) return `Il y a ${diffDays}j`
  
  return date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' })
}

async function fetchNotifications() {
  if (!authStore.currentUser) return
  
  // Circuit breaker: arrêter le polling si trop d'erreurs
  if (!pollingEnabled) {
    console.warn('[NotificationBell] Polling désactivé après erreurs répétées')
    return
  }
  
  try {
    const response = await getNotifications(authStore.currentUser.id, {
      unreadOnly: false,
      limit: 20
    })
    
    notifications.value = response.data
    unreadCount.value = response.unreadCount
    
    // Reset du compteur d'erreurs si succès
    consecutiveErrors = 0
  } catch (error) {
    console.error('Error fetching notifications:', error)
    consecutiveErrors++
    
    // Désactiver le polling après MAX_ERRORS erreurs consécutives
    if (consecutiveErrors >= MAX_ERRORS) {
      console.error(`[NotificationBell] ${MAX_ERRORS} erreurs consécutives détectées. Polling désactivé.`)
      pollingEnabled = false
      stopPolling()
    }
  }
}

async function markRead(notification) {
  if (notification.isRead) return
  
  try {
    await markNotificationAsRead(notification.id, authStore.currentUser.id)
    notification.isRead = true
    unreadCount.value = Math.max(0, unreadCount.value - 1)
  } catch (error) {
    console.error('Error marking notification as read:', error)
  }
}

async function markAllRead() {
  loading.value = true
  try {
    await markAllNotificationsAsRead(authStore.currentUser.id)
    notifications.value.forEach(n => n.isRead = true)
    unreadCount.value = 0
  } catch (error) {
    console.error('Error marking all as read:', error)
  } finally {
    loading.value = false
  }
}

function toggleDropdown() {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    fetchNotifications()
  }
}

function closeDropdown() {
  isOpen.value = false
}

// Polling des notifications toutes les 30 secondes
function startPolling() {
  fetchNotifications() // Premier fetch immédiat
  pollInterval = setInterval(fetchNotifications, 30000) // 30 secondes
}

function stopPolling() {
  if (pollInterval) {
    clearInterval(pollInterval)
    pollInterval = null
  }
}

onMounted(() => {
  if (authStore.isAuthenticated) {
    startPolling()
  }
})

onUnmounted(() => {
  stopPolling()
})
</script>

<style scoped>
.notification-bell {
  position: relative;
}

.bell-button {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: transparent;
  border: none;
  border-radius: 8px;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
}

.bell-button:hover {
  background: #f1f5f9;
  color: #0f172a;
}

.bell-button.has-unread {
  color: #3b82f6;
}

.badge {
  position: absolute;
  top: 4px;
  right: 4px;
  min-width: 18px;
  height: 18px;
  padding: 0 5px;
  background: #ef4444;
  color: white;
  font-size: 11px;
  font-weight: 600;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
}

.dropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 380px;
  max-height: 500px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  z-index: 1000;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.dropdown-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid #e2e8f0;
}

.dropdown-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #0f172a;
}

.mark-all-btn {
  padding: 6px 12px;
  background: transparent;
  border: none;
  color: #3b82f6;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  border-radius: 6px;
  transition: background 0.2s;
}

.mark-all-btn:hover:not(:disabled) {
  background: #eff6ff;
}

.mark-all-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  color: #94a3b8;
}

.spinner {
  width: 24px;
  height: 24px;
  border: 3px solid #e2e8f0;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 12px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-state svg {
  margin-bottom: 12px;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
}

.notifications-list {
  flex: 1;
  overflow-y: auto;
  max-height: 400px;
}

.notification-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 14px 20px;
  cursor: pointer;
  transition: background 0.2s;
  position: relative;
}

.notification-item:hover {
  background: #f8fafc;
}

.notification-item.unread {
  background: #eff6ff;
}

.notification-item.unread:hover {
  background: #dbeafe;
}

.notification-item + .notification-item {
  border-top: 1px solid #f1f5f9;
}

.notification-icon {
  flex-shrink: 0;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-blue {
  background: #eff6ff;
  color: #3b82f6;
}

.icon-green {
  background: #f0fdf4;
  color: #22c55e;
}

.icon-orange {
  background: #fff7ed;
  color: #f97316;
}

.icon-gray {
  background: #f8fafc;
  color: #64748b;
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-message {
  margin: 0 0 4px 0;
  font-size: 14px;
  color: #0f172a;
  line-height: 1.4;
}

.notification-item.unread .notification-message {
  font-weight: 500;
}

.notification-time {
  font-size: 12px;
  color: #94a3b8;
}

.unread-dot {
  flex-shrink: 0;
  width: 8px;
  height: 8px;
  background: #3b82f6;
  border-radius: 50%;
  margin-top: 6px;
}

.dropdown-footer {
  padding: 12px 20px;
  border-top: 1px solid #e2e8f0;
}

.view-all-btn {
  width: 100%;
  padding: 8px;
  background: transparent;
  border: none;
  color: #3b82f6;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  border-radius: 6px;
  transition: background 0.2s;
}

.view-all-btn:hover {
  background: #eff6ff;
}

.overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 999;
}

/* Animations */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Scrollbar */
.notifications-list::-webkit-scrollbar {
  width: 6px;
}

.notifications-list::-webkit-scrollbar-track {
  background: transparent;
}

.notifications-list::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.notifications-list::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
