<!-- resources/js/Components/Tasks/KanbanBoard.vue -->

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
                    <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: column.color }"></span>
                    {{ column.title }}
                    <span class="ml-1 text-xs opacity-75">{{ getTasksByStatus(column.status).length }}</span>
                </button>
            </div>

            <div class="space-y-3">
                <TaskCard
                    v-for="task in getTasksByStatus(activeMobileColumn)"
                    :key="task.id"
                    :task="task"
                    :show-checkbox="false"
                    @status-change="handleStatusChange"
                    @click="openTask(task.id)"
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
                            <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: column.color }"></span>
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ column.title }}</h3>
                        </div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ getTasksByStatus(column.status).length }}</span>
                    </div>
                </div>

                <div class="p-2 space-y-2 max-h-[calc(100vh-280px)] overflow-y-auto">
                    <TaskCard
                        v-for="task in getTasksByStatus(column.status)"
                        :key="task.id"
                        :task="task"
                        :show-checkbox="false"
                        @status-change="handleStatusChange"
                        @click="openTask(task.id)"
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
import TaskCard from './TaskCard.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    tasks: {
        type: Array,
        required: true,
        default: () => []
    }
})

const emit = defineEmits(['status-change'])

const activeMobileColumn = ref('todo')

const columns = [
    { status: 'backlog', title: 'Бэклог', color: '#6B7280' },
    { status: 'todo', title: 'К выполнению', color: '#3B82F6' },
    { status: 'in_progress', title: 'В работе', color: '#F59E0B' },
    { status: 'in_review', title: 'На проверке', color: '#8B5CF6' },
    { status: 'completed', title: 'Выполнено', color: '#10B981' },
    { status: 'cancelled', title: 'Отменено', color: '#EF4444' }
]

const getTasksByStatus = (status) => {
    if (!props.tasks || !Array.isArray(props.tasks)) return []
    return props.tasks.filter(task => task.status === status)
}

const handleStatusChange = (taskId, newStatus) => {
    emit('status-change', taskId, newStatus)
}

const openTask = (id) => {
    if (id) {
        router.get(route('tasks.show', id))
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
