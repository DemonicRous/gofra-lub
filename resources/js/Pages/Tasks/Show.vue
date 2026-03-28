<!-- resources/js/Pages/Tasks/Show.vue -->

<template>
    <AppLayout>
        <Head :title="task.title" />

        <div class="container mx-auto px-4 py-8">
            <div class="max-w-4xl mx-auto">
                <!-- Кнопка назад -->
                <Link :href="route('tasks.index')" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-6 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад к списку
                </Link>

                <!-- Основная карточка задачи -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl overflow-hidden transition-colors">
                    <!-- Заголовок с цветным индикатором статуса -->
                    <div class="relative">
                        <div class="absolute top-0 left-0 w-1 h-full" :class="getStatusColorClass(task.status)"></div>
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 px-6 py-4">
                            <div class="flex items-center justify-between flex-wrap gap-4">
                                <div class="flex-1">
                                    <h1 class="text-xl font-bold text-white">{{ task.title }}</h1>
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        <!-- Приоритет -->
                                        <span :class="getPriorityClass(task.priority)" class="px-2 py-0.5 text-xs rounded-full">
                      {{ getPriorityName(task.priority) }}
                    </span>
                                        <!-- Статус с иконкой -->
                                        <span :class="getStatusBadgeClass(task.status)" class="px-2 py-0.5 text-xs rounded-full inline-flex items-center gap-1">
                      <span class="w-2 h-2 rounded-full" :class="getStatusDotClass(task.status)"></span>
                      {{ getStatusName(task.status) }}
                    </span>
                                        <!-- Тип задачи -->
                                        <span v-if="task.type !== 'task'" :class="getTypeClass(task.type)" class="px-2 py-0.5 text-xs rounded-full">
                      {{ getTypeName(task.type) }}
                    </span>
                                        <span v-if="task.is_overdue" class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                      Просрочена
                    </span>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <!-- Кнопка редактирования - только для создателя -->
                                    <button
                                        v-if="canEdit"
                                        @click="openEditModal"
                                        class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-md transition flex items-center gap-2"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Редактировать
                                    </button>
                                    <!-- Кнопка удаления - только для создателя -->
                                    <button
                                        v-if="canDelete"
                                        @click="deleteTask"
                                        class="bg-red-600/80 hover:bg-red-700 text-white px-4 py-2 rounded-md transition flex items-center gap-2"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Удалить
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Описание и детали -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Описание</h3>
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                                    {{ task.description || 'Нет описания' }}
                                </p>
                            </div>

                            <div class="space-y-3">
                                <!-- Создатель -->
                                <div class="flex justify-between items-center pb-2 border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Создатель:</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center text-xs text-blue-600 dark:text-blue-400">
                                            {{ task.creator?.first_name?.charAt(0) || 'U' }}
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ task.creator_short || task.creator?.short_name || task.creator_name || '—' }}</span>
                                    </div>
                                </div>

                                <!-- Ответственный -->
                                <div class="flex justify-between items-center pb-2 border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Ответственный:</span>
                                    </div>
                                    <div v-if="task.assignee" class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center text-xs text-green-600 dark:text-green-400">
                                            {{ task.assignee?.first_name?.charAt(0) || 'U' }}
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ task.assignee_short || task.assignee?.short_name || task.assignee_name || 'Не назначен' }}</span>
                                    </div>
                                    <span v-else class="text-sm text-gray-500">Не назначен</span>
                                </div>

                                <div v-if="task.due_date" class="flex justify-between items-center pb-2 border-b border-gray-200 dark:border-gray-700">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Срок выполнения:</span>
                                    <span :class="task.is_overdue ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-900 dark:text-white'" class="text-sm">
                    {{ formatDateTime(task.due_date) }}
                  </span>
                                </div>

                                <div v-if="task.completed_at" class="flex justify-between items-center pb-2 border-b border-gray-200 dark:border-gray-700">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Выполнена:</span>
                                    <span class="text-sm text-gray-900 dark:text-white">{{ formatDateTime(task.completed_at) }}</span>
                                </div>

                                <div v-if="task.department" class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Отдел:</span>
                                    <span class="text-sm text-gray-900 dark:text-white">{{ task.department.name }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Теги -->
                        <div v-if="task.tags?.length" class="border-t border-gray-200 dark:border-gray-700 pt-4 mb-6">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Теги</h3>
                            <div class="flex flex-wrap gap-2">
                <span
                    v-for="tag in task.tags"
                    :key="tag.id"
                    class="px-3 py-1 rounded-full text-sm"
                    :style="{ backgroundColor: tag.color + '20', color: tag.color }"
                >
                  {{ tag.name }}
                </span>
                            </div>
                        </div>

                        <!-- Участники -->
                        <div v-if="task.participants?.length" class="border-t border-gray-200 dark:border-gray-700 pt-4 mb-6">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Участники</h3>
                            <div class="flex flex-wrap gap-2">
                <span
                    v-for="participant in task.participants"
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
                                    v-for="subtask in task.subtasks"
                                    :key="subtask.id"
                                    class="flex items-center gap-3 p-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg transition"
                                >
                                    <button @click="toggleSubtask(subtask.id)" class="flex-shrink-0">
                                        <svg
                                            :class="subtask.is_completed ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-gray-500'"
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
                                        :class="subtask.is_completed ? 'line-through text-gray-400 dark:text-gray-500' : 'text-gray-700 dark:text-gray-300'"
                                        class="flex-1 text-sm"
                                    >
                    {{ subtask.title }}
                  </span>
                                </div>
                                <div v-if="!task.subtasks?.length" class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                    Нет подзадач
                                </div>
                            </div>
                        </div>

                        <!-- Комментарии -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Комментарии</h3>

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

                            <div class="space-y-3">
                                <div
                                    v-for="comment in task.comments"
                                    :key="comment.id"
                                    class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
                                >
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white text-xs">
                                                {{ comment.user?.first_name?.charAt(0) || 'U' }}
                                            </div>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ comment.user_short || comment.user?.short_name || comment.user_name || '—' }}</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatDateTime(comment.created_at) }}</span>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ comment.content }}</p>
                                </div>
                                <div v-if="!task.comments?.length" class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto transition-colors">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 px-6 py-4 sticky top-0">
                    <h2 class="text-xl font-bold text-white">Редактирование задачи</h2>
                </div>

                <form @submit.prevent="updateTask" class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Название <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="editForm.title"
                                type="text"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                :class="editForm.errors.title ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
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
                                :class="editForm.errors.description ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                            ></textarea>
                            <p v-if="editForm.errors.description" class="text-red-500 text-xs mt-1">{{ editForm.errors.description }}</p>
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
                                    <option value="critical">Критический</option>
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
                                    <option value="backlog">Бэклог</option>
                                    <option value="todo">К выполнению</option>
                                    <option value="in_progress">В работе</option>
                                    <option value="in_review">На проверке</option>
                                    <option value="completed">Выполнена</option>
                                    <option value="cancelled">Отменена</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Срок выполнения
                            </label>
                            <input
                                v-model="editForm.due_date"
                                type="datetime-local"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            />
                        </div>

                        <div v-if="task.visibility !== 'personal'">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Ответственный
                            </label>
                            <select
                                v-model="editForm.assigned_to"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            >
                                <option value="">Не назначен</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.short_name || user.full_name }}
                                </option>
                            </select>
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md transition-colors">
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
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
    task: Object,
    users: {
        type: Array,
        default: () => []
    }
})

