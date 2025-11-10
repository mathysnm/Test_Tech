/**
 * 🎯 Composable useTicketFormatters
 * Fonctions utilitaires réutilisables pour le formatage des tickets
 */

export function useTicketFormatters() {
  /**
   * Statuts disponibles avec leurs propriétés UI
   */
  const AVAILABLE_STATUSES = [
    { 
      value: 'OPEN', 
      label: 'Ouvert', 
      icon: 'fas fa-circle-notch',
      color: '#5b21b6',
      bgColor: '#ede9fe'
    },
    { 
      value: 'IN_PROGRESS', 
      label: 'En cours', 
      icon: 'fas fa-spinner',
      color: '#1e40af',
      bgColor: '#dbeafe'
    },
    { 
      value: 'RESOLVED', 
      label: 'Résolu', 
      icon: 'fas fa-check-circle',
      color: '#065f46',
      bgColor: '#d1fae5'
    },
    { 
      value: 'CLOSED', 
      label: 'Fermé', 
      icon: 'fas fa-times-circle',
      color: '#374151',
      bgColor: '#f3f4f6'
    }
  ]

  /**
   * Priorités disponibles avec leurs propriétés UI
   */
  const AVAILABLE_PRIORITIES = [
    { 
      value: 'HIGH', 
      label: 'Urgente', 
      icon: 'fas fa-flag',
      color: '#991b1b',
      bgColor: '#fee2e2'
    },
    { 
      value: 'MEDIUM', 
      label: 'Normale', 
      icon: 'fas fa-flag',
      color: '#92400e',
      bgColor: '#fef3c7'
    },
    { 
      value: 'LOW', 
      label: 'Basse', 
      icon: 'fas fa-flag',
      color: '#065f46',
      bgColor: '#d1fae5'
    }
  ]

  /**
   * Rôles disponibles avec leurs propriétés UI
   */
  const AVAILABLE_ROLES = [
    { 
      value: 'CLIENT', 
      label: 'Client',
      color: '#1e40af',
      bgColor: '#dbeafe'
    },
    { 
      value: 'AGENT', 
      label: 'Agent',
      color: '#5b21b6',
      bgColor: '#ede9fe'
    },
    { 
      value: 'MANAGER', 
      label: 'Responsable',
      color: '#9f1239',
      bgColor: '#fce7f3'
    }
  ]

  /**
   * Récupère le libellé d'un statut
   * @param {string} status - Code du statut
   * @returns {string} Libellé français
   */
  const getStatusLabel = (status) => {
    const statusObj = AVAILABLE_STATUSES.find(s => s.value === status)
    return statusObj ? statusObj.label : status
  }

  /**
   * Récupère l'icône d'un statut
   * @param {string} status - Code du statut
   * @returns {string} Classe FontAwesome
   */
  const getStatusIcon = (status) => {
    const statusObj = AVAILABLE_STATUSES.find(s => s.value === status)
    return statusObj ? statusObj.icon : 'fas fa-circle'
  }

  /**
   * Récupère la couleur d'un statut
   * @param {string} status - Code du statut
   * @returns {string} Code couleur hex
   */
  const getStatusColor = (status) => {
    const statusObj = AVAILABLE_STATUSES.find(s => s.value === status)
    return statusObj ? statusObj.color : '#374151'
  }

  /**
   * Récupère le libellé d'une priorité
   * @param {string} priority - Code de la priorité
   * @returns {string} Libellé français
   */
  const getPriorityLabel = (priority) => {
    const priorityObj = AVAILABLE_PRIORITIES.find(p => p.value === priority)
    return priorityObj ? priorityObj.label : priority
  }

  /**
   * Récupère l'icône d'une priorité
   * @param {string} priority - Code de la priorité
   * @returns {string} Classe FontAwesome
   */
  const getPriorityIcon = (priority) => {
    const priorityObj = AVAILABLE_PRIORITIES.find(p => p.value === priority)
    return priorityObj ? priorityObj.icon : 'fas fa-flag'
  }

  /**
   * Récupère la couleur d'une priorité
   * @param {string} priority - Code de la priorité
   * @returns {string} Code couleur hex
   */
  const getPriorityColor = (priority) => {
    const priorityObj = AVAILABLE_PRIORITIES.find(p => p.value === priority)
    return priorityObj ? priorityObj.color : '#374151'
  }

  /**
   * Récupère le libellé d'un rôle
   * @param {string} role - Code du rôle
   * @returns {string} Libellé français
   */
  const getRoleLabel = (role) => {
    const roleObj = AVAILABLE_ROLES.find(r => r.value === role)
    return roleObj ? roleObj.label : role
  }

  /**
   * Récupère la couleur d'un rôle
   * @param {string} role - Code du rôle
   * @returns {string} Code couleur hex
   */
  const getRoleColor = (role) => {
    const roleObj = AVAILABLE_ROLES.find(r => r.value === role)
    return roleObj ? roleObj.color : '#374151'
  }

  return {
    // Constantes
    AVAILABLE_STATUSES,
    AVAILABLE_PRIORITIES,
    AVAILABLE_ROLES,
    
    // Statuts
    getStatusLabel,
    getStatusIcon,
    getStatusColor,
    
    // Priorités
    getPriorityLabel,
    getPriorityIcon,
    getPriorityColor,
    
    // Rôles
    getRoleLabel,
    getRoleColor
  }
}
