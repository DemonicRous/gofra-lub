<template>
    <AppLayout>
        <Head title="Задачи" />

        <div class="container mx-auto px-4 py-8">
            <!-- Заголовок -->
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">Задачи</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Управление личными и командными задачами</p>
                </div>
                <button
                    @click="openCreateModal"
                    class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-4 py-2 rounded-lg transition shadow-md flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Создать задачу
                </button>
            </div>

            <!-- Вкладки -->
            <div class="border-b border-gray-200 dark:border-gray-700 mb-6 overflow-x-auto">
                <div class="flex gap-4 min-w-max">
                    <button
                        v-for="tab in tabs"
                        :key="tab.value"
                        @click="activeTab = tab.value"
                        :class="[
                            'px-4 py-2 text-sm font-medium transition relative whitespace-nowrap',
                            activeTab === tab.value
                                ? 'text-blue-600 dark:text-blue-400'
                                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                        ]"
                    >
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path :d="tab.icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                            </svg>
                            {{ tab.label }}
                        </span>
                        <span v-if="tab.count" class="ml-1 text-xs bg-gray-200 dark:bg-gray-700 px-1.5 py-0.5 rounded-full">
                            {{ tab.count }}
                        </span>
                        <div
                            v-if="activeTab === tab.value"
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 dark:bg-blue-400"
                        ></div>
                    </button>
                </div>
            </div>

            <!-- Переключатель видов -->
            <div class="flex justify-end gap-2 mb-4">
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
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path :d="view.icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                    </svg>
                </button>
            </div>

            <!-- Контент -->
            <div class="overflow-x-auto">
                <KanbanBoard
                    v-if="viewMode === 'board'"
                    :todos="filteredTodos"
                    @status-change="handleStatusChange"
                />

                <div v-else-if="viewMode === 'list'" class="space-y-3">
                    <TodoCard
                        v-for="todo in filteredTodos"
                        :key="todo.id"
                        :todo="todo"
                        @status-change="handleStatusChange"
                        @click="openTodo(todo.id)"
                    />
                    <div v-if="filteredTodos.length === 0" class="text-center py-8 text-gray-500">
                        Нет задач для отображения
                    </div>
                    <div v-if="todos.links?.length" class="mt-6">
                        <Pagination :links="todos.links" />
                    </div>
                </div>

                <CalendarView
                    v-else-if="viewMode === 'calendar'"
                    :todos="filteredTodos"
                />
            </div>
        </div>

        <CreateTodoModal
            :show="showCreateModal"
            :users="users"
            :departments="departments"
            @close="closeModal"
            @created="refreshTodos"
        />
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import KanbanBoard from '@/Components/Todo/KanbanBoard.vue'
import TodoCard from '@/Components/Todo/TodoCard.vue'
import CalendarView from '@/Components/Todo/CalendarView.vue'
import CreateTodoModal from '@/Components/Todo/CreateTodoModal.vue'

const props = defineProps({
    todos: {
        type: Object,
        default: () => ({ data: [], links: [] })
    },
    stats: {
        type: Object,
        default: () => ({})
    },
    users: {
        type: Array,
        default: () => []
    },
    departments: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

const activeTab = ref('personal')
const viewMode = ref('board')
const showCreateModal = ref(false)

const tabs = computed(() => [
    { value: 'personal', label: 'Личные', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', count: props.stats?.personal_count },
    { value: 'department', label: 'Мой отдел', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', count: props.stats?.department_count },
    { value: 'company', label: 'Компания', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', count: props.stats?.company_count },
    { value: 'assigned', label: 'Назначены мне', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', count: props.stats?.assigned_count }
])

const views = [
    { value: 'board', label: 'Доска', icon: 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z' },
    { value: 'list', label: 'Список', icon: 'M4 6h16M4 10h16M4 14h16M4 18h16' },
    { value: 'calendar', label: 'Календарь', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' }
]

const filteredTodos = computed(() => {
    if (!props.todos?.data) return []
    return props.todos.data.filter(todo => {
        if (activeTab.value === 'personal') return todo.visibility === 'personal'
        if (activeTab.value === 'department') return todo.visibility === 'department'
        if (activeTab.value === 'company') return todo.visibility === 'company'
        if (activeTab.value === 'assigned') return todo.assigned_to === props.stats?.current_user_id
        return true
    })
})

const handleStatusChange = ({ id, status }) => {
    router.put(route('todos.update', id), { status }, {
        preserveScroll: true
    })
}

const openTodo = (id) => {
    router.get(route('todos.show', id))
}

const openCreateModal = () => {
    showCreateModal.value = true
}

const closeModal = () => {
    showCreateModal.value = false
}

const refreshTodos = () => {
    router.reload({ only: ['todos', 'stats'] })
}
</script>
