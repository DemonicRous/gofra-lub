<template>
    <div
        class="todo-card bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm hover:shadow-md transition-all cursor-pointer border-l-4"
        :class="borderColorClass"
        @click="$emit('click')"
    >
        <!-- Заголовок и меню -->
        <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0">
                <h4 class="font-medium text-gray-900 dark:text-white text-sm truncate">
                    {{ todo.title }}
                </h4>
                <p v-if="todo.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                    {{ todo.description }}
                </p>
            </div>
            <button
                @click.stop="openStatusMenu"
                class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 transition"
            >
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                </svg>
            </button>
        </div>

        <!-- Теги -->
        <div class="flex flex-wrap items-center gap-2 mt-3">
            <!-- Приоритет -->
            <span :class="priorityClass" class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path v-if="todo.priority === 'low'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    <path v-if="todo.priority === 'medium'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7M5 11l7-7 7 7" />
                    <path v-if="todo.priority === 'high'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7M5 11l7-7 7 7M5 7l7-7 7 7" />
                    <path v-if="todo.priority === 'urgent'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                {{ priorityName }}
            </span>

            <!-- Тип задачи -->
            <span v-if="todo.type === 'urgent'" class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Срочная
            </span>

            <span v-if="todo.type === 'reminder'" class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Напоминание
            </span>

            <span v-if="todo.type === 'idea'" class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                Идея
            </span>

            <!-- Видимость -->
            <span v-if="todo.visibility === 'department'" class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Отдел
            </span>

            <span v-if="todo.visibility === 'company'" class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Компания
            </span>
        </div>

        <!-- Информация -->
        <div class="flex items-center justify-between mt-3 text-xs text-gray-500 dark:text-gray-400">
            <div class="flex items-center gap-3">
                <div v-if="todo.assignee" class="flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>{{ todo.assignee?.short_name || todo.assignee?.full_name || 'Неизвестный' }}</span>
                </div>
                <div v-if="todo.due_date" class="flex items-center gap-1" :class="isOverdue ? 'text-red-500' : ''">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>{{ formatDate(todo.due_date) }}</span>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <svg v-if="todo.subtasks?.length" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>{{ getCompletedSubtasksCount() }}/{{ todo.subtasks?.length || 0 }}</span>
            </div>
        </div>

        <!-- Меню статусов -->
        <div v-if="showStatusMenu" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="closeStatusMenu">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-4 min-w-[220px]">
                <h4 class="font-medium mb-3 text-gray-900 dark:text-white">Изменить статус</h4>
                <div class="space-y-1">
                    <button
                        v-for="status in statuses"
                        :key="status.value"
                        @click="changeStatus(status.value)"
                        class="w-full text-left px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition text-sm text-gray-700 dark:text-gray-300 flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path :d="status.icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                        </svg>
                        {{ status.label }}
                    </button>
                </div>
                <button @click="closeStatusMenu" class="mt-3 w-full text-center text-sm text-gray-500 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">Отмена</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    todo: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['status-change', 'click'])

const showStatusMenu = ref(false)

const statuses = [
    { value: 'backlog', label: 'Бэклог', icon: 'M5 8h14M5 12h14M5 16h14' },
    { value: 'pending', label: 'Ожидает', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    { value: 'in_progress', label: 'В работе', icon: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15' },
    { value: 'review', label: 'На проверке', icon: 'M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' },
    { value: 'completed', label: 'Выполнено', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { value: 'cancelled', label: 'Отменено', icon: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' }
]

const priorityName = computed(() => {
    const map = { low: 'Низкий', medium: 'Средний', high: 'Высокий', urgent: 'Срочный' }
    return map[props.todo.priority] || props.todo.priority
})

const priorityClass = computed(() => {
    const map = {
        low: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        medium: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        high: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
        urgent: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
    }
    return map[props.todo.priority] || ''
})

const borderColorClass = computed(() => {
    const map = {
        low: 'border-gray-300 dark:border-gray-600',
        medium: 'border-blue-400 dark:border-blue-500',
        high: 'border-orange-400 dark:border-orange-500',
        urgent: 'border-red-500 dark:border-red-600'
    }
    return map[props.todo.priority] || 'border-gray-300'
})

const isOverdue = computed(() => {
    if (!props.todo.due_date) return false
    return new Date(props.todo.due_date) < new Date() && props.todo.status !== 'completed' && props.todo.status !== 'cancelled'
})

const formatDate = (date) => {
    if (!date) return ''
    const d = new Date(date)
    return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
}

const getCompletedSubtasksCount = () => {
    if (!props.todo.subtasks) return 0
    return props.todo.subtasks.filter(s => s.is_completed).length
}

const openStatusMenu = () => {
    showStatusMenu.value = true
}

const closeStatusMenu = () => {
    showStatusMenu.value = false
}

const changeStatus = (status) => {
    emit('status-change', props.todo.id, status)
    closeStatusMenu()
}
</script>

<style scoped>
.todo-card {
    transition: all 0.2s ease;
}

.todo-card:active {
    transform: scale(0.98);
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
