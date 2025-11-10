import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import './assets/styles/base.css'
import App from './App.vue'
import { useAuthStore } from './stores/auth'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

// Restaurer la session après l'initialisation de Pinia
const authStore = useAuthStore()
authStore.restoreSession()

app.mount('#app')
