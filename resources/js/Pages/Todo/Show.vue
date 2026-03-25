<template>
    <AppLayout>
        <Head :title="todo.title" />

        <div class="container mx-auto px-4 py-8">
            <div class="max-w-4xl mx-auto">
                <!-- Кнопка назад -->
                <Link :href="route('todos.index')" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-6 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад к списку
                </Link>

                <!-- Основная карточка задачи -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl overflow-hidden">
                    <!-- Заголовок -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 px-6 py-4">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h1 class="text-xl font-bold text-white">{{ todo.title }}</h1>
                                    <div class="flex flex-wrap gap-2 mt-1">
                                        <span :class="getPriorityClass(todo.priority)" class="px-2 py-0.5 text-xs rounded-full">
                                            {{ getPriorityName(todo.priority) }}
                                        </span>
                                        <span :class="getStatusClass(todo.status)" class="px-2 py-0.5 text-xs rounded-full">
                                            {{ getStatusName(todo.status) }}
                                        </span>
                                        <span v-if="todo.type === 'shared'" class="px-2 py-0.5 text-xs rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                            Общая задача
                                        </span>
                                        <span v-if="todo.is_overdue" class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                            Просрочена
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    v-if="canEdit"
                                    @click="openEditModal"
                                    class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-md transition"
                                >
                                    Редактировать
                                </button>
                                <button
                                    v-if="canDelete"
                                    @click="deleteTodo"
                                    class="bg-red-600/80 hover:bg-red-700 text-white px-4 py-2 rounded-md transition"
                                >
                                    Удалить
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Информация о задаче -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Описание</h3>
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                                    {{ todo.description || 'Нет описания' }}
                                </p>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Создатель:</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ todo.creator?.full_name }}</span>
                                </div>
                                <div v-if="todo.assignee" class="flex justify-between">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Ответственный:</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ todo.assignee?.full_name || todo.assignee?.short_name || 'Не назначен' }}</span>
                                </div>
                                <div v-if="todo.due_date" class="flex justify-between">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Срок выполнения:</span>
                                    <span :class="todo.is_overdue ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-900 dark:text-white'" class="text-sm">
                                        {{ formatDate(todo.due_date) }}
                                    </span>
                                </div>
                                <div v-if="todo.completed_at" class="flex justify-between">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Выполнена:</span>
                                    <span class="text-sm text-gray-900 dark:text-white">{{ formatDateTime(todo.completed_at) }}</span>
                                </div>
                                <div v-if="todo.department" class="flex justify-between">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Отдел:</span>
                                    <span class="text-sm text-gray-900 dark:text-white">{{ todo.department.name }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Участники -->
                        <div v-if="todo.type === 'shared' && todo.participants?.length" class="border-t border-gray-200 dark:border-gray-700 pt-4 mb-6">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Участники</h3>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="participant in todo.participants"
                                    :key="participant.id"
                                    class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-sm text-gray-700 dark:text-gray-300"
                                >
                                    {{ participant.full_name }}
                                </span>
                            </div>
                        </div>

                        <!-- Подзадачи -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mb-6">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Подзадачи</h3>
                                <button
                                    v-if="canEdit"
                                    @click="showSubtaskModal = true"
                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-700 text-sm"
                                >
                                    + Добавить
                                </button>
                            </div>
                            <div class="space-y-2">
                                <div
                                    v-for="subtask in todo.subtasks"
                                    :key="subtask.id"
                                    class="flex items-center gap-3 p-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg transition"
                                >
                                    <button
                                        @click="toggleSubtask(subtask.id)"
                                        class="flex-shrink-0"
                                    >
                                        <svg
                                            :class="subtask.is_completed ? 'text-green-600 dark:text-green-400' : 'text-gray-400'"
                                            class="w-5 h-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                v-if="subtask.is_completed"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                            <path
                                                v-else
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </button>
                                    <span
                                        :class="subtask.is_completed ? 'line-through text-gray-400' : 'text-gray-700 dark:text-gray-300'"
                                        class="flex-1 text-sm"
                                    >
                                        {{ subtask.title }}
                                    </span>
                                </div>
                                <div v-if="!todo.subtasks?.length" class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                    Нет подзадач
                                </div>
                            </div>
                        </div>

                        <!-- Комментарии -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Комментарии</h3>

                            <!-- Форма добавления комментария -->
                            <form @submit.prevent="addComment" class="mb-4">
                                <div class="flex gap-3">
                                    <div class="flex-1">
                                        <textarea
                                            v-model="commentForm.content"
                                            rows="2"
                                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white resize-none"
                                            placeholder="Написать комментарий..."
                                        ></textarea>
                                        <p v-if="commentForm.errors.content" class="text-red-500 text-xs mt-1">{{ commentForm.errors.content }}</p>
                                    </div>
                                    <button
                                        type="submit"
                                        class="self-end px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition disabled:opacity-50"
                                        :disabled="commentForm.processing"
                                    >
                                        Отправить
                                    </button>
                                </div>
                            </form>

                            <!-- Список комментариев -->
                            <div class="space-y-3">
                                <div
                                    v-for="comment in todo.comments"
                                    :key="comment.id"
                                    class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
                                >
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white text-xs">
                                                {{ comment.user?.first_name?.charAt(0) || 'U' }}
                                            </div>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ comment.user?.full_name }}</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatDateTime(comment.created_at) }}</span>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ comment.content }}</p>
                                </div>
                                <div v-if="!todo.comments?.length" class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                    Нет комментариев
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно редактирования -->
        <div v-if="showEditModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50 p-4" @click.self="closeEditModal">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 px-6 py-4 sticky top-0">
                    <h2 class="text-xl font-bold text-white">Редактирование задачи</h2>
                </div>

                <form @submit.prevent="updateTodo" class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Название <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="editForm.title"
                                type="text"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
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
                            ></textarea>
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
                                    <option value="pending">Ожидает</option>
                                    <option value="in_progress">В работе</option>
                                    <option value="completed">Выполнена</option>
                                    <option value="cancelled">Отменена</option>
                                </select>
                            </div>
                        </div>

                        <div v-if="todo.type === 'shared'">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Назначить ответственному
                            </label>
                            <select
                                v-model="editForm.assigned_to"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            >
                                <option value="">Не назначен</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.full_name }} ({{ user.department?.name || 'Без отдела' }})
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Срок выполнения
                            </label>
                            <input
                                v-model="editForm.due_date"
                                type="date"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            />
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

        <!-- Модальное окно добавления подзадачи -->
        <div v-if="showSubtaskModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50 p-4" @click.self="showSubtaskModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 px-6 py-4">
                    <h2 class="text-xl font-bold text-white">Добавить подзадачу</h2>
                </div>

                <form @submit.prevent="addSubtask" class="p-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Название подзадачи <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="subtaskForm.title"
                            type="text"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Введите название подзадачи"
                        />
                        <p v-if="subtaskForm.errors.title" class="text-red-500 text-xs mt-1">{{ subtaskForm.errors.title }}</p>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button
                            type="button"
                            @click="showSubtaskModal = false"
                            class="px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 rounded-md transition"
                        >
                            Отмена
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition disabled:opacity-50"
                            :disabled="subtaskForm.processing"
                        >
                            {{ subtaskForm.processing ? 'Добавление...' : 'Добавить' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    todo: Object,
    users: Array
})

