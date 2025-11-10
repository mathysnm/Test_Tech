<template>
  <div class="create-ticket-page">
    <div class="page-wrapper">
      <div class="ticket-container">
        <!-- Header avec retour -->
        <div class="page-header">
          <button @click="goBack" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            Retour à la liste
          </button>
        </div>

        <!-- Formulaire de création -->
        <div class="form-card">
          <h1 class="form-title">
            <i class="fas fa-plus-circle"></i>
            Nouvelle Demande
          </h1>

          <form @submit.prevent="handleSubmit" class="ticket-form">
            <!-- Titre -->
            <div class="form-group">
              <label for="title" class="form-label">
                Titre <span class="required">*</span>
              </label>
              <input
                id="title"
                v-model="form.title"
                type="text"
                class="form-input"
                :class="{ 'input-error': errors.title }"
                placeholder="Ex: Problème de connexion"
                maxlength="255"
                required
              />
              <span v-if="errors.title" class="error-message">{{ errors.title }}</span>
            </div>

            <!-- Description -->
            <div class="form-group">
              <label for="description" class="form-label">
                Description <span class="required">*</span>
              </label>
              <textarea
                id="description"
                v-model="form.description"
                class="form-textarea"
                :class="{ 'input-error': errors.description }"
                placeholder="Décrivez votre problème en détail..."
                rows="6"
                required
              ></textarea>
              <span v-if="errors.description" class="error-message">{{ errors.description }}</span>
            </div>

            <!-- Priorité -->
            <div class="form-group">
              <label for="priority" class="form-label">
                Priorité <span class="required">*</span>
              </label>
              <select
                id="priority"
                v-model="form.priority"
                class="form-select"
                :class="{ 'input-error': errors.priority }"
                required
              >
                <option value="">Sélectionnez une priorité</option>
                <option value="LOW">Basse - Peut attendre</option>
                <option value="MEDIUM">Moyenne - À traiter prochainement</option>
                <option value="HIGH">Haute - Urgent</option>
                <option value="CRITICAL">Critique - Bloquant</option>
              </select>
              <span v-if="errors.priority" class="error-message">{{ errors.priority }}</span>
            </div>

            <!-- Actions -->
            <div class="form-actions">
              <button 
                type="button" 
                @click="goBack" 
                class="btn-secondary"
                :disabled="submitting"
              >
                Annuler
              </button>
              <button 
                type="submit" 
                class="btn-primary"
                :disabled="submitting"
              >
                <span v-if="submitting">
                  <i class="fas fa-spinner fa-spin"></i>
                  Création en cours...
                </span>
                <span v-else>
                  <i class="fas fa-paper-plane"></i>
                  Créer la demande
                </span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { createTicket } from '@/services/ticketService'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  title: '',
  description: '',
  priority: ''
})

const errors = reactive({
  title: '',
  description: '',
  priority: ''
})

const submitting = ref(false)

function validateForm() {
  let isValid = true
  
  // Reset errors
  errors.title = ''
  errors.description = ''
  errors.priority = ''

  // Validate title
  if (!form.title.trim()) {
    errors.title = 'Le titre est obligatoire'
    isValid = false
  }

  // Validate description
  if (!form.description.trim()) {
    errors.description = 'La description est obligatoire'
    isValid = false
  }

  // Validate priority
  if (!form.priority) {
    errors.priority = 'La priorité est obligatoire'
    isValid = false
  }

  return isValid
}

async function handleSubmit() {
  if (!validateForm()) {
    return
  }

  submitting.value = true

  try {
    const response = await createTicket({
      title: form.title.trim(),
      description: form.description.trim(),
      priority: form.priority,
      creator_id: authStore.currentUser.id
    })

    if (response.success) {
      // Succès - rediriger vers la liste avec message
      router.push({
        name: 'tickets',
        query: { created: 'true' }
      })
    } else {
      alert('Erreur: ' + response.message)
    }
  } catch (error) {
    console.error('Error creating ticket:', error)
    alert('Erreur lors de la création de la demande')
  } finally {
    submitting.value = false
  }
}

function goBack() {
  router.push({ name: 'tickets' })
}
</script>

<style scoped>
.create-ticket-page {
  min-height: 100vh;
  background: #f7fafc;
}

.page-wrapper {
  padding: 2rem 0;
  min-height: 100vh;
}

.ticket-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 0 2rem;
  padding-bottom: 2rem;
}

.page-header {
  margin-bottom: 2rem;
}

.btn-back {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-weight: 600;
  color: #4a5568;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-back:hover {
  border-color: #667eea;
  color: #667eea;
  transform: translateX(-4px);
}

.form-card {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.form-title {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.75rem;
  font-weight: 700;
  color: #2d3748;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #e2e8f0;
}

.form-title i {
  color: #667eea;
}

.ticket-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-label {
  font-weight: 600;
  color: #2d3748;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.required {
  color: #f56565;
}

.form-input,
.form-textarea,
.form-select {
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.2s;
  font-family: inherit;
}

.form-input:focus,
.form-textarea:focus,
.form-select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-textarea {
  resize: vertical;
  min-height: 120px;
}

.input-error {
  border-color: #f56565;
}

.input-error:focus {
  border-color: #f56565;
  box-shadow: 0 0 0 3px rgba(245, 101, 101, 0.1);
}

.error-message {
  color: #f56565;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 2px solid #e2e8f0;
}

.btn-secondary,
.btn-primary {
  flex: 1;
  padding: 0.875rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  border: none;
}

.btn-secondary {
  background: #e2e8f0;
  color: #4a5568;
}

.btn-secondary:hover:not(:disabled) {
  background: #cbd5e0;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #5568d3;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-secondary:disabled,
.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .form-actions {
    flex-direction: column;
  }
}
</style>
