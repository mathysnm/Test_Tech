/**
 * Constantes de l'application
 * Centralisation de toutes les valeurs fixes
 */

export const API_URL = 'http://localhost:8000/api'

export const STATUS = {
  NEW: 'NEW',
  IN_PROGRESS: 'IN_PROGRESS',
  RESOLVED: 'RESOLVED',
  CLOSED: 'CLOSED'
}

export const STATUS_LABELS = {
  NEW: 'Nouveau',
  IN_PROGRESS: 'En cours',
  RESOLVED: 'Résolu',
  CLOSED: 'Fermé'
}

export const STATUS_COLORS = {
  NEW: '#3b82f6',      // Bleu
  IN_PROGRESS: '#f59e0b', // Orange
  RESOLVED: '#10b981',  // Vert
  CLOSED: '#6b7280'     // Gris
}

export const PRIORITY = {
  LOW: 'LOW',
  MEDIUM: 'MEDIUM',
  HIGH: 'HIGH',
  URGENT: 'URGENT'
}

export const PRIORITY_LABELS = {
  LOW: 'Basse',
  MEDIUM: 'Moyenne',
  HIGH: 'Haute',
  URGENT: 'Urgente'
}

export const PRIORITY_COLORS = {
  LOW: '#10b981',    // Vert
  MEDIUM: '#3b82f6', // Bleu
  HIGH: '#f59e0b',   // Orange
  URGENT: '#ef4444'  // Rouge
}

export const ROLES = {
  CLIENT: 'CLIENT',
  AGENT: 'AGENT',
  MANAGER: 'MANAGER'
}

export const ROLE_LABELS = {
  CLIENT: 'Client',
  AGENT: 'Agent',
  MANAGER: 'Responsable'
}
