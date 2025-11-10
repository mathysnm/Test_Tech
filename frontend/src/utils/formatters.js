/**
 * Fonctions de formatage
 * Conversion et affichage des données
 */

import { STATUS_LABELS, PRIORITY_LABELS, ROLE_LABELS } from '@/constants'

/**
 * Formate une date ISO en format français
 * @param {string} dateString - Date au format ISO
 * @returns {string} Date formatée (ex: "08/11/2025 14:30")
 */
export function formatDate(dateString) {
  if (!dateString) return '-'
  
  const date = new Date(dateString)
  const day = String(date.getDate()).padStart(2, '0')
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const year = date.getFullYear()
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')
  
  return `${day}/${month}/${year} ${hours}:${minutes}`
}

/**
 * Formate un statut en libellé français
 * @param {string} status - Statut brut (ex: "NEW")
 * @returns {string} Libellé (ex: "Nouveau")
 */
export function formatStatus(status) {
  return STATUS_LABELS[status] || status
}

/**
 * Formate une priorité en libellé français
 * @param {string} priority - Priorité brute (ex: "HIGH")
 * @returns {string} Libellé (ex: "Haute")
 */
export function formatPriority(priority) {
  return PRIORITY_LABELS[priority] || priority
}

/**
 * Formate un rôle en libellé français
 * @param {string} role - Rôle brut (ex: "AGENT")
 * @returns {string} Libellé (ex: "Agent")
 */
export function formatRole(role) {
  return ROLE_LABELS[role] || role
}

/**
 * Tronque un texte avec ellipse
 * @param {string} text - Texte à tronquer
 * @param {number} maxLength - Longueur maximale
 * @returns {string} Texte tronqué
 */
export function truncate(text, maxLength = 100) {
  if (!text) return ''
  if (text.length <= maxLength) return text
  return text.substring(0, maxLength) + '...'
}
