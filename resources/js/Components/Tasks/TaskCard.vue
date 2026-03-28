<!-- resources/js/Components/Tasks/TaskCard.vue -->

<template>
    <div
        class="task-card bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm hover:shadow-md transition-all cursor-pointer border-l-4"
        :class="borderColorClass"
        @click="$emit('click')"
    >
        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
                <h4 class="font-medium text-gray-900 dark:text-white text-sm truncate">
                    {{ task.title }}
                </h4>
                <!-- Кнопка редактирования - только для создателя -->
                <button
                    v-if="canEdit"
                    @click.stop="$emit('edit', task.id)"
                    class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                    title="Редактировать задачу"
                >
                    <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>
            </div>

            <p v-if="task.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                {{ task.description }}
            </p>
        </div>

        <!-- Теги и статусы -->
        <div class="flex flex-wrap items-center gap-2 mt-3">
            <!-- Приоритет -->
            <span :class="priorityClass" class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full">
        {{ priorityName }}
      </span>

            <!-- Статус с цветным индикатором -->
            <span :class="statusBadgeClass" class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full">
        <span class="w-2 h-2 rounded-full" :class="statusDotClass"></span>
        {{ statusName }}
      </span>

            <!-- Тип задачи -->
            <span v-if="task.type !== 'task'" :class="typeClass" class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full">
        {{ typeName }}
      </span>

            <!-- Видимость -->
            <span v-if="task.visibility !== 'personal'" :class="visibilityClass" class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full">
        {{ visibilityName }}
      </span>

            <!-- Теги -->
            <span
                v-for="tag in task.tags"
                :key="tag.id"
                class="px-2 py-0.5 text-xs rounded-full"
                :style="{ backgroundColor: tag.color + '20', color: tag.color }"
            >
        {{ tag.name }}
      </span>
        </div>

        <!-- Информация: создатель и ответственный -->
        <div class="flex flex-col gap-1 mt-3 text-xs">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-3 h-3 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-gray-500 dark:text-gray-400">Создатель:</span>
                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ task.creator_short || task.creator?.short_name || task.creator_name || '—' }}</span>
                </div>

                <div v-if="task.due_date" class="flex items-center gap-1" :class="isOverdue ? 'text-red-500' : 'text-gray-500 dark:text-gray-400'">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>{{ formatDate(task.due_date) }}</span>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-3 h-3 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span class="text-gray-500 dark:text-gray-400">Ответственный:</span>
                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ task.assignee_short || task.assignee?.short_name || task.assignee_name || 'Не назначен' }}</span>
                </div>

                <div class="flex items-center gap-2">
                    <div v-if="task.subtasks?.length" class="flex items-center gap-1 text-gray-500 dark:text-gray-400">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span>{{ getCompletedSubtasksCount() }}/{{ task.subtasks.length }}</span>
                    </div>

                    <div v-if="task.comments?.length" class="flex items-center gap-1 text-gray-500 dark:text-gray-400">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <span>{{ task.comments.length }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
    task: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['click', 'edit'])

const page = usePage()
const currentUser = computed(() => page.props.auth?.user)

// Исправленная проверка - создатель может быть как объектом, так и просто id
const canEdit = computed(() => {
    // Получаем ID создателя из разных форматов
    const creatorId = props.task.created_by?.id || props.task.created_by
    return currentUser.value?.id === creatorId
})

// Приоритет
const priorityName = computed(() => {
    const map = { low: 'Низкий', medium: 'Средний', high: 'Высокий', urgent: 'Срочный', critical: 'Критический' }
    return map[props.task.priority] || props.task.priority
})

const priorityClass = computed(() => {
    const map = {
        low: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        medium: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        high: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
        urgent: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        critical: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300'
    }
    return map[props.task.priority] || ''
})

const borderColorClass = computed(() => {
    const map = {
        low: 'border-gray-300 dark:border-gray-600',
        medium: 'border-blue-400 dark:border-blue-500',
        high: 'border-orange-400 dark:border-orange-500',
        urgent: 'border-red-500 dark:border-red-600',
        critical: 'border-purple-500 dark:border-purple-600'
    }
    return map[props.task.priority] || 'border-gray-300 dark:border-gray-600'
})

// Статус
const statusName = computed(() => {
    const map = {
        backlog: 'Бэклог',
        todo: 'К выполнению',
        in_progress: 'В работе',
        in_review: 'На проверке',
        completed: 'Выполнено',
        cancelled: 'Отменено'
    }
    return map[props.task.status] || props.task.status
})

const statusBadgeClass = computed(() => {
    const map = {
        backlog: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        todo: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        in_progress: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        in_review: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
        completed: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
    }
    return map[props.task.status] || ''
})

const statusDotClass = computed(() => {
    const map = {
        backlog: 'bg-gray-500',
        todo: 'bg-blue-500',
        in_progress: 'bg-yellow-500',
        in_review: 'bg-purple-500',
        completed: 'bg-green-500',
        cancelled: 'bg-red-500'
    }
    return map[props.task.status] || 'bg-gray-500'
})

// Тип задачи
const typeName = computed(() => {
    const map = {
        task: 'Задача',
        urgent: 'Срочная',
        reminder: 'Напоминание',
        idea: 'Идея',
        bug: 'Ошибка',
        feature: 'Новая функция'
    }
    return map[props.task.type] || props.task.type
})

const typeClass = computed(() => {
    const map = {
        urgent: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        reminder: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
        idea: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        bug: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        feature: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
    }
    return map[props.task.type] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
})

// Видимость
const visibilityName = computed(() => {
    const map = {
        personal: 'Личная',
        department: 'Отдел',
        company: 'Компания',
        project: 'Проект'
    }
    return map[props.task.visibility] || props.task.visibility
})

const visibilityClass = computed(() => {
    const map = {
        department: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        company: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        project: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300'
    }
    return map[props.task.visibility] || ''
})

const isOverdue = computed(() => {
    if (!props.task.due_date) return false
    return new Date(props.task.due_date) < new Date() &&
        props.task.status !== 'completed' &&
        props.task.status !== 'cancelled'
})

const formatDate = (date) => {
    if (!date) return ''
    const d = new Date(date)
    return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
}

const getCompletedSubtasksCount = () => {
    if (!props.task.subtasks) return 0
    return props.task.subtasks.filter(s => s.is_completed).length
}
</script>

<style scoped>
.task-card {
    transition: all 0.2s ease;
}

.task-card:active {
    transform: scale(0.98);
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
