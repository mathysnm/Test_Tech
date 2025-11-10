/**
 * 🎨 Utilitaires de formatage - LegalDesk
 * Centralise toutes les fonctions de formatage pour éviter la duplication
 */

import { STATUS_LABELS, PRIORITY_LABELS, ROLE_LABELS } from '@/constants'

// ============================================
// 📅 FORMATAGE DES DATES
// ============================================

/**
 * Formate une date au format court français
 * @param {string} dateString - Date au format ISO
 * @returns {string} Format: "09/11/2025"
 * @example formatDateShort('2025-11-09') → "09/11/2025"
 */
export function formatDateShort(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  const day = String(date.getDate()).padStart(2, '0')
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const year = date.getFullYear()
  return `${day}/${month}/${year}`
}

/**
 * Formate une date au format long français
 * @param {string} dateString - Date au format ISO
 * @returns {string} Format: "09 novembre 2025"
 * @example formatDateLong('2025-11-09') → "09 novembre 2025"
 */
export function formatDateLong(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  })
}

/**
 * Formate une date avec heure
 * @param {string} dateString - Date au format ISO
 * @returns {string} Format: "09/11/2025 14:30"
 * @example formatDateTime('2025-11-09T14:30:00') → "09/11/2025 14:30"
 */
export function formatDateTime(dateString) {
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
 * Formate uniquement l'heure
 * @param {string} dateString - Date au format ISO
 * @returns {string} Format: "14:30"
 * @example formatTime('2025-11-09T14:30:00') → "14:30"
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
 * Calcule le temps écoulé depuis une date (relatif)
 * @param {string} dateString - Date au format ISO
 * @returns {string} Format: "il y a 2 heures"
 * @example timeAgo('2025-11-09T12:00:00') → "il y a 2 heures"
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

// ============================================
// 🏷️ FORMATAGE DES LIBELLÉS
// ============================================

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

// ============================================
// ✂️ FORMATAGE DU TEXTE
// ============================================

/**
 * Tronque un texte avec ellipse
 * @param {string} text - Texte à tronquer
 * @param {number} maxLength - Longueur maximale (défaut: 100)
 * @returns {string} Texte tronqué avec "..."
 * @example truncate('Lorem ipsum dolor...', 10) → "Lorem ipsu..."
 */
export function truncate(text, maxLength = 100) {
  if (!text) return ''
  if (text.length <= maxLength) return text
  return text.substring(0, maxLength) + '...'
}

/**
 * Capitalise la première lettre
 * @param {string} text - Texte à capitaliser
 * @returns {string} Texte avec première lettre majuscule
 * @example capitalize('hello world') → "Hello world"
 */
export function capitalize(text) {
  if (!text) return ''
  return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase()
}

// ============================================
// 🔢 FORMATAGE DES NOMBRES
// ============================================

/**
 * Formate un nombre avec séparateurs de milliers
 * @param {number} num - Nombre à formater
 * @returns {string} Nombre formaté (ex: "1 234")
 * @example formatNumber(1234) → "1 234"
 */
export function formatNumber(num) {
  if (num === null || num === undefined) return '0'
  return num.toLocaleString('fr-FR')
}

/**
 * Formate un pourcentage
 * @param {number} value - Valeur entre 0 et 100
 * @param {number} decimals - Nombre de décimales (défaut: 0)
 * @returns {string} Format: "75%"
 * @example formatPercent(75.5, 1) → "75,5%"
 */
export function formatPercent(value, decimals = 0) {
  if (value === null || value === undefined) return '0%'
  return `${value.toFixed(decimals).replace('.', ',')}%`
}

// ============================================
// 🎯 ALIAS POUR COMPATIBILITÉ
// ============================================

/**
 * Alias de formatDateTime pour compatibilité avec l'ancien code
 * @deprecated Utiliser formatDateTime à la place
 */
export const formatDate = formatDateTime