const page = usePage()
const currentUser = computed(() => page.props.auth?.user)

// Проверка прав - создатель может редактировать и удалять
const canEdit = computed(() => {
    const creatorId = props.task.created_by?.id || props.task.created_by
    return currentUser.value?.id === creatorId
})

const canDelete = computed(() => {
    const creatorId = props.task.created_by?.id || props.task.created_by
    return currentUser.value?.id === creatorId
})

const showEditModal = ref(false)
const showSubtaskModal = ref(false)

const editForm = useForm({
    title: props.task.title,
    description: props.task.description || '',
    priority: props.task.priority,
    status: props.task.status,
    due_date: props.task.due_date ? new Date(props.task.due_date).toISOString().slice(0, 16) : '',
    assigned_to: props.task.assignee?.id || ''
})

const commentForm = useForm({
    content: ''
})

const subtaskForm = useForm({
    title: ''
})

// Вспомогательные функции
const getPriorityName = (priority) => {
    const map = { low: 'Низкий', medium: 'Средний', high: 'Высокий', urgent: 'Срочный', critical: 'Критический' }
    return map[priority] || priority
}

const getPriorityClass = (priority) => {
    const map = {
        low: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        medium: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        high: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
        urgent: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        critical: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300'
    }
    return map[priority] || ''
}