const showEditModal = ref(false)
const showSubtaskModal = ref(false)

const editForm = useForm({
    title: props.todo.title,
    description: props.todo.description || '',
    priority: props.todo.priority,
    status: props.todo.status,
    assigned_to: props.todo.assigned_to?.id || '',
    due_date: props.todo.due_date || ''
})

const commentForm = useForm({
    content: ''
})

const subtaskForm = useForm({
    title: ''
})

const canEdit = computed(() => {
    const user = window.page?.props?.auth?.user
    return user?.id === props.todo.created_by?.id ||
        user?.role === 'admin' ||
        user?.role === 'manager'
})

const canDelete = computed(() => {
    const user = window.page?.props?.auth?.user
    return user?.id === props.todo.created_by?.id ||
        user?.role === 'admin'
})

const getPriorityName = (priority) => {
    const map = { low: 'Низкий', medium: 'Средний', high: 'Высокий', urgent: 'Срочный' }
    return map[priority] || priority
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

const getStatusName = (status) => {
    const map = {
        pending: 'Ожидает',
        in_progress: 'В работе',
        completed: 'Выполнена',
        cancelled: 'Отменена'
    }
    return map[status] || status
}

const getStatusClass = (status) => {
    const map = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        in_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        completed: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        cancelled: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    }
    return map[status] || ''
}

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('ru-RU')
}

const formatDateTime = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleString('ru-RU')
}

const openEditModal = () => {
    editForm.title = props.todo.title
    editForm.description = props.todo.description || ''
    editForm.priority = props.todo.priority
    editForm.status = props.todo.status
    editForm.assigned_to = props.todo.assigned_to?.id || ''
    editForm.due_date = props.todo.due_date || ''
    editForm.clearErrors()
    showEditModal.value = true
}

const closeEditModal = () => {
    showEditModal.value = false
    editForm.reset()
}

const updateTodo = () => {
    editForm.put(route('todos.update', props.todo.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal()
        }
    })
}

const deleteTodo = () => {
    if (confirm('Вы уверены, что хотите удалить эту задачу? Это действие необратимо.')) {
        router.delete(route('todos.destroy', props.todo.id))
    }
}

const addComment = () => {
    commentForm.post(route('todos.comments.store', props.todo.id), {
        preserveScroll: true,
        onSuccess: () => {
            commentForm.reset()
        }
    })
}

const addSubtask = () => {
    subtaskForm.post(route('todos.subtasks.store', props.todo.id), {
        preserveScroll: true,
        onSuccess: () => {
            subtaskForm.reset()
            showSubtaskModal.value = false
        }
    })
}

const toggleSubtask = (subtaskId) => {
    router.patch(route('todos.subtasks.toggle', subtaskId), {}, {
        preserveScroll: true
    })
}
</script>
