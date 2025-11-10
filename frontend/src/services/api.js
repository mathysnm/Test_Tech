/**
 * Configuration Axios centralisée
 * Instance unique pour tous les appels API
 */

import axios from 'axios'
import { API_URL } from '@/constants'

const apiClient = axios.create({
  baseURL: API_URL,
  timeout: 30000, // 30s pour dev (Symfony debug est lent)
  headers: {
    'Content-Type': 'application/json'
  }
})

// Intercepteur de requête (pour ajouter le token plus tard si besoin)
apiClient.interceptors.request.use(
  (config) => {
    // console.log('API Request:', config.method.toUpperCase(), config.url)
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Intercepteur de réponse (pour gérer les erreurs globalement)
apiClient.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    console.error('API Error:', error.response?.status, error.message)
    return Promise.reject(error)
  }
)

export default apiClient
