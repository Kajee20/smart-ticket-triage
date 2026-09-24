<template>
  <div class="ticket-details">
    <div v-if="loading">
      Loading ticket...
    </div>

    <div v-else-if="ticket">
      <button class="ticket-details__back" @click="goBack">
        ← Back to Tickets
      </button>

      <div class="ticket-details__header">
        <div>
          <h1>{{ ticket.subject }}</h1>
          <p>Ticket ID: {{ ticket.id }}</p>
        </div>

        <button
          class="ticket-details__classify"
          :disabled="classifying"
          @click="classifyTicket"
        >
          {{ classifying ? 'Classifying...' : 'Run Classification' }}
        </button>
      </div>

      <div class="ticket-details__grid">
        <div class="ticket-details__main">
          <div class="ticket-details__card">
            <h2>Ticket Body</h2>
            <p>{{ ticket.body }}</p>
          </div>

          <div class="ticket-details__card">
            <h2>Internal Note</h2>

            <textarea
              v-model="note"
              rows="5"
              placeholder="Add internal note..."
            ></textarea>

            <button
              class="ticket-details__save"
              @click="saveNote"
            >
              Save Note
            </button>
          </div>
        </div>

        <div class="ticket-details__side">
          <div class="ticket-details__card">
            <h2>Status</h2>

            <select v-model="status" @change="updateStatus">
              <option value="open">Open</option>
              <option value="in_progress">In Progress</option>
              <option value="resolved">Resolved</option>
              <option value="closed">Closed</option>
            </select>
          </div>

          <div class="ticket-details__card">
            <h2>Category</h2>

            <select v-model="category" @change="updateCategory">
              <option value="">Not classified</option>
              <option value="Technical">Technical</option>
              <option value="Billing">Billing</option>
              <option value="Account">Account</option>
              <option value="General">General</option>
            </select>
          </div>

          <div class="ticket-details__card">
            <h2>AI Classification</h2>

            <p>
              <strong>Explanation:</strong>
            </p>

            <p>
              {{ ticket.explanation || 'Not classified yet.' }}
            </p>

            <p>
              <strong>Confidence:</strong>
              {{ ticket.confidence ?? '-' }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <div v-else>
      Ticket not found.
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const ticket = ref(null)
const loading = ref(true)
const classifying = ref(false)

const status = ref('')
const category = ref('')
const note = ref('')

async function loadTicket() {
  loading.value = true

  try {
    const response = await fetch(
      `http://127.0.0.1:8000/api/tickets/${route.params.id}`
    )

    const data = await response.json()

    ticket.value = data.ticket

    status.value = ticket.value.status || ''
    category.value = ticket.value.category || ''
    note.value = ticket.value.internal_note || ''
  } catch (error) {
    console.error('Failed to load ticket:', error)
  } finally {
    loading.value = false
  }
}
async function updateTicket(data) {
  try {
    const response = await fetch(
      `http://127.0.0.1:8000/api/tickets/${route.params.id}`,
      {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
      }
    )

    if (!response.ok) {
      throw new Error('Failed to update ticket.')
    }

    const result = await response.json()

    ticket.value = result.ticket

    note.value = result.ticket.internal_note || ''

    alert('Ticket updated successfully.')
  } catch (error) {
    console.error('Update ticket failed:', error)
    alert('Failed to update ticket.')
  }
}
async function updateStatus() {
  await updateTicket({
    status: status.value,
  })
}

async function updateCategory() {
  await updateTicket({
    category: category.value || null,
  })
}

async function saveNote() {
  await updateTicket({
    internal_note: note.value || null,
  })
}

async function classifyTicket() {
  classifying.value = true

  try {
    await fetch(
      `http://127.0.0.1:8000/api/tickets/${route.params.id}/classify`,
      {
        method: 'POST',
      }
    )

    alert('Classification queued successfully.')
  } catch (error) {
    console.error('Classification failed:', error)
  } finally {
    classifying.value = false
  }
}

function goBack() {
  router.push('/tickets')
}

onMounted(() => {
  loadTicket()
})
</script>

<style scoped>
.ticket-details {
  padding: 30px;
}

.ticket-details__back {
  margin-bottom: 20px;
  padding: 8px 14px;
  border: none;
  cursor: pointer;
}

.ticket-details__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}

.ticket-details__header h1 {
  margin: 0;
}

.ticket-details__header p {
  color: #666;
}

.ticket-details__classify,
.ticket-details__save {
  padding: 10px 18px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.ticket-details__classify:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.ticket-details__grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 20px;
}

.ticket-details__card {
  background: white;
  padding: 20px;
  margin-bottom: 20px;
  border-radius: 8px;
}

.ticket-details__card h2 {
  margin-top: 0;
}

.ticket-details__card textarea,
.ticket-details__card select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
}

.ticket-details__card textarea {
  resize: vertical;
  margin-bottom: 10px;
}

.ticket-details__card select {
  background: white;
}
</style>