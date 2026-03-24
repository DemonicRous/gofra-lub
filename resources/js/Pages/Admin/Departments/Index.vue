<template>
    <AppLayout>
        <Head title="Управление отделами" />

        <div class="container mx-auto px-4 py-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl overflow-hidden transition-colors duration-300">
                <!-- Заголовок -->
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 px-6 py-4">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-white">Управление отделами</h1>
                            <p class="text-blue-100 mt-1">Создание, редактирование и управление структурой отделов</p>
                        </div>
                        <div class="flex gap-3">
                            <Link :href="route('dashboard')" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-md transition">
                                ← Назад
                            </Link>
                            <button
                                @click="openCreateModal"
                                class="bg-white text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-md transition font-medium shadow-md"
                            >
                                + Создать отдел
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Фильтры и поиск -->
                    <div class="mb-6">
                        <div class="flex flex-wrap gap-4">
                            <div class="flex-1 min-w-[200px]">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <input
                                        v-model="filters.search"
                                        @input="debouncedSearch"
                                        type="text"
                                        placeholder="Поиск по названию или коду..."
                                        class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Таблица отделов -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/50">
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Название</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Код</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Руководитель</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Кол-во сотрудников</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Статус</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Действия</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="department in departments.data" :key="department.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ department.name }}</div>
                                    <div v-if="department.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ department.description }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400 font-mono">{{ department.code || '—' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div v-if="department.head" class="text-sm text-gray-900 dark:text-white">
                                        {{ department.head.short_name || department.head.full_name }}
                                    </div>
                                    <span v-else class="text-sm text-gray-400">Не назначен</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-900 dark:text-white">{{ department.users_count || 0 }}</span>
                                </td>
                                <td class="px-4 py-3">
                                        <span :class="department.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'" class="px-2 py-1 text-xs rounded-full">
                                            {{ department.is_active ? 'Активен' : 'Неактивен' }}
                                        </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <button
                                            @click="openEditModal(department)"
                                            class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-800/50 rounded-full transition"
                                            title="Редактировать"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button
                                            @click="deleteDepartment(department.id)"
                                            class="inline-flex items-center justify-center w-8 h-8 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-800/50 rounded-full transition"
                                            title="Удалить"
                                            :disabled="department.users_count > 0"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="departments.data?.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    Нет отделов для отображения
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагинация -->
                    <div class="mt-6">
                        <Pagination :links="departments.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно создания/редактирования отдела -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50 p-4" @click.self="closeModal">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto transition-colors duration-300">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 px-6 py-4 sticky top-0">
                    <h2 class="text-xl font-bold text-white">{{ isEditing ? 'Редактирование отдела' : 'Создание отдела' }}</h2>
                </div>

                <form @submit.prevent="submitForm" class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Название отдела <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                :class="form.errors.name ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                placeholder="Например: IT-отдел"
                            />
                            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Код отдела
                            </label>
                            <input
                                v-model="form.code"
                                type="text"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                :class="form.errors.code ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                placeholder="Например: IT"
                            />
                            <p v-if="form.errors.code" class="text-red-500 text-xs mt-1">{{ form.errors.code }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Уникальный идентификатор отдела (опционально)</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Руководитель отдела
                            </label>
                            <select
                                v-model="form.head_id"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                :class="form.errors.head_id ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                :disabled="loadingUsers"
                            >
                                <option value="">Не выбран</option>
                                <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                                    {{ user.full_name }} ({{ user.position_name }} - {{ getLevelName(user.position_level) }})
                                </option>
                            </select>
                            <p v-if="form.errors.head_id" class="text-red-500 text-xs mt-1">{{ form.errors.head_id }}</p>
                            <div v-if="loadingUsers" class="text-xs text-blue-600 dark:text-blue-400 mt-1 flex items-center gap-1">
                                <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Загрузка списка руководителей...
                            </div>
                            <div v-if="!loadingUsers && availableUsers.length === 0" class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                                ⚠️ Нет доступных руководителей. Для выбора руководителя, пользователь должен иметь должность с уровнем "Руководитель (Head)" или "Ведущий специалист (Lead)", подтвержденный email и быть одобренным.
                            </div>
                            <div v-if="!loadingUsers && availableUsers.length > 0" class="text-xs text-green-600 dark:text-green-400 mt-1">
                                ✓ Доступно {{ availableUsers.length }} руководитель(ей)
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Описание
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                :class="form.errors.description ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                placeholder="Описание отдела, его функции и задачи..."
                            ></textarea>
                            <p v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</p>
                        </div>

                        <div v-if="isEditing">
                            <label class="flex items-center cursor-pointer">
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:border-gray-600 dark:bg-gray-700"
                                />
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Отдел активен
                                </span>
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Неактивные отделы не отображаются при регистрации</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t dark:border-gray-700">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 rounded-md transition"
                        >
                            Отмена
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="form.processing"
                        >
                            <svg v-if="form.processing" class="animate-spin inline w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? 'Сохранение...' : (isEditing ? 'Обновить' : 'Создать') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import axios from 'axios'

const props = defineProps({
    departments: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({ search: '' })
    }
})

// Состояние
const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)
const availableUsers = ref([])
const loadingUsers = ref(false)

// Форма
const form = useForm({
    name: '',
    code: '',
    description: '',
    head_id: '',
    is_active: true
})

// Получение названия уровня
const getLevelName = (level) => {
    const levels = {
        'junior': 'Младший',
        'middle': 'Специалист',
        'senior': 'Старший',
        'lead': 'Ведущий',
        'head': 'Руководитель'
    }
    return levels[level] || level
}

// Поиск
let searchTimeout = null
const debouncedSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        applyFilters()
    }, 300)
}

const applyFilters = () => {
    router.get(route('admin.departments.index'), {
        search: props.filters.search
    }, {
        preserveState: true,
        preserveScroll: true
    })
}

// Загрузка списка руководителей через axios
const loadLeaders = async () => {
    loadingUsers.value = true
    try {
        const response = await axios.get('/admin/leaders', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })

        if (response.data && response.data.success && response.data.users) {
            availableUsers.value = response.data.users
        }
    } catch (error) {
        console.error('Ошибка загрузки руководителей:', error)
        availableUsers.value = []
    } finally {
        loadingUsers.value = false
    }
}

// Открытие модального окна создания
const openCreateModal = async () => {
    isEditing.value = false
    editingId.value = null
    form.reset()
    form.clearErrors()
    form.is_active = true
    await loadLeaders()
    showModal.value = true
}

// Открытие модального окна редактирования
const openEditModal = async (department) => {
    isEditing.value = true
    editingId.value = department.id
    form.name = department.name
    form.code = department.code || ''
    form.description = department.description || ''
    form.head_id = department.head_id || ''
    form.is_active = department.is_active
    form.clearErrors()
    await loadLeaders()
    showModal.value = true
}

// Отправка формы
const submitForm = () => {
    if (isEditing.value) {
        form.put(route('admin.departments.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal()
            }
        })
    } else {
        form.post(route('admin.departments.store'), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal()
            }
        })
    }
}

// Удаление отдела
const deleteDepartment = (id) => {
    if (confirm('Вы уверены, что хотите удалить этот отдел? Это действие нельзя отменить.')) {
        router.delete(route('admin.departments.destroy', id), {
            preserveScroll: true
        })
    }
}

// Закрытие модального окна
const closeModal = () => {
    showModal.value = false
    isEditing.value = false
    editingId.value = null
    form.reset()
    form.clearErrors()
    availableUsers.value = []
    loadingUsers.value = false
}
</script>

<style scoped>
@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>
