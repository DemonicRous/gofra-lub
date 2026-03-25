<template>
    <div class="w-full">
        <!-- Мобильная версия -->
        <div class="lg:hidden">
            <div class="flex gap-2 overflow-x-auto pb-4 mb-4">
                <button
                    v-for="column in columns"
                    :key="column.status"
                    @click="activeMobileColumn = column.status"
                    :class="[
                        'flex items-center gap-2 px-4 py-2 rounded-lg whitespace-nowrap transition',
                        activeMobileColumn === column.status
                            ? 'bg-blue-600 text-white'
                            : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path :d="column.icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                    </svg>
                    {{ column.title }}
                    <span class="ml-1 text-xs opacity-75">{{ getTasksByStatus(column.status).length }}</span>
                </button>
            </div>

            <div class="space-y-3">
                <TodoCard
                    v-for="todo in getTasksByStatus(activeMobileColumn)"
                    :key="todo.id"
                    :todo="todo"
                    @status-change="handleStatusChange"
                    @click="openTodo(todo.id)"
                />
                <div v-if="getTasksByStatus(activeMobileColumn).length === 0" class="text-center py-8 text-gray-500">
                    Нет задач в этой колонке
                </div>
            </div>
        </div>

        <!-- Десктопная версия -->
        <div class="hidden lg:flex lg:gap-4 overflow-x-auto pb-4">
            <div
                v-for="column in columns"
                :key="column.status"
                class="flex-shrink-0 w-80 bg-gray-50 dark:bg-gray-800/50 rounded-lg"
            >
                <div class="p-3 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path :d="column.icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                            </svg>
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ column.title }}</h3>
                        </div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ getTasksByStatus(column.status).length }}</span>
                    </div>
                </div>

                <div class="p-2 space-y-2 max-h-[calc(100vh-280px)] overflow-y-auto">
                    <TodoCard
                        v-for="todo in getTasksByStatus(column.status)"
                        :key="todo.id"
                        :todo="todo"
                        @status-change="handleStatusChange"
                        @click="openTodo(todo.id)"
                    />
                    <div v-if="getTasksByStatus(column.status).length === 0" class="text-center py-8 text-gray-400 text-sm">
                        Нет задач
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import TodoCard from './TodoCard.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    todos: {
        type: Array,
        required: true,
        default: () => []
    }
})

const emit = defineEmits(['status-change'])

const activeMobileColumn = ref('pending')

const columns = [
    { status: 'backlog', title: 'Бэклог', icon: 'M5 8h14M5 12h14M5 16h14' },
    { status: 'pending', title: 'Ожидает', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    { status: 'in_progress', title: 'В работе', icon: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15' },
    { status: 'review', title: 'На проверке', icon: 'M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' },
    { status: 'completed', title: 'Выполнено', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { status: 'cancelled', title: 'Отменено', icon: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' }
]

const getTasksByStatus = (status) => {
    if (!props.todos || !Array.isArray(props.todos)) return []
    return props.todos.filter(todo => todo.status === status)
}

const handleStatusChange = (todoId, newStatus) => {
    emit('status-change', { id: todoId, status: newStatus })
}

const openTodo = (id) => {
    if (id) {
        router.get(route('todos.show', id))
    }
}
</script>

<style scoped>
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.dark .overflow-y-auto::-webkit-scrollbar-track {
    background: #1f2937;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb {
    background: #4b5563;
}
</style>
