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

/**
 * Calcule le temps écoulé depuis une date
 * @param {string} dateString - Date au format ISO
 * @returns {string} Temps écoulé (ex: "il y a 2 heures")
 */
export function timeAgo(dateString) {
  if (!dateString) return '-'
  
  const date = new Date(dateString)
  const now = new Date()
  const seconds = Math.floor((now - date) / 1000)
  
  const intervals = {
    année: 31536000,
    mois: 2592000,
    semaine: 604800,
    jour: 86400,
    heure: 3600,
    minute: 60
  }
  
  for (const [name, secondsInInterval] of Object.entries(intervals)) {
    const interval = Math.floor(seconds / secondsInInterval)
    if (interval >= 1) {
      return interval === 1 
        ? `il y a 1 ${name}`
        : `il y a ${interval} ${name}s`
    }
  }
  
  return 'à l\'instant'
}
