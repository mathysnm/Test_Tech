/**
 * Service Utilisateurs
 * Gestion des appels API pour les utilisateurs
 */

import apiClient from './api'

/**
 * Récupère la liste de tous les utilisateurs
 * @returns {Promise} Liste des utilisateurs
 */
export async function fetchUsers() {
  const response = await apiClient.get('/users')
  return response.data
}

/**
 * Récupère un utilisateur par son ID
 * @param {number} userId - ID de l'utilisateur
 * @returns {Promise} Détails de l'utilisateur
 */
export async function fetchUser(userId) {
  const response = await apiClient.get(`/users/${userId}`)
  return response.data
}

// Export par défaut pour faciliter l'import
export default {
  getUsers: fetchUsers,
  getUser: fetchUser
}
