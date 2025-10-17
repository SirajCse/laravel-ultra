<template>
  <div class="ultra-table" :class="tableClasses">
    <!-- Views/Bookmarks -->
    <div v-if="views.length" class="table-views">
      <button
          v-for="view in views"
          :key="view.key"
          @click="switchView(view)"
          :class="{ active: currentView.key === view.key }"
      >
        {{ view.name }}
      </button>
      <button @click="saveCurrentView" class="save-view">Save View</button>
    </div>

    <!-- Bulk Actions -->
    <div v-if="bulkActions.length && selectedRows.length" class="bulk-actions">
      <select v-model="selectedBulkAction" @change="executeBulkAction">
        <option value="">Bulk Actions</option>
        <option v-for="action in bulkActions" :key="action.name" :value="action.name">
          {{ action.label }}
        </option>
      </select>
      <span class="selected-count">{{ selectedRows.length }} selected</span>
    </div>

    <!-- Table with sticky header/columns -->
    <div class="table-container" :class="{ 'sticky-header': config.sticky_header }">
      <table>
        <thead>
        <tr>
          <!-- Checkbox for bulk selection -->
          <th v-if="bulkActions.length" class="bulk-checkbox">
            <input
                type="checkbox"
                :checked="allSelected"
                @change="toggleAllSelection"
            >
          </th>

          <th
              v-for="column in visibleColumns"
              :key="column.key"
              :class="[
                                'column-header',
                                {
                                    sortable: column.sortable,
                                    sticky: config.sticky_columns.includes(column.key)
                                }
                            ]"
              @click="column.sortable && sort(column.key)"
          >
            <div class="header-content">
              <span>{{ column.label }}</span>

              <!-- Sort indicator -->
              <span v-if="column.sortable" class="sort-indicator">
                                    {{ getSortIndicator(column.key) }}
                                </span>

              <!-- Column toggle -->
              <button
                  v-if="config.toggleable"
                  @click.stop="toggleColumn(column.key)"
                  class="column-toggle"
              >
                👁
              </button>
            </div>
          </th>

          <!-- Row actions header -->
          <th v-if="rowActions.length" class="row-actions-header">
            Actions
          </th>
        </tr>

        <!-- Filter row -->
        <tr v-if="filters.length" class="filter-row">
          <th v-if="bulkActions.length"></th>
          <th v-for="column in visibleColumns" :key="column.key">
            <component
                :is="getFilterComponent(column)"
                v-model="filterValues[column.key]"
                @update:modelValue="applyFilters"
            />
          </th>
          <th v-if="rowActions.length"></th>
        </tr>
        </thead>

        <tbody>
        <!-- Empty state -->
        <tr v-if="!data.length">
          <td :colspan="visibleColumns.length + (bulkActions.length ? 1 : 0) + (rowActions.length ? 1 : 0)">
            <div class="empty-state">
              <slot name="emptyState">
                <div class="empty-content">
                  <span class="empty-icon">📊</span>
                  <h3>No data found</h3>
                  <p>There are no records to display.</p>
                  <button @click="resetFilters" class="reset-filters">
                    Reset Filters
                  </button>
                </div>
              </slot>
            </div>
          </td>
        </tr>

        <!-- Data rows -->
        <tr
            v-for="row in data"
            :key="getRowKey(row)"
            :class="[
                            'data-row',
                            {
                                selected: isRowSelected(row),
                                'row-clickable': config.row_links
                            }
                        ]"
            @click="handleRowClick(row)"
        >
          <!-- Bulk selection checkbox -->
          <td v-if="bulkActions.length" class="bulk-checkbox">
            <input
                type="checkbox"
                :checked="isRowSelected(row)"
                @change="toggleRowSelection(row)"
                @click.stop
            >
          </td>

          <!-- Data cells -->
          <td
              v-for="column in visibleColumns"
              :key="column.key"
              :class="{ sticky: config.sticky_columns.includes(column.key) }"
          >
            <component
                :is="getCellComponent(column.type)"
                :value="getCellValue(row, column)"
                :column="column"
                :row="row"
            />
          </td>

          <!-- Row actions -->
          <td v-if="rowActions.length" class="row-actions">
            <div class="actions-container">
              <button
                  v-for="action in rowActions"
                  :key="action.name"
                  @click.stop="executeRowAction(action, row)"
                  :class="['action-btn', action.name]"
                  :title="action.tooltip"
              >
                                    <span v-if="action.icon" class="action-icon">
                                        {{ action.icon }}
                                    </span>
                <span v-if="!action.iconOnly" class="action-label">
                                        {{ action.label }}
                                    </span>
              </button>
            </div>
          </td>
        </tr>
        </tbody>
      </table>
    </div>

    <!-- Table Footer -->
    <div class="table-footer">
      <!-- Pagination -->
      <div v-if="pagination" class="pagination-container">
        <div class="pagination-info">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} entries
        </div>

        <div class="pagination-controls">
          <button
              @click="changePage(1)"
              :disabled="pagination.current_page === 1"
          >
            First
          </button>

          <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
          >
            Previous
          </button>

          <span
              v-for="page in visiblePages"
              :key="page"
              class="page-item"
          >
                        <button
                            v-if="page !== '...'"
                            @click="changePage(page)"
                            :class="{ active: page === pagination.current_page }"
                        >
                            {{ page }}
                        </button>
                        <span v-else class="page-ellipsis">{{ page }}</span>
                    </span>

          <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
          >
            Next
          </button>

          <button
              @click="changePage(pagination.last_page)"
              :disabled="pagination.current_page === pagination.last_page"
          >
            Last
          </button>
        </div>

        <!-- Per page selector -->
        <select v-model="perPage" @change="changePerPage" class="per-page-selector">
          <option value="10">10 per page</option>
          <option value="25">25 per page</option>
          <option value="50">50 per page</option>
          <option value="100">100 per page</option>
        </select>
      </div>

      <!-- Export buttons -->
      <div v-if="config.exportable" class="export-options">
        <button @click="exportData('csv')" class="export-btn">Export CSV</button>
        <button @click="exportData('excel')" class="export-btn">Export Excel</button>
        <button @click="exportData('pdf')" class="export-btn">Export PDF</button>
      </div>
    </div>

    <!-- Multiple tables support -->
    <div v-if="multipleTables" class="multiple-tables">
      <button
          v-for="table in tables"
          :key="table.key"
          @click="switchTable(table.key)"
          :class="{ active: currentTable === table.key }"
      >
        {{ table.name }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

// Components
import TextCell from './cells/TextCell.vue'
import ImageCell from './cells/ImageCell.vue'
import DateCell from './cells/DateCell.vue'
import BooleanCell from './cells/BooleanCell.vue'
import SelectFilter from './filters/SelectFilter.vue'
import DateFilter from './filters/DateFilter.vue'
import TextFilter from './filters/TextFilter.vue'

const props = defineProps({
  data: Array,
  columns: Array,
  pagination: Object,
  filters: {
    type: Array,
    default: () => []
  },
  rowActions: {
    type: Array,
    default: () => []
  },
  bulkActions: {
    type: Array,
    default: () => []
  },
  views: {
    type: Array,
    default: () => []
  },
  config: {
    type: Object,
    default: () => ({
      sticky_header: false,
      sticky_columns: [],
      toggleable: true,
      exportable: false,
      row_links: false,
      searchable: true,
      sortable: true
    })
  },
  multipleTables: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits([
  'sort',
  'filter',
  'search',
  'page-change',
  'row-action',
  'bulk-action',
  'view-change',
  'table-change',
  'export',
  'row-click'
])

// Reactive state
const selectedRows = ref([])
const filterValues = ref({})
const searchQuery = ref('')
const sortColumn = ref('')
const sortDirection = ref('asc')
const currentView = ref(props.views.find(v => v.default) || props.views[0])
const currentTable = ref('')
const perPage = ref(props.pagination?.per_page || 25)

// Computed properties
const visibleColumns = computed(() => {
  if (currentView.value?.columns) {
    return props.columns.filter(col =>
        currentView.value.columns.includes(col.key)
    )
  }
  return props.columns.filter(col => !col.hidden)
})

const allSelected = computed(() => {
  return props.data.length > 0 && selectedRows.value.length === props.data.length
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

// Methods
const getCellComponent = (type) => {
  const components = {
    text: TextCell,
    image: ImageCell,
    date: DateCell,
    boolean: BooleanCell,
    email: TextCell,
    number: TextCell
  }
  return components[type] || TextCell
}

const getFilterComponent = (column) => {
  const components = {
    select: SelectFilter,
    date: DateFilter,
    text: TextFilter
  }
  return components[column.filter_type] || TextFilter
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

const toggleRowSelection = (row) => {
  const index = selectedRows.value.findIndex(r => getRowKey(r) === getRowKey(row))
  if (index > -1) {
    selectedRows.value.splice(index, 1)
  } else {
    selectedRows.value.push(row)
  }
}

const toggleAllSelection = () => {
  if (allSelected.value) {
    selectedRows.value = []
  } else {
    selectedRows.value = [...props.data]
  }
}

const isRowSelected = (row) => {
  return selectedRows.value.some(r => getRowKey(r) === getRowKey(row))
}

const getRowKey = (row) => {
  return row.id || JSON.stringify(row)
}

const executeRowAction = (action, row) => {
  if (action.confirm) {
    if (!confirm(action.confirm_text)) return
  }

  if (action.url && action.method === 'get') {
    if (action.open_in_new_tab) {
      window.open(action.url, '_blank')
    } else {
      window.location.href = action.url
    }
  } else {
    emit('row-action', { action: action.name, row })
  }
}

const executeBulkAction = (event) => {
  const actionName = event.target.value
  if (!actionName) return

  const action = props.bulkActions.find(a => a.name === actionName)
  if (action && selectedRows.value.length > 0) {
    if (action.confirm) {
      if (!confirm(action.confirm_text)) return
    }
    emit('bulk-action', { action: action.name, rows: selectedRows.value })
  }

  // Reset selection
  event.target.value = ''
}

const switchView = (view) => {
  currentView.value = view
  emit('view-change', view)
}

const saveCurrentView = () => {
  const viewName = prompt('Enter view name:')
  if (viewName) {
    // Save view logic
    console.log('Saving view:', viewName)
  }
}

const exportData = (format) => {
  emit('export', { format, filters: filterValues.value, search: searchQuery.value })
}

const handleRowClick = (row) => {
  if (props.config.row_links) {
    emit('row-click', row)
  }
}

const resetFilters = () => {
  filterValues.value = {}
  searchQuery.value = ''
  emit('filter', {})
  emit('search', '')
}

// Watchers
watch(filterValues, (newFilters) => {
  emit('filter', newFilters)
}, { deep: true })

watch(searchQuery, debounce((newSearch) => {
  emit('search', newSearch)
}, 300))

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
/* Enhanced styles for all InertiaUI features */
.sticky-header thead {
  position: sticky;
  top: 0;
  background: white;
  z-index: 10;
}

.sticky-column {
  position: sticky;
  left: 0;
  background: white;
  z-index: 5;
}

.bulk-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.5rem;
  background: #f8f9fa;
  border: 1px solid #dee2e6;
}

.table-views {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.empty-state {
  text-align: center;
  padding: 2rem;
}

.empty-content {
  max-width: 300px;
  margin: 0 auto;
}

.row-clickable {
  cursor: pointer;
}

.row-clickable:hover {
  background: #f8f9fa;
}

.actions-container {
  display: flex;
  gap: 0.25rem;
}

.action-btn {
  padding: 0.25rem 0.5rem;
  border: 1px solid #ddd;
  background: white;
  border-radius: 3px;
  cursor: pointer;
  font-size: 0.8rem;
}

.action-btn:hover {
  background: #f8f9fa;
}

.export-options {
  display: flex;
  gap: 0.5rem;
}

.export-btn {
  padding: 0.5rem 1rem;
  border: 1px solid #007bff;
  background: white;
  color: #007bff;
  border-radius: 4px;
  cursor: pointer;
}

.export-btn:hover {
  background: #007bff;
  color: white;
}

.multiple-tables {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}
</style>