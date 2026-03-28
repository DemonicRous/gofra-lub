<!-- resources/js/Pages/Tasks/Index.vue -->

<template>
    <AppLayout>
        <Head title="Задачи" />

        <div class="container mx-auto px-4 py-8">
            <!-- Заголовок и действия -->
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Задачи</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Управление задачами и проектами
                    </p>
                </div>

                <div class="flex gap-2">
                    <!-- Кнопка экспорта с выпадающим меню -->
                    <div class="relative">
                        <button
                            @click="showExportMenu = !showExportMenu"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Экспорт
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Выпадающее меню экспорта -->
                        <div v-if="showExportMenu" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-10">
                            <button
                                @click="exportExcel"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition flex items-center gap-2"
                            >
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Excel (.xlsx)
                            </button>
                            <button
                                @click="exportPdf"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition flex items-center gap-2"
                            >
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                PDF (.pdf)
                            </button>
                        </div>
                    </div>

                    <button
                        @click="openCreateModal"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition shadow-md flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Создать задачу
                    </button>
                </div>
            </div>

            <!-- Статистика -->
            <TaskStats :stats="stats" class="mb-6" />

            <!-- Фильтры и поиск -->
            <TaskFilters :filters="filters" />

            <!-- Переключатель вида -->
            <div class="flex justify-between items-center mb-4">
                <div class="flex gap-2">
                    <button
                        v-for="view in views"
                        :key="view.value"
                        @click="viewMode = view.value"
                        :class="[
              'p-2 rounded-lg transition',
              viewMode === view.value
                ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
                : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800'
            ]"
                        :title="view.label"
                    >
                        <svg v-if="view.value === 'list'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        <svg v-else-if="view.value === 'board'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Список задач -->
            <div v-if="viewMode === 'list'" class="space-y-3">
                <div v-if="tasks.data && tasks.data.length > 0">
                    <TaskCard
                        v-for="task in tasks.data"
                        :key="task.id"
                        :task="task"
                        @click="openTask(task.id)"
                        @edit="openEditModalFromCard"
                        class="my-2"
                    />
                </div>

                <div v-else class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-lg">Нет задач для отображения</p>
                    <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">Попробуйте изменить фильтры или создайте новую задачу</p>
                </div>

                <div v-if="tasks.links && tasks.links.length > 0" class="mt-6">
                    <Pagination :links="tasks.links" />
                </div>
            </div>

            <!-- Доска канбан -->
            <KanbanBoard
                v-else-if="viewMode === 'board'"
                :tasks="tasks.data || []"
                @status-change="handleStatusChange"
            />

            <!-- Календарь -->
            <CalendarView
                v-else
                :tasks="tasks.data || []"
            />
        </div>

        <!-- Модальное окно создания задачи -->
        <CreateTaskModal
            :show="showCreateModal"
            :users="users"
            :tags="tags"
            :projects="projects"
            @close="closeModal"
            @created="refreshTasks"
        />

        <!-- Модальное окно редактирования задачи -->
        <div v-if="showEditModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50 p-4" @click.self="closeEditModal">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 px-6 py-4 sticky top-0">
                    <h2 class="text-xl font-bold text-white">Редактирование задачи</h2>
                </div>

                <form @submit.prevent="updateTask" class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Название <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="editForm.title"
                                type="text"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                :class="editForm.errors.title ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                            />
                            <p v-if="editForm.errors.title" class="text-red-500 text-xs mt-1">{{ editForm.errors.title }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Описание
                            </label>
                            <textarea
                                v-model="editForm.description"
                                rows="3"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                :class="editForm.errors.description ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                            ></textarea>
                            <p v-if="editForm.errors.description" class="text-red-500 text-xs mt-1">{{ editForm.errors.description }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Приоритет
                                </label>
                                <select
                                    v-model="editForm.priority"
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                >
                                    <option value="low">Низкий</option>
                                    <option value="medium">Средний</option>
                                    <option value="high">Высокий</option>
                                    <option value="urgent">Срочный</option>
                                    <option value="critical">Критический</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Статус
                                </label>
                                <select
                                    v-model="editForm.status"
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                >
                                    <option value="backlog">Бэклог</option>
                                    <option value="todo">К выполнению</option>
                                    <option value="in_progress">В работе</option>
                                    <option value="in_review">На проверке</option>
                                    <option value="completed">Выполнена</option>
                                    <option value="cancelled">Отменена</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Срок выполнения
                            </label>
                            <input
                                v-model="editForm.due_date"
                                type="datetime-local"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            />
                        </div>

                        <div v-if="taskToEdit && taskToEdit.visibility !== 'personal'">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Ответственный
                            </label>
                            <select
                                v-model="editForm.assigned_to"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            >
                                <option value="">Не назначен</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.short_name || user.full_name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t dark:border-gray-700">
                        <button
                            type="button"
                            @click="closeEditModal"
                            class="px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 rounded-md transition"
                        >
                            Отмена
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition disabled:opacity-50"
                            :disabled="editForm.processing"
                        >
                            {{ editForm.processing ? 'Сохранение...' : 'Сохранить' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import TaskStats from '@/Components/Tasks/TaskStats.vue'
import TaskFilters from '@/Components/Tasks/TaskFilters.vue'
import TaskCard from '@/Components/Tasks/TaskCard.vue'
import KanbanBoard from '@/Components/Tasks/KanbanBoard.vue'
import CalendarView from '@/Components/Tasks/CalendarView.vue'
import CreateTaskModal from '@/Components/Tasks/CreateTaskModal.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
    tasks: {
        type: Object,
        default: () => ({ data: [], links: [] })
    },
    stats: {
        type: Object,
        default: () => ({})
    },
    tags: {
        type: Array,
        default: () => []
    },
    projects: {
        type: Array,
        default: () => []
    },
    users: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

const page = usePage()
const currentUser = computed(() => page.props.auth?.user)

const viewMode = ref('list')
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showExportMenu = ref(false)
const taskToEdit = ref(null)

const editForm = useForm({
    title: '',
    description: '',
    priority: '',
    status: '',
    due_date: '',
    assigned_to: ''
})

const views = [
    { value: 'list', label: 'Список' },
    { value: 'board', label: 'Доска' },
    { value: 'calendar', label: 'Календарь' }
]

// Применение фильтров
const applyFilters = (newFilters) => {
    router.get(route('tasks.index'), newFilters, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })
}

// Изменение статуса задачи
const handleStatusChange = (taskId, status) => {
    router.put(route('tasks.update', taskId), { status }, {
        preserveScroll: true
    })
}

// Экспорт в Excel
const exportExcel = () => {
    const params = new URLSearchParams()
    if (props.filters.status && props.filters.status !== 'all') params.append('status', props.filters.status)
    if (props.filters.priority && props.filters.priority !== 'all') params.append('priority', props.filters.priority)
    if (props.filters.type && props.filters.type !== 'all') params.append('type', props.filters.type)
    if (props.filters.visibility && props.filters.visibility !== 'all') params.append('visibility', props.filters.visibility)
    if (props.filters.search) params.append('search', props.filters.search)

    window.location.href = route('tasks.export.excel') + '?' + params.toString()
    showExportMenu.value = false
}

// Экспорт в PDF
const exportPdf = () => {
    const params = new URLSearchParams()
    if (props.filters.status && props.filters.status !== 'all') params.append('status', props.filters.status)
    if (props.filters.priority && props.filters.priority !== 'all') params.append('priority', props.filters.priority)
    if (props.filters.type && props.filters.type !== 'all') params.append('type', props.filters.type)
    if (props.filters.visibility && props.filters.visibility !== 'all') params.append('visibility', props.filters.visibility)
    if (props.filters.search) params.append('search', props.filters.search)

    window.location.href = route('tasks.export.pdf') + '?' + params.toString()
    showExportMenu.value = false
}

// Открыть задачу для просмотра
const openTask = (id) => {
    router.get(route('tasks.show', id))
}

// Открыть модальное окно создания задачи
const openCreateModal = () => {
    showCreateModal.value = true
}

// Закрыть модальное окно создания
const closeModal = () => {
    showCreateModal.value = false
}

// Обновить список задач
const refreshTasks = () => {
    router.reload({ only: ['tasks', 'stats'] })
}

// Редактирование из карточки
const openEditModalFromCard = (taskId) => {
    const task = props.tasks.data.find(t => t.id === taskId)
    if (task) {
        taskToEdit.value = task
        editForm.title = task.title
        editForm.description = task.description || ''
        editForm.priority = task.priority
        editForm.status = task.status
        editForm.due_date = task.due_date ? new Date(task.due_date).toISOString().slice(0, 16) : ''
        editForm.assigned_to = task.assignee?.id || ''
        editForm.clearErrors()
        showEditModal.value = true
    }
}

// Закрыть модальное окно редактирования
const closeEditModal = () => {
    showEditModal.value = false
    taskToEdit.value = null
    editForm.reset()
}

// Обновить задачу
const updateTask = () => {
    if (!taskToEdit.value) return

    editForm.put(route('tasks.update', taskToEdit.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal()
            refreshTasks()
        }
    })
}

// Закрытие меню при клике вне
const handleClickOutside = (event) => {
    if (!event.target.closest('.relative')) {
        showExportMenu.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>
