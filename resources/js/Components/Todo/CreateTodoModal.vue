<template>
    <div v-if="show" class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50 p-4" @click.self="$emit('close')">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 px-6 py-4 sticky top-0">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-white">Создание задачи</h2>
                    <button @click="$emit('close')" class="text-white hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <form @submit.prevent="submit" class="p-6 space-y-5">
                <!-- Название -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Название <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.title"
                        type="text"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400"
                        placeholder="Введите название задачи"
                        required
                    />
                    <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
                </div>

                <!-- Тип задачи -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Тип задачи
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <button
                            v-for="type in taskTypes"
                            :key="type.value"
                            type="button"
                            @click="onTypeChange(type.value)"
                            :class="[
                                'p-3 rounded-lg border-2 transition-all text-center',
                                form.type === type.value
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                            ]"
                        >
                            <svg class="w-5 h-5 mx-auto mb-1" :class="form.type === type.value ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path :d="type.icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                            </svg>
                            <div class="text-xs font-medium" :class="form.type === type.value ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300'">
                                {{ type.label }}
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Приоритет -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Приоритет
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="priority in priorities"
                            :key="priority.value"
                            type="button"
                            @click="form.priority = priority.value"
                            :class="[
                                'flex items-center gap-1 px-3 py-2 rounded-lg text-sm transition',
                                form.priority === priority.value
                                    ? priority.activeClass
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                            ]"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path :d="priority.icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                            </svg>
                            {{ priority.label }}
                        </button>
                    </div>
                </div>

                <!-- Видимость -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Видимость
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="visibility in visibilities"
                            :key="visibility.value"
                            type="button"
                            @click="form.visibility = visibility.value"
                            :class="[
                                'flex items-center gap-1 px-4 py-2 rounded-lg text-sm transition',
                                form.visibility === visibility.value
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                            ]"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path :d="visibility.icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                            </svg>
                            {{ visibility.label }}
                        </button>
                    </div>
                </div>

                <!-- Назначить ответственному -->
                <div v-if="form.visibility !== 'personal'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Назначить ответственному
                    </label>
                    <div class="relative">
                        <input
                            v-model="assigneeSearch"
                            type="text"
                            @input="searchAssignees"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Начните вводить имя или @nickname..."
                        />
                        <div v-if="filteredAssignees.length && assigneeSearch" class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border rounded-lg shadow-lg max-h-48 overflow-y-auto">
                            <button
                                v-for="user in filteredAssignees"
                                :key="user.id"
                                type="button"
                                @click="selectAssignee(user)"
                                class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition flex items-center justify-between"
                            >
                                <span class="text-gray-900 dark:text-white">{{ user.full_name }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">@{{ user.nickname }}</span>
                            </button>
                        </div>
                    </div>
                    <div v-if="form.assigned_to" class="mt-2 flex items-center gap-2 p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-sm">{{ selectedAssigneeName }}</span>
                        <button type="button" @click="clearAssignee" class="text-red-500 text-xs">×</button>
                    </div>
                </div>

                <!-- Участники -->
                <div v-if="form.visibility !== 'personal'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Участники
                    </label>
                    <div class="relative">
                        <input
                            v-model="participantSearch"
                            type="text"
                            @input="searchParticipants"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Добавить участников..."
                        />
                        <div v-if="filteredParticipants.length && participantSearch" class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border rounded-lg shadow-lg max-h-48 overflow-y-auto">
                            <button
                                v-for="user in filteredParticipants"
                                :key="user.id"
                                type="button"
                                @click="addParticipant(user)"
                                class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition flex items-center justify-between"
                            >
                                <span class="text-gray-900 dark:text-white">{{ user.full_name }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">@{{ user.nickname }}</span>
                            </button>
                        </div>
                    </div>
                    <div v-if="form.participants.length" class="mt-2 flex flex-wrap gap-2">
                        <span
                            v-for="participant in form.participants"
                            :key="participant.id"
                            class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-sm"
                        >
                            {{ participant.full_name }}
                            <span class="text-xs text-gray-500">@{{ participant.nickname }}</span>
                            <button type="button" @click="removeParticipant(participant.id)" class="text-red-500">×</button>
                        </span>
                    </div>
                </div>

                <!-- Описание -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Описание
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="Подробное описание задачи..."
                    ></textarea>
                </div>

                <!-- Сроки (для идеи не показываем) -->
                <div v-if="form.type !== 'idea'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Дата выполнения
                        </label>
                        <input
                            v-model="form.due_date"
                            type="date"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Время выполнения
                        </label>
                        <input
                            v-model="form.due_time"
                            type="time"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        />
                    </div>
                </div>

                <!-- Напоминание -->
                <div v-if="form.type === 'reminder'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Дата напоминания
                        </label>
                        <input
                            v-model="form.reminder_date"
                            type="date"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Время напоминания
                        </label>
                        <input
                            v-model="form.reminder_time"
                            type="time"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        />
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-700">
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 rounded-md transition"
                    >
                        Отмена
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Создание...' : 'Создать задачу' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    show: Boolean,
    users: {
        type: Array,
        default: () => []
    },
    departments: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['close', 'created'])

const assigneeSearch = ref('')
const participantSearch = ref('')

const taskTypes = [
    { value: 'task', label: 'Задача', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
    { value: 'urgent', label: 'Срочная', icon: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { value: 'reminder', label: 'Напоминание', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    { value: 'idea', label: 'Идея', icon: 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z' }
]

const priorities = [
    { value: 'low', label: 'Низкий', activeClass: 'bg-gray-600 text-white', icon: 'M5 15l7-7 7 7' },
    { value: 'medium', label: 'Средний', activeClass: 'bg-blue-600 text-white', icon: 'M5 15l7-7 7 7M5 11l7-7 7 7' },
    { value: 'high', label: 'Высокий', activeClass: 'bg-orange-600 text-white', icon: 'M5 15l7-7 7 7M5 11l7-7 7 7M5 7l7-7 7 7' },
    { value: 'urgent', label: 'Срочный', activeClass: 'bg-red-600 text-white', icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' }
]

const visibilities = [
    { value: 'personal', label: 'Личная', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
    { value: 'department', label: 'Отдел', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
    { value: 'company', label: 'Компания', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' }
]

const form = useForm({
    title: '',
    description: '',
    type: 'task',
    priority: 'medium',
    visibility: 'personal',
    assigned_to: null,
    participants: [], // Здесь будем хранить ID участников
    due_date: '',
    due_time: '',
    reminder_date: '',
    reminder_time: ''
})

const filteredAssignees = computed(() => {
    if (!assigneeSearch.value) return []
    const search = assigneeSearch.value.toLowerCase().replace('@', '')
    return (props.users || []).filter(user =>
        user.full_name?.toLowerCase().includes(search) ||
        user.nickname?.toLowerCase().includes(search)
    ).slice(0, 10)
})

const filteredParticipants = computed(() => {
    if (!participantSearch.value) return []
    const search = participantSearch.value.toLowerCase().replace('@', '')
    return (props.users || []).filter(user =>
        (user.full_name?.toLowerCase().includes(search) ||
            user.nickname?.toLowerCase().includes(search)) &&
        !form.participants.includes(user.id) // Сравниваем по ID
    ).slice(0, 10)
})

const selectedAssigneeName = computed(() => {
    if (!form.assigned_to) return ''
    const user = (props.users || []).find(u => u.id === form.assigned_to)
    return user ? `${user.full_name} (@${user.nickname})` : ''
})

const onTypeChange = (type) => {
    form.type = type
    if (type === 'idea') {
        form.due_date = ''
        form.due_time = ''
    }
}

const searchAssignees = () => {}
const searchParticipants = () => {}

const selectAssignee = (user) => {
    form.assigned_to = user.id
    assigneeSearch.value = ''
}

const clearAssignee = () => {
    form.assigned_to = null
}

const addParticipant = (user) => {
    if (!form.participants.includes(user.id)) {
        form.participants.push(user.id) // Сохраняем только ID
    }
    participantSearch.value = ''
}

const removeParticipant = (userId) => {
    form.participants = form.participants.filter(id => id !== userId)
}

const submit = () => {
    const data = {
        title: form.title,
        description: form.description,
        type: form.type,
        priority: form.priority,
        visibility: form.visibility,
        assigned_to: form.assigned_to,
        participants: form.participants, // Теперь это массив ID
        due_date: form.due_date || null,
        due_time: form.due_time || null,
        reminder_at: form.reminder_date && form.reminder_time
            ? `${form.reminder_date} ${form.reminder_time}`
            : null
    }

    form.post(route('todos.store'), {
        preserveScroll: true,
        onSuccess: () => {
            emit('created')
            emit('close')
            form.reset()
            form.assigned_to = null
            form.participants = []
        },
        onError: (errors) => {
            console.error('Ошибка создания задачи:', errors)
        }
    })
}
</script>
