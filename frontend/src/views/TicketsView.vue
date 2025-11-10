<template>
  <div class="tickets-page">
    <div class="page-container">
      <div class="page-header">
        <h1 class="page-title">Système de Gestion des Demandes</h1>
        <p class="page-subtitle">Vue d'ensemble et suivi des demandes de support juridique</p>
      </div>

      <StatsCard />
      <TicketList />
    </div>
  </div>
</template>

<script setup>
import StatsCard from '../components/tickets/StatsCard.vue'
import TicketList from '../components/tickets/TicketList.vue'
</script>

<style scoped>
.tickets-page {
  min-height: 100%;
  padding: 2rem 0;
}

.page-container {
  max-width: 1600px;
  margin: 0 auto;
  padding: 0 2rem;
}

.page-header {
  margin-bottom: 2rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: var(--color-primary);
  margin-bottom: 0.5rem;
  letter-spacing: -0.025em;
}

.page-subtitle {
  font-size: 1rem;
  color: var(--color-text-muted);
  font-weight: 500;
}
</style>
