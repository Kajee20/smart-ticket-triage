<template>
  <div class="app">
    <nav class="app__nav">
      <h2>Smart Ticket Triage</h2>

      <div class="app__links">
        <RouterLink to="/dashboard">Dashboard</RouterLink>
        <RouterLink to="/tickets">Tickets</RouterLink>
      </div>
    </nav>

    <main class="app__content">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<style>
* {
  box-sizing: border-box;
}

body {
  margin: 0;
  font-family: Arial, sans-serif;
  background: #f5f6f8;
}

.app {
  min-height: 100vh;
}

.app__nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 30px;
  background: white;
  border-bottom: 1px solid #ddd;
}

.app__nav h2 {
  margin: 0;
}

.app__links {
  display: flex;
  gap: 20px;
}

.app__links a {
  text-decoration: none;
  color: #333;
}

.app__links a.router-link-active {
  font-weight: bold;
}

.app__content {
  padding: 20px;
}
</style>