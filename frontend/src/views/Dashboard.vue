<template>
  <div class="dashboard">

    <div class="dashboard__header">
      <div>
        <h1>Dashboard</h1>
        <p>Smart Ticket Triage Overview</p>
      </div>

      <button class="dashboard__refresh" @click="loadStats">
        Refresh
      </button>
    </div>

    <div class="dashboard__cards">

      <div class="dashboard__card">
        <h3>Total Tickets</h3>
        <p>{{ stats.total }}</p>
      </div>

      <div class="dashboard__card">
        <h3>Open</h3>
        <p>{{ stats.by_status.open || 0 }}</p>
      </div>

      <div class="dashboard__card">
        <h3>In Progress</h3>
        <p>{{ stats.by_status.in_progress || 0 }}</p>
      </div>

      <div class="dashboard__card">
        <h3>Resolved</h3>
        <p>{{ stats.by_status.resolved || 0 }}</p>
      </div>

      <div class="dashboard__card">
        <h3>Closed</h3>
        <p>{{ stats.by_status.closed || 0 }}</p>
      </div>

    </div>

    <h2 class="dashboard__section-title">
      Tickets by Category
    </h2>

    <div class="dashboard__cards dashboard__cards--categories">

      <div class="dashboard__card">
        <h3>Technical</h3>
        <p>{{ stats.by_category.Technical || 0 }}</p>
      </div>

      <div class="dashboard__card">
        <h3>Billing</h3>
        <p>{{ stats.by_category.Billing || 0 }}</p>
      </div>

      <div class="dashboard__card">
        <h3>Account</h3>
        <p>{{ stats.by_category.Account || 0 }}</p>
      </div>

      <div class="dashboard__card">
        <h3>General</h3>
        <p>{{ stats.by_category.General || 0 }}</p>
      </div>

    </div>

    <h2 class="dashboard__section-title">
      Category Chart
    </h2>

    <div class="dashboard__chart">

      <div class="dashboard__bar-row">
        <span>Technical</span>

        <div class="dashboard__bar-container">
          <div
            class="dashboard__bar"
            :style="{ width: getBarWidth(stats.by_category.Technical) }"
          ></div>
        </div>

        <strong>{{ stats.by_category.Technical || 0 }}</strong>
      </div>

      <div class="dashboard__bar-row">
        <span>Billing</span>

        <div class="dashboard__bar-container">
          <div
            class="dashboard__bar"
            :style="{ width: getBarWidth(stats.by_category.Billing) }"
          ></div>
        </div>

        <strong>{{ stats.by_category.Billing || 0 }}</strong>
      </div>

      <div class="dashboard__bar-row">
        <span>Account</span>

        <div class="dashboard__bar-container">
          <div
            class="dashboard__bar"
            :style="{ width: getBarWidth(stats.by_category.Account) }"
          ></div>
        </div>

        <strong>{{ stats.by_category.Account || 0 }}</strong>
      </div>

      <div class="dashboard__bar-row">
        <span>General</span>

        <div class="dashboard__bar-container">
          <div
            class="dashboard__bar"
            :style="{ width: getBarWidth(stats.by_category.General) }"
          ></div>
        </div>

        <strong>{{ stats.by_category.General || 0 }}</strong>
      </div>

    </div>

    <p v-if="loading" class="dashboard__loading">
      Loading dashboard...
    </p>

    <p v-if="errorMessage" class="dashboard__error">
      {{ errorMessage }}
    </p>

  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'

const loading = ref(false)
const errorMessage = ref('')

const stats = reactive({
  total: 0,
  by_status: {},
  by_category: {},
})

async function loadStats() {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await fetch(
      'http://127.0.0.1:8000/api/stats'
    )

    if (!response.ok) {
      throw new Error('Failed to load dashboard data.')
    }

    const data = await response.json()

    stats.total = data.total || 0
    stats.by_status = data.by_status || {}
    stats.by_category = data.by_category || {}
  } catch (error) {
    console.error('Failed to load stats:', error)

    errorMessage.value = 'Unable to load dashboard data.'
  } finally {
    loading.value = false
  }
}

function getBarWidth(value) {
  const number = Number(value || 0)

  const categoryValues = Object.values(stats.by_category)
    .map(Number)

  const max = Math.max(...categoryValues, 1)

  return `${(number / max) * 100}%`
}

onMounted(() => {
  loadStats()
})
</script>

<style scoped>
.dashboard {
  padding: 30px;
}

.dashboard__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}

.dashboard__header h1 {
  margin: 0;
}

.dashboard__header p {
  margin: 5px 0 0;
  color: #666;
}

.dashboard__refresh {
  padding: 10px 18px;
  border: none;
  border-radius: 6px;
  background: #222;
  color: white;
  cursor: pointer;
}

.dashboard__cards {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 20px;
}

.dashboard__cards--categories {
  grid-template-columns: repeat(4, 1fr);
}

.dashboard__card {
  background: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.dashboard__card h3 {
  margin: 0 0 10px;
  font-size: 16px;
}

.dashboard__card p {
  margin: 0;
  font-size: 28px;
  font-weight: bold;
}

.dashboard__section-title {
  margin: 35px 0 15px;
}

.dashboard__chart {
  background: white;
  padding: 25px;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.dashboard__bar-row {
  display: grid;
  grid-template-columns: 100px 1fr 40px;
  align-items: center;
  gap: 15px;
  margin-bottom: 18px;
}

.dashboard__bar-row:last-child {
  margin-bottom: 0;
}

.dashboard__bar-container {
  height: 20px;
  background: #eee;
  border-radius: 10px;
  overflow: hidden;
}

.dashboard__bar {
  height: 100%;
  background: #222;
  border-radius: 10px;
  transition: width 0.3s ease;
}

.dashboard__loading {
  text-align: center;
  margin-top: 20px;
  color: #666;
}

.dashboard__error {
  margin-top: 20px;
  color: #c00;
}

@media (max-width: 900px) {
  .dashboard__cards {
    grid-template-columns: repeat(2, 1fr);
  }

  .dashboard__cards--categories {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .dashboard {
    padding: 15px;
  }

  .dashboard__cards,
  .dashboard__cards--categories {
    grid-template-columns: 1fr;
  }

  .dashboard__header {
    align-items: flex-start;
    gap: 15px;
  }

  .dashboard__bar-row {
    grid-template-columns: 80px 1fr 30px;
    gap: 8px;
  }
}
</style>