const getStatusName = (status) => {
    const map = {
        backlog: 'Бэклог',
        todo: 'К выполнению',
        in_progress: 'В работе',
        in_review: 'На проверке',
        completed: 'Выполнена',
        cancelled: 'Отменена'
    }
    return map[status] || status
}

const getStatusBadgeClass = (status) => {
    const map = {
        backlog: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        todo: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        in_progress: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        in_review: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
        completed: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
    }
    return map[status] || ''
}

const getStatusDotClass = (status) => {
    const map = {
        backlog: 'bg-gray-500',
        todo: 'bg-blue-500',
        in_progress: 'bg-yellow-500',
        in_review: 'bg-purple-500',
        completed: 'bg-green-500',
        cancelled: 'bg-red-500'
    }
    return map[status] || 'bg-gray-500'
}

const getStatusColorClass = (status) => {
    const map = {
        backlog: 'bg-gray-500',
        todo: 'bg-blue-500',
        in_progress: 'bg-yellow-500',
        in_review: 'bg-purple-500',
        completed: 'bg-green-500',
        cancelled: 'bg-red-500'
    }
    return map[status] || 'bg-gray-500'
}

const getTypeName = (type) => {
    const map = {
        task: 'Задача',
        urgent: 'Срочная',
        reminder: 'Напоминание',
        idea: 'Идея',
        bug: 'Ошибка',
        feature: 'Новая функция'
    }
    return map[type] || type
}

const getTypeClass = (type) => {
    const map = {
        urgent: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        reminder: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
        idea: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        bug: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        feature: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
    }
    return map[type] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}

const formatDateTime = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleString('ru-RU')
}

const openEditModal = () => {
    editForm.title = props.task.title
    editForm.description = props.task.description || ''
    editForm.priority = props.task.priority
    editForm.status = props.task.status
    editForm.due_date = props.task.due_date ? new Date(props.task.due_date).toISOString().slice(0, 16) : ''
    editForm.assigned_to = props.task.assignee?.id || ''
    editForm.clearErrors()
    showEditModal.value = true
}

const closeEditModal = () => {
    showEditModal.value = false
    editForm.reset()
}

const updateTask = () => {
    editForm.put(route('tasks.update', props.task.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal()
            // Обновляем страницу
            router.reload()
        }
    })
}

const deleteTask = () => {
    if (confirm('Вы уверены, что хотите удалить эту задачу? Это действие необратимо.')) {
        router.delete(route('tasks.destroy', props.task.id))
    }
}

const addComment = () => {
    commentForm.post(route('tasks.comments.store', props.task.id), {
        preserveScroll: true,
        onSuccess: () => {
            commentForm.reset()
        }
    })
}

const addSubtask = () => {
    subtaskForm.post(route('tasks.subtasks.store', props.task.id), {
        preserveScroll: true,
        onSuccess: () => {
            subtaskForm.reset()
            showSubtaskModal.value = false
        }
    })
}

const toggleSubtask = (subtaskId) => {
    router.patch(route('tasks.subtasks.toggle', subtaskId), {}, {
        preserveScroll: true
    })
}
</script>
