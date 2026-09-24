<template>
  <div class="tickets">

    <!-- Header -->
    <div class="tickets__header">
      <div>
        <h1>Tickets</h1>
        <p>View and manage support tickets.</p>
      </div>

      <div class="tickets__actions">
        <button
          class="tickets__button tickets__button--secondary"
          @click="loadTickets()"
        >
          Refresh
        </button>

        <button
          class="tickets__button tickets__button--secondary"
          @click="exportCSV"
        >
          Export CSV
        </button>

        <button
          class="tickets__button tickets__button--primary"
          @click="showNewTicket = true"
        >
          + New Ticket
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="tickets__filters">

      <input
        v-model="search"
        type="text"
        placeholder="Search tickets..."
        @input="loadTickets()"
      />

      <select
        v-model="status"
        @change="loadTickets()"
      >
        <option value="">All Status</option>
        <option value="open">Open</option>
        <option value="in_progress">In Progress</option>
        <option value="resolved">Resolved</option>
        <option value="closed">Closed</option>
      </select>

      <select
        v-model="category"
        @change="loadTickets()"
      >
        <option value="">All Categories</option>
        <option value="Technical">Technical</option>
        <option value="Billing">Billing</option>
        <option value="Account">Account</option>
        <option value="General">General</option>
      </select>

    </div>

    <!-- Loading -->
    <p
      v-if="loading"
      class="tickets__message"
    >
      Loading tickets...
    </p>

    <!-- Error -->
    <p
      v-else-if="errorMessage"
      class="tickets__error"
    >
      {{ errorMessage }}
    </p>

    <!-- Ticket List -->
    <div
      v-else-if="tickets.length"
      class="tickets__list"
    >

      <button
        v-for="ticket in tickets"
        :key="ticket.id"
        class="tickets__item"
        @click="openTicket(ticket.id)"
      >

        <div class="tickets__item-main">
          <strong>
            {{ ticket.subject }}
          </strong>

          <span>
            #{{ ticket.id }}
          </span>
        </div>

        <div class="tickets__item-info">

          <!-- Status -->
          <span
            class="tickets__status"
            :class="`tickets__status--${ticket.status}`"
          >
            {{ ticket.status }}
          </span>

          <!-- Category -->
          <span class="tickets__category">
            {{ ticket.category || 'Not classified' }}
          </span>

          <!-- Confidence -->
          <span
            v-if="
              ticket.confidence !== null &&
              ticket.confidence !== undefined
            "
          >
            Confidence: {{ ticket.confidence }}
          </span>

          <!-- Internal Note -->
          <span
            v-if="ticket.internal_note"
            class="tickets__note"
          >
            📝 Note
          </span>

          <!-- Explanation Tooltip -->
          <span
            v-if="ticket.explanation"
            class="tickets__explanation"
          >
            ⓘ

            <span class="tickets__tooltip">
              {{ ticket.explanation }}
            </span>
          </span>

        </div>

      </button>

    </div>

    <!-- Empty -->
    <p
      v-else
      class="tickets__message"
    >
      No tickets found.
    </p>

    <!-- Pagination -->
    <div
      v-if="pagination.last > 1"
      class="tickets__pagination"
    >

      <button
        :disabled="!pagination.prev"
        @click="changePage(pagination.current - 1)"
      >
        ← Previous
      </button>

      <span>
        Page {{ pagination.current }}
        of {{ pagination.last }}
      </span>

      <button
        :disabled="!pagination.next"
        @click="changePage(pagination.current + 1)"
      >
        Next →
      </button>

    </div>

    <!-- New Ticket Modal -->
    <div
      v-if="showNewTicket"
      class="tickets__modal"
    >

      <div class="tickets__modal-content">

        <div class="tickets__modal-header">
          <h2>Create New Ticket</h2>

          <button
            class="tickets__modal-close"
            @click="closeModal"
          >
            ×
          </button>
        </div>

        <div class="tickets__form">

          <label>
            Subject
          </label>

          <input
            v-model="newTicket.subject"
            type="text"
            placeholder="Enter ticket subject"
          />

          <label>
            Description
          </label>

          <textarea
            v-model="newTicket.body"
            rows="6"
            placeholder="Describe the problem..."
          ></textarea>

          <p
            v-if="errorMessage"
            class="tickets__error"
          >
            {{ errorMessage }}
          </p>

          <div class="tickets__form-actions">

            <button
              class="tickets__button tickets__button--secondary"
              @click="closeModal"
            >
              Cancel
            </button>

            <button
              class="tickets__button tickets__button--primary"
              :disabled="creating"
              @click="createTicket"
            >
              {{ creating ? 'Creating...' : 'Create Ticket' }}
            </button>

          </div>

        </div>

      </div>

    </div>

  </div>
