<template>
    <div class="w-full">
        <!-- Переключатель режимов -->
        <div class="flex justify-center gap-2 mb-4">
            <button
                v-for="mode in modes"
                :key="mode.value"
                @click="currentMode = mode.value"
                :class="[
                    'px-3 py-1.5 rounded-lg text-sm transition flex items-center gap-2',
                    currentMode === mode.value
                        ? 'bg-blue-600 text-white'
                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                ]"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path :d="mode.icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                </svg>
                <span class="hidden sm:inline">{{ mode.label }}</span>
            </button>
        </div>

        <!-- Календарь -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="p-3 sm:p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <button @click="previousPeriod" class="p-1.5 sm:p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <h3 class="text-sm sm:text-lg font-semibold text-gray-900 dark:text-white">{{ currentTitle }}</h3>
                    <button @click="nextPeriod" class="p-1.5 sm:p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Месячный вид -->
            <div v-if="currentMode === 'month'" class="p-2 sm:p-4 overflow-x-auto">
                <div class="min-w-[500px]">
                    <div class="grid grid-cols-7 gap-0.5 sm:gap-1 mb-1 sm:mb-2">
                        <div v-for="day in weekDays" :key="day" class="text-center text-xs sm:text-sm font-medium text-gray-500 py-1 sm:py-2">
                            {{ day }}
                        </div>
                    </div>
                    <div class="grid grid-cols-7 gap-0.5 sm:gap-1">
                        <div
                            v-for="day in monthDays"
                            :key="day.date"
                            class="min-h-[70px] sm:min-h-[100px] p-1 sm:p-2 border border-gray-200 dark:border-gray-700 rounded-lg"
                            :class="{
                                'bg-blue-50 dark:bg-blue-900/20': isToday(day.date),
                                'bg-gray-50 dark:bg-gray-800/50': day.isCurrentMonth
                            }"
                        >
                            <div class="text-right text-xs sm:text-sm text-gray-500 mb-0.5 sm:mb-1">{{ day.day }}</div>
                            <div class="space-y-0.5">
                                <div
                                    v-for="todo in getTodosForDate(day.date).slice(0, 3)"
                                    :key="todo.id"
                                    class="text-[10px] sm:text-xs p-0.5 sm:p-1 rounded cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700 truncate"
                                    :class="getPriorityClass(todo.priority)"
                                    @click.stop="openTodo(todo.id)"
                                >
                                    {{ todo.title }}
                                </div>
                                <div v-if="getTodosForDate(day.date).length > 3" class="text-[10px] text-gray-400">
                                    +{{ getTodosForDate(day.date).length - 3 }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Недельный вид -->
            <div v-else-if="currentMode === 'week'" class="p-2 sm:p-4 overflow-x-auto">
                <div class="min-w-[600px]">
                    <div class="grid grid-cols-8 border-b border-gray-200 dark:border-gray-700">
                        <div class="p-1 sm:p-2 text-center text-xs sm:text-sm font-medium text-gray-500">Время</div>
                        <div v-for="day in weekDaysShort" :key="day" class="p-1 sm:p-2 text-center text-xs sm:text-sm font-medium text-gray-500">
                            {{ day }}
                        </div>
                    </div>
                    <div v-for="hour in dayHours" :key="hour" class="grid grid-cols-8 border-b border-gray-200 dark:border-gray-700">
                        <div class="p-1 sm:p-2 text-[10px] sm:text-xs text-gray-500">{{ hour }}:00</div>
                        <div
                            v-for="dayIndex in 7"
                            :key="dayIndex"
                            class="min-h-[40px] sm:min-h-[60px] p-0.5 sm:p-1 border-l border-gray-200 dark:border-gray-700"
                        >
                            <div
                                v-for="todo in getTodosForDateTime(getWeekDate(dayIndex), hour)"
                                :key="todo.id"
                                class="text-[10px] sm:text-xs p-0.5 rounded cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700 mb-0.5 truncate"
                                :class="getPriorityClass(todo.priority)"
                                @click.stop="openTodo(todo.id)"
                            >
                                {{ todo.title }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Дневной вид -->
            <div v-else class="p-2 sm:p-4 overflow-x-auto">
                <div class="min-w-[300px]">
                    <div class="p-2 sm:p-4 border-b border-gray-200 dark:border-gray-700">
                        <h4 class="text-center font-medium text-sm sm:text-base text-gray-900 dark:text-white">{{ formatDate(currentDate, 'full') }}</h4>
                    </div>
                    <div v-for="hour in dayHours" :key="hour" class="flex border-b border-gray-200 dark:border-gray-700">
                        <div class="w-14 sm:w-20 p-1 sm:p-2 text-[10px] sm:text-xs text-gray-500">{{ hour }}:00</div>
                        <div class="flex-1 min-h-[40px] sm:min-h-[60px] p-0.5 sm:p-1">
                            <div
                                v-for="todo in getTodosForDateTime(currentDate, hour)"
                                :key="todo.id"
                                class="text-[10px] sm:text-xs p-0.5 rounded cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700 mb-0.5"
                                :class="getPriorityClass(todo.priority)"
                                @click.stop="openTodo(todo.id)"
                            >
                                {{ todo.title }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    todos: {
        type: Array,
        required: true,
        default: () => []
    }
})

const currentMode = ref('month')
const currentDate = ref(new Date())

const modes = [
    { value: 'month', label: 'Месяц', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
    { value: 'week', label: 'Неделя', icon: 'M4 6h16M4 10h16M4 14h16M4 18h16' },
    { value: 'day', label: 'День', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' }
]

const weekDays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']
const weekDaysShort = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']
const dayHours = Array.from({ length: 24 }, (_, i) => i)

const currentTitle = computed(() => {
    if (currentMode.value === 'month') {
        return currentDate.value.toLocaleDateString('ru-RU', { month: 'long', year: 'numeric' })
    } else if (currentMode.value === 'week') {
        const start = getWeekStart(currentDate.value)
        const end = new Date(start)
        end.setDate(start.getDate() + 6)
        return `${formatDateShort(start)} - ${formatDateShort(end)}`
    } else {
        return formatDate(currentDate.value, 'full')
    }
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

const isToday = (date) => {
    const today = new Date()
    return date.toDateString() === today.toDateString()
}

const getWeekStart = (date) => {
    const d = new Date(date)
    const day = d.getDay()
    const diff = d.getDate() - (day === 0 ? 6 : day - 1)
    return new Date(d.setDate(diff))
}

const getWeekDate = (index) => {
    const start = getWeekStart(currentDate.value)
    const date = new Date(start)
    date.setDate(start.getDate() + index - 1)
    return date
}

const formatDate = (date, format = 'short') => {
    if (format === 'short') {
        return date.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
    } else if (format === 'full') {
        return date.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
    }
    return date.toLocaleDateString('ru-RU')
}

const formatDateShort = (date) => {
    return date.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
}

const getTodosForDate = (date) => {
    if (!props.todos?.length) return []
    const dateStr = date.toDateString()
    return props.todos.filter(todo => {
        if (!todo.due_date) return false
        const todoDate = new Date(todo.due_date)
        return todoDate.toDateString() === dateStr
    })
}

const getTodosForDateTime = (date, hour) => {
    if (!props.todos?.length) return []
    const dateStr = date.toDateString()
    return props.todos.filter(todo => {
        if (!todo.due_date) return false
        const todoDate = new Date(todo.due_date)
        return todoDate.toDateString() === dateStr
    })
}

const getPriorityClass = (priority) => {
    const map = {
        low: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        medium: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        high: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
        urgent: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
    }
    return map[priority] || ''
}

const previousPeriod = () => {
    if (currentMode.value === 'month') {
        currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1)
    } else if (currentMode.value === 'week') {
        currentDate.value = new Date(currentDate.value.getTime() - 7 * 24 * 60 * 60 * 1000)
    } else {
        currentDate.value = new Date(currentDate.value.getTime() - 24 * 60 * 60 * 1000)
    }
}

const nextPeriod = () => {
    if (currentMode.value === 'month') {
        currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1)
    } else if (currentMode.value === 'week') {
        currentDate.value = new Date(currentDate.value.getTime() + 7 * 24 * 60 * 60 * 1000)
    } else {
        currentDate.value = new Date(currentDate.value.getTime() + 24 * 60 * 60 * 1000)
    }
}

const openTodo = (id) => {
    if (id) {
        router.get(route('todos.show', id))
    }
}
</script>
