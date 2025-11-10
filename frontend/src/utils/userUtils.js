/**
 * Génère une couleur consistante basée sur le nom d'utilisateur
 * @param {string} name - Nom de l'utilisateur
 * @returns {string} Code couleur hexa
 */
export function getUserColor(name) {
  if (!name) return '#6B7280'
  
  const colors = [
    '#3B82F6', // blue
    '#8B5CF6', // purple
    '#EC4899', // pink
    '#F59E0B', // amber
    '#10B981', // emerald
    '#6366F1', // indigo
    '#EF4444', // red
    '#14B8A6', // teal
  ]
  
  // Générer un hash simple du nom
  let hash = 0
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  
  return colors[Math.abs(hash) % colors.length]
}

/**
 * Extrait les initiales d'un nom
 * @param {string} name - Nom complet
 * @returns {string} Initiales (max 2 lettres)
 */
export function getInitials(name) {
  if (!name) return '?'
  
  const words = name.trim().split(' ')
  if (words.length === 1) {
    return words[0].substring(0, 2).toUpperCase()
  }
  
  return (words[0][0] + words[words.length - 1][0]).toUpperCase()
}

/**
 * Formatte le rôle d'un utilisateur
 * @param {string} role - Rôle (CLIENT, AGENT, MANAGER)
 * @returns {string} Rôle formaté
 */
export function formatRole(role) {
  const roleLabels = {
    CLIENT: 'Client',
    AGENT: 'Agent',
    MANAGER: 'Manager'
  }
  return roleLabels[role] || role
}