</template>


<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const tickets = ref([])
const loading = ref(false)

const search = ref('')
const status = ref('')
const category = ref('')

const showNewTicket = ref(false)
const creating = ref(false)

const errorMessage = ref('')

const newTicket = ref({
  subject: '',
  body: '',
})

const pagination = ref({
  current: 1,
  last: 1,
  next: false,
  prev: false,
})


/* Load Tickets */
async function loadTickets(page = 1) {
  loading.value = true
  errorMessage.value = ''

  try {
    const params = new URLSearchParams()

    params.append('page', page)

    if (search.value.trim()) {
      params.append('search', search.value.trim())
    }

    if (status.value) {
      params.append('status', status.value)
    }

    if (category.value) {
      params.append('category', category.value)
    }

    const response = await fetch(
      `http://127.0.0.1:8000/api/tickets?${params.toString()}`
    )

    if (!response.ok) {
      throw new Error('Failed to load tickets.')
    }

    const data = await response.json()

    tickets.value = data.data || []

    pagination.value = {
      current: data.current_page || 1,
      last: data.last_page || 1,
      next: !!data.next_page_url,
      prev: !!data.prev_page_url,
    }

  } catch (error) {

    console.error(
      'Failed to load tickets:',
      error
    )

    errorMessage.value =
      'Unable to load tickets.'

  } finally {

    loading.value = false

  }
}


/* Create Ticket */
async function createTicket() {

  errorMessage.value = ''

  if (!newTicket.value.subject.trim()) {
    errorMessage.value =
      'Subject is required.'
    return
  }

  if (!newTicket.value.body.trim()) {
    errorMessage.value =
      'Description is required.'
    return
  }

  creating.value = true

  try {

    const response = await fetch(
      'http://127.0.0.1:8000/api/tickets',
      {
        method: 'POST',

        headers: {
          'Content-Type': 'application/json',
        },

        body: JSON.stringify({
          subject: newTicket.value.subject,
          body: newTicket.value.body,
        }),
      }
    )

    if (!response.ok) {
      throw new Error(
        'Failed to create ticket.'
      )
    }

    closeModal()

    await loadTickets()

  } catch (error) {

    console.error(error)

    errorMessage.value =
      'Failed to create ticket.'

  } finally {

    creating.value = false

  }
}


/* Close Modal */
function closeModal() {

  showNewTicket.value = false

  newTicket.value = {
    subject: '',
    body: '',
  }

  errorMessage.value = ''
}


/* Pagination */
function changePage(page) {

  if (
    page < 1 ||
    page > pagination.value.last
  ) {
    return
  }

  loadTickets(page)
}


/* Open Ticket */
function openTicket(id) {

  router.push(`/tickets/${id}`)
}


/* Export CSV */
function exportCSV() {

  if (!tickets.value.length) {
    alert('No tickets to export.')
    return
  }

  const headers = [
    'ID',
    'Subject',
    'Status',
    'Category',
    'Confidence',
    'Internal Note',
  ]

  const rows = tickets.value.map(ticket => [
    ticket.id,
    ticket.subject,
    ticket.status,
    ticket.category || '',
    ticket.confidence ?? '',
    ticket.internal_note || '',
  ])

  const csv = [
    headers,
    ...rows,
  ]
    .map(row =>
      row
        .map(value =>
          `"${String(value).replace(/"/g, '""')}"`
        )
        .join(',')
    )
    .join('\n')

  const blob = new Blob(
    [csv],
    {
      type: 'text/csv;charset=utf-8;',
    }
  )

  const url =
    URL.createObjectURL(blob)

  const link =
    document.createElement('a')

  link.href = url
  link.download = 'tickets.csv'

  link.click()

  URL.revokeObjectURL(url)
}


/* Initial Load */
onMounted(() => {
  loadTickets()
})
</script>


<style scoped>

