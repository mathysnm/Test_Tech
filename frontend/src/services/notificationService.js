/**
 * Service API pour la gestion des notifications
 */
import apiClient from './api'

/**
 * Récupère les notifications d'un utilisateur
 * @param {number} userId - ID de l'utilisateur
 * @param {Object} options - Options de filtrage (unreadOnly, limit)
 * @returns {Promise<Object>} Liste des notifications avec compteur non lues
 */
export async function getNotifications(userId, options = {}) {
  const params = {
    user_id: userId,
    unread_only: options.unreadOnly ? 'true' : 'false',
    limit: options.limit || 50
  }
  
  const response = await apiClient.get('/notifications', { params })
  return response.data
}

/**
 * Marque une notification comme lue
 * @param {number} notificationId - ID de la notification
 * @param {number} userId - ID de l'utilisateur
 * @returns {Promise<Object>} Confirmation
 */
export async function markNotificationAsRead(notificationId, userId) {
  const response = await apiClient.post(`/notifications/${notificationId}/read`, {
    user_id: userId
  })
  return response.data
}

/**
 * Marque toutes les notifications comme lues
 * @param {number} userId - ID de l'utilisateur
 * @returns {Promise<Object>} Confirmation avec nombre de notifications marquées
 */
export async function markAllNotificationsAsRead(userId) {
  const response = await apiClient.post('/notifications/read-all', {
    user_id: userId
  })
  return response.data
}

// Export par défaut
export default {
  getNotifications,
  markAsRead: markNotificationAsRead,
  markAllAsRead: markAllNotificationsAsRead
}
