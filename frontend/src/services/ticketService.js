/**
 * Service Tickets
 * Gestion des appels API pour les tickets
 */

import apiClient from './api'

/**
 * Récupère la liste des tickets filtrés par utilisateur
 * @param {number} userId - ID de l'utilisateur connecté
 * @returns {Promise} Liste des tickets
 */
export async function fetchTickets(userId) {
  const response = await apiClient.get('/tickets', {
    params: { user_id: userId }
  })
  return response.data
}

/**
 * Récupère les statistiques des tickets
 * @param {number} userId - ID de l'utilisateur connecté
 * @returns {Promise} Statistiques
 */
export async function fetchStats(userId) {
  const response = await apiClient.get('/tickets/stats', {
    params: { user_id: userId }
  })
  return response.data
}

/**
 * Récupère les détails d'un ticket
 * @param {number} ticketId - ID du ticket
 * @param {number} userId - ID de l'utilisateur connecté
 * @returns {Promise} Détails du ticket
 */
export async function fetchTicket(ticketId, userId) {
  const response = await apiClient.get(`/tickets/${ticketId}`, {
    params: { user_id: userId }
  })
  return response.data
}

/**
 * Créer un nouveau ticket
 * @param {Object} ticketData - Les données du ticket (title, description, priority, creator_id)
 * @returns {Promise<Object>} Le ticket créé
 */
export async function createTicket(ticketData) {
  // Extraire creator_id pour le renommer en user_id (attendu par le backend)
  const { creator_id, ...restData } = ticketData
  
  const payload = {
    ...restData,
    user_id: creator_id
  }
  
  const response = await apiClient.post('/tickets', payload)
  return response.data
}

/**
 * Met à jour un ticket
 * @param {number} ticketId - ID du ticket
 * @param {Object} ticketData - Données à mettre à jour
 * @param {number} userId - ID de l'utilisateur connecté
 * @returns {Promise} Ticket mis à jour
 */
export async function updateTicket(ticketId, ticketData, userId) {
  const response = await apiClient.put(`/tickets/${ticketId}`, ticketData, {
    params: { user_id: userId }
  })
  return response.data
}

/**
 * Met à jour le statut d'un ticket (AGENT/MANAGER uniquement)
 * @param {number} ticketId - ID du ticket
 * @param {string} newStatus - Nouveau statut (OPEN, IN_PROGRESS, RESOLVED, CLOSED)
 * @param {number} userId - ID de l'utilisateur connecté
 * @returns {Promise} Ticket avec statut mis à jour
 */
export async function updateTicketStatus(ticketId, newStatus, userId) {
  const response = await apiClient.put(`/tickets/${ticketId}/status`, {
    user_id: userId,
    status: newStatus
  })
  return response.data
}

/**
 * Supprime un ticket
 * @param {number} ticketId - ID du ticket
 * @param {number} userId - ID de l'utilisateur connecté
 * @returns {Promise} Confirmation de suppression
 */
export async function deleteTicket(ticketId, userId) {
  const response = await apiClient.delete(`/tickets/${ticketId}`, {
    params: { user_id: userId }
  })
  return response.data
}

// Export par défaut pour faciliter l'import
export default {
  getTickets: fetchTickets,
  getStats: fetchStats,
  getTicket: fetchTicket,
  createTicket,
  updateTicket,
  updateTicketStatus,
  deleteTicket
}
