/**
 * Formatte une date en format lisible
 * @param {string} dateString - Date au format ISO
 * @returns {string} Date formatée
 */
export function formatDate(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  })
}

/**
 * Formatte une heure
 * @param {string} dateString - Date au format ISO
 * @returns {string} Heure formatée
 */
export function formatTime(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleTimeString('fr-FR', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

/**
 * Formatte une date et heure complète
 * @param {string} dateString - Date au format ISO
 * @returns {string} Date et heure formatées
 */
export function formatDateTime(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleString('fr-FR', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
