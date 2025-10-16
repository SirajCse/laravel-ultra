<template>
  <div class="ultra-table" :class="tableClasses">
    <!-- AI Assistant -->
    <div v-if="features.ai_enhanced" class="ai-assistant">
      <button @click="toggleAIAssistant" class="ai-toggle">
        🤖 AI Assistant
      </button>
      <div v-if="showAIAssistant" class="ai-panel">
        <div class="ai-suggestions">
          <h4>AI Suggestions</h4>
          <div v-for="suggestion in aiSuggestions" :key="suggestion.id" class="suggestion">
            {{ suggestion.text }}
            <button @click="applySuggestion(suggestion)">Apply</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Header with Filters -->
    <div class="table-header">
      <div class="filters">
        <div v-for="filter in filters" :key="filter.key" class="filter">
          <label>{{ filter.label }}</label>
          <select v-if="filter.type === 'select'" v-model="filterValues[filter.key]">
            <option value="">All</option>
            <option v-for="option in filter.options" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </div>
        <input v-model="searchQuery" placeholder="Search..." @input="performSearch" />
      </div>
    </div>

    <!-- Main Table -->
    <div class="table-container">
      <table>
        <thead>
        <tr>
          <th v-for="column in columns" :key="column.key"
              :class="{ sortable: column.sortable }"
              @click="column.sortable && sort(column.key)">
            {{ column.label }}
            <span v-if="column.sortable" class="sort-indicator">
                                {{ getSortIndicator(column.key) }}
                            </span>
          </th>
          <th v-if="actions.length">Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="row in paginatedData" :key="row.id">
          <td v-for="column in columns" :key="column.key">
            <component
                :is="getCellComponent(column.type)"
                :value="row[column.key]"
                :column="column"
                :row="row" />
          </td>
          <td v-if="actions.length" class="actions">
            <button
                v-for="action in actions"
                :key="action.name"
                @click="executeAction(action, row)"
                :class="action.name">
              {{ action.label }}
            </button>
          </td>
        </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination" class="pagination">
      <button @click="changePage(1)" :disabled="pagination.current_page === 1">First</button>
      <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1">Previous</button>

      <span v-for="page in visiblePages" :key="page">
                <button
                    @click="changePage(page)"
                    :class="{ active: page === pagination.current_page }">
                    {{ page }}
                </button>
            </span>

      <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page">Next</button>
      <button @click="changePage(pagination.last_page)" :disabled="pagination.current_page === pagination.last_page">Last</button>

      <span class="page-info">
                Page {{ pagination.current_page }} of {{ pagination.last_page }}
                ({{ pagination.total }} total records)
            </span>
    </div>

    <!-- Real-time Collaboration -->
    <div v-if="features.realtime_collaboration" class="collaboration">
      <div class="online-users">
                <span v-for="user in onlineUsers" :key="user.id" class="user">
                    {{ user.name }}
                </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import TextCell from './cells/TextCell.vue'
import DateCell from './cells/DateCell.vue'
import BooleanCell from './cells/BooleanCell.vue'

const props = defineProps({
  data: Array,
  columns: Array,
  pagination: Object,
  filters: {
    type: Array,
    default: () => []
  },
  actions: {
    type: Array,
    default: () => []
  },
  features: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['sort', 'filter', 'search', 'page-change', 'action'])

// Reactive state
const searchQuery = ref('')
const filterValues = ref({})
const sortColumn = ref('')
const sortDirection = ref('asc')
const showAIAssistant = ref(false)
const aiSuggestions = ref([])
const onlineUsers = ref([])

// Computed properties
const paginatedData = computed(() => {
  return props.data
})

const visiblePages = computed(() => {
  if (!props.pagination) return []

  const current = props.pagination.current_page
  const last = props.pagination.last_page
  const delta = 2
  const range = []

  for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
    range.push(i)
  }

  if (current - delta > 2) range.unshift('...')
  if (current + delta < last - 1) range.push('...')

  range.unshift(1)
  if (last !== 1) range.push(last)

  return range
})

const tableClasses = computed(() => ({
  'ultra-table': true,
  'ai-enhanced': props.features.ai_enhanced,
  'realtime-enabled': props.features.realtime_collaboration
}))

// Methods
const getCellComponent = (type) => {
  const components = {
    text: TextCell,
    date: DateCell,
    boolean: BooleanCell,
    email: TextCell,
    number: TextCell
  }
  return components[type] || TextCell
}

const getSortIndicator = (columnKey) => {
  if (sortColumn.value !== columnKey) return '↕️'
  return sortDirection.value === 'asc' ? '↑' : '↓'
}

const sort = (columnKey) => {
  if (sortColumn.value === columnKey) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortColumn.value = columnKey
    sortDirection.value = 'asc'
  }

  emit('sort', {
    column: sortColumn.value,
    direction: sortDirection.value
  })
}

const performSearch = debounce(() => {
  emit('search', searchQuery.value)
}, 300)

const changePage = (page) => {
  if (page !== '...') {
    emit('page-change', page)
  }
}

const executeAction = (action, row) => {
  if (action.confirm) {
    if (!confirm(action.confirm_message)) return
  }

  emit('action', { action: action.name, row })
}

const toggleAIAssistant = () => {
  showAIAssistant.value = !showAIAssistant.value
}

const applySuggestion = (suggestion) => {
  // Apply AI suggestion logic
  console.log('Applying suggestion:', suggestion)
}

// Watch filters
watch(filterValues, (newFilters) => {
  emit('filter', newFilters)
}, { deep: true })

// Lifecycle
onMounted(() => {
  // Initialize real-time features
  if (props.features.realtime_collaboration) {
    initializeRealtime()
  }

  // Load AI suggestions
  if (props.features.ai_enhanced) {
    loadAISuggestions()
  }
})

// Utility function
function debounce(func, wait) {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}
</script>

<style scoped>
.ultra-table {
  width: 100%;
  border-collapse: collapse;
}

.table-header {
  margin-bottom: 1rem;
}

.filters {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.filter label {
  margin-right: 0.5rem;
}

.sortable {
  cursor: pointer;
  user-select: none;
}

.sort-indicator {
  margin-left: 0.5rem;
}

.pagination {
  margin-top: 1rem;
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.pagination button {
  padding: 0.5rem 1rem;
  border: 1px solid #ddd;
  background: white;
  cursor: pointer;
}

.pagination button.active {
  background: #007bff;
  color: white;
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.ai-assistant {
  margin-bottom: 1rem;
}

.ai-toggle {
  background: #ff6b6b;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
}

.ai-panel {
  border: 1px solid #ddd;
  padding: 1rem;
  margin-top: 0.5rem;
  border-radius: 4px;
}

.collaboration {
  margin-top: 1rem;
  padding: 0.5rem;
  background: #f8f9fa;
  border-radius: 4px;
}

.online-users {
  display: flex;
  gap: 0.5rem;
}

.user {
  background: #007bff;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-size: 0.8rem;
}
</style>