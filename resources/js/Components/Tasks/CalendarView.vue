<!-- resources/js/Components/Tasks/CalendarView.vue -->

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex gap-2">
                    <button
                        v-for="mode in modes"
                        :key="mode.value"
                        @click="currentMode = mode.value"
                        :class="[
              'px-3 py-1.5 rounded-lg text-sm transition',
              currentMode === mode.value
                ? 'bg-blue-600 text-white'
                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
            ]"
                    >
                        {{ mode.label }}
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <button @click="previousPeriod" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ currentTitle }}</h3>
                    <button @click="nextPeriod" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="p-4">
            <div v-if="tasks.length === 0" class="text-center py-12 text-gray-500">
                Нет задач для отображения в календаре
            </div>

            <div v-else-if="currentMode === 'month'" class="calendar-month">
                <div class="grid grid-cols-7 gap-1 mb-2">
                    <div v-for="day in weekDays" :key="day" class="text-center text-sm font-medium text-gray-500 py-2">
                        {{ day }}
                    </div>
                </div>
                <div class="grid grid-cols-7 gap-1">
                    <div
                        v-for="day in monthDays"
                        :key="day.date"
                        class="min-h-[100px] p-2 border border-gray-200 dark:border-gray-700 rounded-lg"
                        :class="{
              'bg-blue-50 dark:bg-blue-900/20': isToday(day.date),
              'bg-gray-50 dark:bg-gray-800/50': day.isCurrentMonth
            }"
                    >
                        <div class="text-right text-sm text-gray-500 mb-1">{{ day.day }}</div>
                        <div class="space-y-1">
                            <div
                                v-for="task in getTasksForDate(day.date)"
                                :key="task.id"
                                class="text-xs p-1 rounded cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700 truncate"
                                :class="getPriorityBgClass(task.priority)"
                                @click.stop="openTask(task.id)"
                            >
                                {{ task.title }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="space-y-4">
                <div
                    v-for="task in sortedTasks"
                    :key="task.id"
                    class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer"
                    @click="openTask(task.id)"
                >
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
              <span :class="getPriorityClass(task.priority)" class="px-2 py-0.5 text-xs rounded-full">
                {{ getPriorityName(task.priority) }}
              </span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ task.title }}</span>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ formatDateTime(task.due_date) }}
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    tasks: {
        type: Array,
        default: () => []
    }
})

const currentMode = ref('month')
const currentDate = ref(new Date())

const modes = [
    { value: 'month', label: 'Месяц' },
    { value: 'list', label: 'Список' }
]

const weekDays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']

const currentTitle = computed(() => {
    if (currentMode.value === 'month') {
        return currentDate.value.toLocaleDateString('ru-RU', { month: 'long', year: 'numeric' })
    }
    return 'Задачи по датам'
})

const monthDays = computed(() => {
    const year = currentDate.value.getFullYear()
    const month = currentDate.value.getMonth()
    const firstDay = new Date(year, month, 1)
    const startDate = new Date(firstDay)
    startDate.setDate(firstDay.getDate() - (firstDay.getDay() || 7) + 1)

    const days = []
    for (let i = 0; i < 42; i++) {
        const date = new Date(startDate)
        date.setDate(startDate.getDate() + i)
        days.push({
            date: date,
            day: date.getDate(),
            isCurrentMonth: date.getMonth() === month
        })
    }
    return days
})

const sortedTasks = computed(() => {
    return [...props.tasks]
        .filter(t => t.due_date)
        .sort((a, b) => new Date(a.due_date) - new Date(b.due_date))
})

const isToday = (date) => {
    const today = new Date()
    return date.toDateString() === today.toDateString()
}

const getTasksForDate = (date) => {
    if (!props.tasks?.length) return []
    const dateStr = date.toDateString()
    return props.tasks.filter(task => {
        if (!task.due_date) return false
        const taskDate = new Date(task.due_date)
        return taskDate.toDateString() === dateStr
    })
}

const getPriorityName = (priority) => {
    const map = { low: 'Низкий', medium: 'Средний', high: 'Высокий', urgent: 'Срочный', critical: 'Критический' }
    return map[priority] || priority
}

const getPriorityClass = (priority) => {
    const map = {
        low: 'bg-gray-100 text-gray-800',
        medium: 'bg-blue-100 text-blue-800',
        high: 'bg-orange-100 text-orange-800',
        urgent: 'bg-red-100 text-red-800',
        critical: 'bg-purple-100 text-purple-800'
    }
    return map[priority] || ''
}

const getPriorityBgClass = (priority) => {
    const map = {
        low: 'bg-gray-100 text-gray-800',
        medium: 'bg-blue-100 text-blue-800',
        high: 'bg-orange-100 text-orange-800',
        urgent: 'bg-red-100 text-red-800',
        critical: 'bg-purple-100 text-purple-800'
    }
    return map[priority] || ''
}

const formatDateTime = (date) => {
    if (!date) return ''
    const d = new Date(date)
    return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}

const previousPeriod = () => {
    if (currentMode.value === 'month') {
        currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1)
    }
}

const nextPeriod = () => {
    if (currentMode.value === 'month') {
        currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1)
    }
}

const openTask = (id) => {
    if (id) {
        router.get(route('tasks.show', id))
    }
}
</script>
