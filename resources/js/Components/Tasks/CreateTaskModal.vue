<!-- resources/js/Components/Tasks/CreateTaskModal.vue -->

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
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Название <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.title"
                        type="text"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="Введите название задачи"
                        required
                    />
                    <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Тип задачи
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <button
                            v-for="type in taskTypes"
                            :key="type.value"
                            type="button"
                            @click="form.type = type.value"
                            :class="[
                'p-3 rounded-lg border-2 transition text-center',
                form.type === type.value
                  ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                  : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'
              ]"
                        >
                            <div class="text-xs font-medium" :class="form.type === type.value ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300'">
                                {{ type.label }}
                            </div>
                        </button>
                    </div>
                </div>

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
                'px-3 py-2 rounded-lg text-sm transition',
                form.priority === priority.value
                  ? priority.activeClass
                  : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200'
              ]"
                        >
                            {{ priority.label }}
                        </button>
                    </div>
                </div>

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
                'px-4 py-2 rounded-lg text-sm transition',
                form.visibility === visibility.value
                  ? 'bg-blue-600 text-white'
                  : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200'
              ]"
                        >
                            {{ visibility.label }}
                        </button>
                    </div>
                </div>

                <div v-if="form.visibility !== 'personal'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Назначить ответственному
                    </label>
                    <select
                        v-model="form.assigned_to"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    >
                        <option value="">Не назначен</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">
                            {{ user.full_name }} (@{{ user.nickname }})
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Описание
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="Подробное описание задачи..."
                    ></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                <div v-if="projects && projects.length">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Проект
                    </label>
                    <select
                        v-model="form.project_id"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    >
                        <option value="">Без проекта</option>
                        <option v-for="project in projects" :key="project.id" :value="project.id">
                            {{ project.name }}
                        </option>
                    </select>
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
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    show: Boolean,
    users: {
        type: Array,
        default: () => []
    },
    tags: {
        type: Array,
        default: () => []
    },
    projects: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['close', 'created'])

const taskTypes = [
    { value: 'task', label: 'Задача' },
    { value: 'urgent', label: 'Срочная' },
    { value: 'reminder', label: 'Напоминание' },
    { value: 'idea', label: 'Идея' },
    { value: 'bug', label: 'Ошибка' },
    { value: 'feature', label: 'Новая функция' }
]

const priorities = [
    { value: 'low', label: 'Низкий', activeClass: 'bg-gray-600 text-white' },
    { value: 'medium', label: 'Средний', activeClass: 'bg-blue-600 text-white' },
    { value: 'high', label: 'Высокий', activeClass: 'bg-orange-600 text-white' },
    { value: 'urgent', label: 'Срочный', activeClass: 'bg-red-600 text-white' },
    { value: 'critical', label: 'Критический', activeClass: 'bg-purple-600 text-white' }
]

const visibilities = [
    { value: 'personal', label: 'Личная' },
    { value: 'department', label: 'Отдел' },
    { value: 'company', label: 'Компания' },
    { value: 'project', label: 'Проект' }
]

const form = useForm({
    title: '',
    description: '',
    type: 'task',
    priority: 'medium',
    visibility: 'personal',
    assigned_to: null,
    project_id: null,
    due_date: '',
    due_time: '',
    reminder_date: '',
    reminder_time: ''
})

const submit = () => {
    const data = {
        title: form.title,
        description: form.description,
        type: form.type,
        priority: form.priority,
        visibility: form.visibility,
        assigned_to: form.assigned_to,
        project_id: form.project_id,
        due_date: form.due_date || null,
        due_time: form.due_time || null,
        reminder_at: form.reminder_date && form.reminder_time
            ? `${form.reminder_date} ${form.reminder_time}`
            : null
    }

    form.post(route('tasks.store'), {
        preserveScroll: true,
        onSuccess: () => {
            emit('created')
            emit('close')
            form.reset()
            form.assigned_to = null
            form.project_id = null
        },
        onError: (errors) => {
            console.error('Ошибка создания задачи:', errors)
        }
    })
}
</script>
