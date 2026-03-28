<!-- resources/js/Components/Tasks/TaskFilters.vue -->

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
        <div class="flex flex-wrap gap-4">
            <!-- Поиск -->
            <div class="flex-1 min-w-[200px]">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input
                        :value="searchValue"
                        @input="handleSearch"
                        type="text"
                        placeholder="Поиск по названию..."
                        class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    />
                </div>
            </div>

            <!-- Статус -->
            <select
                :value="statusValue"
                @change="handleStatusChange"
                class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            >
                <option value="all">Все статусы</option>
                <option value="backlog">Бэклог</option>
                <option value="todo">К выполнению</option>
                <option value="in_progress">В работе</option>
                <option value="in_review">На проверке</option>
                <option value="completed">Выполнено</option>
                <option value="cancelled">Отменено</option>
            </select>

            <!-- Приоритет -->
            <select
                :value="priorityValue"
                @change="handlePriorityChange"
                class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            >
                <option value="all">Все приоритеты</option>
                <option value="low">Низкий</option>
                <option value="medium">Средний</option>
                <option value="high">Высокий</option>
                <option value="urgent">Срочный</option>
                <option value="critical">Критический</option>
            </select>

            <!-- Тип -->
            <select
                :value="typeValue"
                @change="handleTypeChange"
                class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            >
                <option value="all">Все типы</option>
                <option value="task">Задача</option>
                <option value="urgent">Срочная</option>
                <option value="reminder">Напоминание</option>
                <option value="idea">Идея</option>
                <option value="bug">Ошибка</option>
                <option value="feature">Новая функция</option>
            </select>

            <!-- Видимость -->
            <select
                :value="visibilityValue"
                @change="handleVisibilityChange"
                class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            >
                <option value="all">Все задачи</option>
                <option value="personal">Личные</option>
                <option value="department">Отдел</option>
                <option value="company">Компания</option>
                <option value="project">Проект</option>
            </select>

            <!-- Сортировка -->
            <select
                :value="sortValue"
                @change="handleSortChange"
                class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            >
                <option value="priority">По приоритету</option>
                <option value="due_date">По сроку</option>
                <option value="created_at">По дате создания</option>
            </select>

            <!-- Порядок -->
            <select
                :value="orderValue"
                @change="handleOrderChange"
                class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            >
                <option value="desc">По убыванию</option>
                <option value="asc">По возрастанию</option>
            </select>

            <!-- Сброс фильтров -->
            <button
                @click="resetFilters"
                class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition flex items-center gap-2"
                title="Сбросить все фильтры"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span class="hidden sm:inline">Сбросить</span>
            </button>
        </div>

        <!-- Активные фильтры -->
        <div v-if="hasActiveFilters" class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <span class="text-sm text-gray-500 dark:text-gray-400">Активные фильтры:</span>
            <button
                v-if="statusValue !== 'all'"
                @click="clearFilter('status')"
                class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 rounded-md text-sm hover:bg-blue-200 dark:hover:bg-blue-800/50 transition"
            >
                Статус: {{ getStatusLabel(statusValue) }}
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <button
                v-if="priorityValue !== 'all'"
                @click="clearFilter('priority')"
                class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 rounded-md text-sm hover:bg-blue-200 dark:hover:bg-blue-800/50 transition"
            >
                Приоритет: {{ getPriorityLabel(priorityValue) }}
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <button
                v-if="typeValue !== 'all'"
                @click="clearFilter('type')"
                class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 rounded-md text-sm hover:bg-blue-200 dark:hover:bg-blue-800/50 transition"
            >
                Тип: {{ getTypeLabel(typeValue) }}
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <button
                v-if="visibilityValue !== 'all'"
                @click="clearFilter('visibility')"
                class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 rounded-md text-sm hover:bg-blue-200 dark:hover:bg-blue-800/50 transition"
            >
                Видимость: {{ getVisibilityLabel(visibilityValue) }}
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <button
                v-if="searchValue"
                @click="clearFilter('search')"
                class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 rounded-md text-sm hover:bg-blue-200 dark:hover:bg-blue-800/50 transition"
            >
                Поиск: {{ searchValue }}
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    filters: {
        type: Object,
        default: () => ({
            status: 'all',
            priority: 'all',
            type: 'all',
            visibility: 'all',
            search: '',
            sort: 'priority',
            order: 'desc'
        })
    }
})

