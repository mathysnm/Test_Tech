<template>
  <section class="content-section">
    <div class="section-header">
      <i class="fas fa-file-alt"></i>
      <h2>Description de la demande</h2>
    </div>
    <div class="description-content">
      <p class="description">{{ ticket.description }}</p>
    </div>
  </section>
</template>

<script setup>
defineProps({
  ticket: {
    type: Object,
    required: true
  }
})
</script>

<style scoped>
.content-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.section-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #f0f0f0;
}

.section-header i {
  color: #667eea;
  font-size: 1.25rem;
}

.section-header h2 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #2d3748;
  margin: 0;
}

.description-content {
  line-height: 1.8;
}

.description {
  color: #4a5568;
  font-size: 1rem;
  margin: 0;
  white-space: pre-wrap;
}
</style>