.tickets {
  padding: 30px;
}


/* Header */

.tickets__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}

.tickets__header h1 {
  margin: 0;
}

.tickets__header p {
  color: #666;
}


/* Buttons */

.tickets__actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.tickets__button {
  padding: 10px 18px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.tickets__button--primary {
  background: #222;
  color: white;
}

.tickets__button--secondary {
  background: #e9e9e9;
  color: #222;
}

.tickets__button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}


/* Filters */

.tickets__filters {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 12px;
  margin-bottom: 20px;
}

.tickets__filters input,
.tickets__filters select {
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  background: white;
}


/* Ticket List */

.tickets__list {
  display: grid;
  gap: 12px;
}

.tickets__item {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;

  padding: 18px;

  border: none;
  border-radius: 8px;

  background: white;

  cursor: pointer;

  text-align: left;

  box-shadow:
    0 2px 8px rgba(0, 0, 0, 0.05);

  position: relative;
}

.tickets__item:hover {
  transform: translateY(-1px);
}

.tickets__item-main {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.tickets__item-main span {
  color: #777;
  font-size: 13px;
}

.tickets__item-info {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  justify-content: flex-end;
}


/* Status */

.tickets__status {
  padding: 5px 9px;
  border-radius: 5px;
  font-size: 13px;
  background: #eee;
}

.tickets__status--open {
  background: #e8f5e9;
}

.tickets__status--in_progress {
  background: #fff3cd;
}

.tickets__status--resolved {
  background: #e3f2fd;
}

.tickets__status--closed {
  background: #eeeeee;
}


/* Category */

.tickets__category {
  font-size: 14px;
  color: #444;
}


/* Note */

.tickets__note {
  font-size: 13px;
}


/* Explanation Tooltip */

.tickets__explanation {
  position: relative;

  display: inline-flex;
  align-items: center;
  justify-content: center;

  width: 24px;
  height: 24px;

  border-radius: 50%;

  background: #eef2ff;
  color: #4f46e5;

  font-weight: bold;

  cursor: help;
}

.tickets__tooltip {
  position: absolute;

  bottom: 130%;
  left: 50%;

  transform: translateX(-50%);

  width: 240px;

  padding: 10px 12px;

  border-radius: 8px;

  background: #222;
  color: white;

  font-size: 13px;
  line-height: 1.4;

  text-align: left;

  visibility: hidden;
  opacity: 0;

  transition: opacity 0.2s;

  z-index: 100;
}

.tickets__explanation:hover
.tickets__tooltip {
  visibility: visible;
  opacity: 1;
}


/* Messages */

.tickets__message {
  padding: 30px;
  text-align: center;
  color: #666;
}

.tickets__error {
  color: #c62828;
  margin: 15px 0;
}


/* Pagination */

.tickets__pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
  margin-top: 25px;
}

.tickets__pagination button {
  padding: 8px 14px;
  border: none;
  border-radius: 6px;
  background: #222;
  color: white;
  cursor: pointer;
}

.tickets__pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}


/* Modal */

.tickets__modal {
  position: fixed;
  inset: 0;

  display: flex;
  align-items: center;
  justify-content: center;

  background: rgba(0, 0, 0, 0.45);

  padding: 20px;

  z-index: 1000;
}

.tickets__modal-content {
  width: 100%;
  max-width: 550px;

  background: white;

  border-radius: 10px;

  padding: 25px;
}

.tickets__modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;

  margin-bottom: 20px;
}

.tickets__modal-header h2 {
  margin: 0;
}

.tickets__modal-close {
  border: none;
  background: transparent;
  font-size: 28px;
  cursor: pointer;
}

.tickets__form {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.tickets__form input,
.tickets__form textarea {
  width: 100%;

  padding: 12px;

  border: 1px solid #ccc;
  border-radius: 6px;

  font-family: inherit;
}

.tickets__form textarea {
  resize: vertical;
}

.tickets__form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 10px;
}


/* Responsive */

@media (max-width: 800px) {

  .tickets {
    padding: 15px;
  }

  .tickets__header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }

  .tickets__filters {
    grid-template-columns: 1fr;
  }

  .tickets__item {
    flex-direction: column;
    align-items: flex-start;
  }

  .tickets__item-info {
    justify-content: flex-start;
  }

}

</style>