// Вычисляемые значения для текущих фильтров
const statusValue = computed(() => props.filters.status || 'all')
const priorityValue = computed(() => props.filters.priority || 'all')
const typeValue = computed(() => props.filters.type || 'all')
const visibilityValue = computed(() => props.filters.visibility || 'all')
const searchValue = computed(() => props.filters.search || '')
const sortValue = computed(() => props.filters.sort || 'priority')
const orderValue = computed(() => props.filters.order || 'desc')

const hasActiveFilters = computed(() => {
    return statusValue.value !== 'all' ||
        priorityValue.value !== 'all' ||
        typeValue.value !== 'all' ||
        visibilityValue.value !== 'all' ||
        searchValue.value !== ''
})

let searchTimeout = null

// Обновление URL с новыми фильтрами
const updateFilters = (newFilters) => {
    router.get(route('tasks.index'), newFilters, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })
}

// Обработчики изменений
const handleSearch = (event) => {
    if (searchTimeout) clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        updateFilters({
            ...props.filters,
            search: event.target.value
        })
    }, 500)
}

const handleStatusChange = (event) => {
    updateFilters({
        ...props.filters,
        status: event.target.value
    })
}

const handlePriorityChange = (event) => {
    updateFilters({
        ...props.filters,
        priority: event.target.value
    })
}

const handleTypeChange = (event) => {
    updateFilters({
        ...props.filters,
        type: event.target.value
    })
}

const handleVisibilityChange = (event) => {
    updateFilters({
        ...props.filters,
        visibility: event.target.value
    })
}

const handleSortChange = (event) => {
    updateFilters({
        ...props.filters,
        sort: event.target.value
    })
}

const handleOrderChange = (event) => {
    updateFilters({
        ...props.filters,
        order: event.target.value
    })
}

// Сброс всех фильтров
const resetFilters = () => {
    updateFilters({
        status: 'all',
        priority: 'all',
        type: 'all',
        visibility: 'all',
        search: '',
        sort: 'priority',
        order: 'desc'
    })
}

// Очистка конкретного фильтра
const clearFilter = (filterName) => {
    const newFilters = { ...props.filters }

    switch (filterName) {
        case 'status':
            newFilters.status = 'all'
            break
        case 'priority':
            newFilters.priority = 'all'
            break
        case 'type':
            newFilters.type = 'all'
            break
        case 'visibility':
            newFilters.visibility = 'all'
            break
        case 'search':
            newFilters.search = ''
            break
    }

    updateFilters(newFilters)
}

// Вспомогательные функции для отображения
const getStatusLabel = (status) => {
    const labels = {
        backlog: 'Бэклог',
        todo: 'К выполнению',
        in_progress: 'В работе',
        in_review: 'На проверке',
        completed: 'Выполнено',
        cancelled: 'Отменено'
    }
    return labels[status] || status
}

const getPriorityLabel = (priority) => {
    const labels = {
        low: 'Низкий',
        medium: 'Средний',
        high: 'Высокий',
        urgent: 'Срочный',
        critical: 'Критический'
    }
    return labels[priority] || priority
}

const getTypeLabel = (type) => {
    const labels = {
        task: 'Задача',
        urgent: 'Срочная',
        reminder: 'Напоминание',
        idea: 'Идея',
        bug: 'Ошибка',
        feature: 'Новая функция'
    }
    return labels[type] || type
}

const getVisibilityLabel = (visibility) => {
    const labels = {
        personal: 'Личные',
        department: 'Отдел',
        company: 'Компания',
        project: 'Проект'
    }
    return labels[visibility] || visibility
}
</script>
