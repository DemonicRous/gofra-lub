<!-- resources/js/Pages/Admin/Scoring/Categories.vue -->
<template>
    <AppLayout>
        <Head title="Управление категориями баллов" />

        <div class="container mx-auto px-4 py-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl overflow-hidden">
                <!-- Заголовок -->
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 px-6 py-4">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-white">Управление категориями баллов</h1>
                            <p class="text-blue-100 mt-1">Настройка системы начисления баллов для конструкторов и дизайнеров</p>
                        </div>
                        <button
                            @click="openCreateModal"
                            class="bg-white text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-md transition font-medium shadow-md"
                        >
                            + Создать категорию
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Табы для переключения между отделами -->
                    <div class="flex gap-2 mb-6 border-b border-gray-200 dark:border-gray-700">
                        <button
                            @click="activeTab = 'constructor'"
                            :class="[
                                'px-4 py-2 font-medium transition',
                                activeTab === 'constructor'
                                    ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600'
                                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'
                            ]"
                        >
                            Конструкторы
                        </button>
                        <button
                            @click="activeTab = 'designer'"
                            :class="[
                                'px-4 py-2 font-medium transition',
                                activeTab === 'designer'
                                    ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600'
                                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'
                            ]"
                        >
                            Дизайнеры
                        </button>
                    </div>

                    <!-- Дерево категорий -->
                    <div class="space-y-4">
                        <div
                            v-for="parent in filteredTree"
                            :key="parent.id"
                            class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden"
                        >
                            <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 flex items-center justify-between">
                                <div class="flex items-center gap-3 flex-wrap">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                    </svg>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ parent.name }}</h3>
                                    <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                        Базовые баллы: {{ formatPoints(parent.base_points) }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ parent.is_multiselect ? 'Множественный выбор' : 'Одиночный выбор' }}
                                    </span>
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        @click="editCategory(parent)"
                                        class="text-blue-600 hover:text-blue-700"
                                        title="Редактировать"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button
                                        @click="deleteCategory(parent.id)"
                                        class="text-red-500 hover:text-red-700"
                                        title="Удалить"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Подкатегории -->
                            <div v-if="parent.children && parent.children.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
                                <div
                                    v-for="child in parent.children"
                                    :key="child.id"
                                    class="px-4 py-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                >
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">{{ child.name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            Дополнительные баллы: +{{ formatPoints(child.points) }}
                                            <span class="text-blue-600 dark:text-blue-400 ml-2">
                                                (Итого: {{ formatPoints(Number(parent.base_points) + Number(child.points)) }})
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button
                                            @click="editCategory(child)"
                                            class="text-blue-600 hover:text-blue-700"
                                            title="Редактировать"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            @click="deleteCategory(child.id)"
                                            class="text-red-500 hover:text-red-700"
                                            title="Удалить"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="px-4 py-3 text-center text-gray-500 text-sm">
                                Нет подкатегорий
                            </div>
                        </div>

                        <div v-if="filteredTree.length === 0" class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <p class="text-gray-500 dark:text-gray-400">Нет категорий для отображения</p>
                            <button @click="openCreateModal" class="mt-4 text-blue-600 hover:text-blue-700">
                                Создать первую категорию
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно создания/редактирования категории -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50 p-4" @click.self="closeModal">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 px-6 py-4">
                    <h2 class="text-xl font-bold text-white">{{ editingCategory ? 'Редактирование категории' : 'Создание категории' }}</h2>
                </div>

                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Название <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            :class="form.errors.name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                            required
                        />
                        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Тип категории
                        </label>
                        <select
                            v-model="form.type"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        >
                            <option value="constructor">Конструкторы</option>
                            <option value="designer">Дизайнеры</option>
                            <option value="common">Общая</option>
                        </select>
                    </div>

                    <!-- Выбор родительской категории -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Родительская категория
                        </label>
                        <select
                            v-model="form.parent_id"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        >
                            <option value="">Нет (корневая категория)</option>
                            <option
                                v-for="parent in rootCategories"
                                :key="parent.id"
                                :value="parent.id"
                                :disabled="editingCategory && editingCategory.id === parent.id"
                            >
                                {{ parent.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Базовые баллы (для корневой категории) -->
                    <div v-if="!form.parent_id">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Базовые баллы
                        </label>
                        <input
                            v-model.number="form.base_points"
                            type="number"
                            step="0.5"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        />
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Базовые баллы начисляются за выбор этой категории
                        </p>
                    </div>

                    <!-- Дополнительные баллы (для подкатегории) -->
                    <div v-else>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Дополнительные баллы
                        </label>
                        <input
                            v-model.number="form.points"
                            type="number"
                            step="0.5"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        />
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Дополнительные баллы добавляются к базовым баллам родительской категории
                        </p>
                    </div>

                    <!-- Единица измерения -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Единица измерения
                        </label>
                        <input
                            v-model="form.unit"
                            type="text"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="шт, день, раз"
                        />
                    </div>

                    <!-- Множественный выбор (только для корневых) -->
                    <div v-if="!form.parent_id">
                        <label class="flex items-center cursor-pointer">
                            <input
                                v-model="form.is_multiselect"
                                type="checkbox"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:border-gray-600 dark:bg-gray-700"
                            />
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                Множественный выбор
                            </span>
                        </label>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Если включено, можно выбрать несколько подкатегорий
                        </p>
                    </div>

                    <!-- Активность -->
                    <div>
                        <label class="flex items-center cursor-pointer">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:border-gray-600 dark:bg-gray-700"
                            />
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                Активна
                            </span>
                        </label>
                    </div>

                    <!-- Описание -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Описание
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="2"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Краткое описание категории..."
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 rounded-md transition"
                        >
                            Отмена
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Сохранение...' : (editingCategory ? 'Обновить' : 'Создать') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    categories: {
        type: Object,
        required: true
    },
    tree: {
        type: Array,
        default: () => []
    }
})

const activeTab = ref('constructor')
const showModal = ref(false)
const editingCategory = ref(null)

const form = useForm({
    name: '',
    description: '',
    type: 'constructor',
    parent_id: null,
    base_points: 0,
    points: 0,
    unit: 'шт',
    is_multiselect: false,
    is_active: true
})

// Фильтруем дерево по выбранной вкладке
const filteredTree = computed(() => {
    if (!props.tree) return []
    return props.tree.filter(cat => cat.type === activeTab.value)
})

// Корневые категории для выбора родителя
const rootCategories = computed(() => {
    if (!props.tree) return []
    return props.tree.filter(cat => cat.parent_id === null && cat.type === activeTab.value)
})

// Форматирование чисел
const formatPoints = (value) => {
    if (value === null || value === undefined) return '0'
    const num = Number(value)
    if (isNaN(num)) return '0'
    if (num % 1 === 0) return num.toString()
    return num.toFixed(2).replace(/\.?0+$/, '')
}

const openCreateModal = () => {
    editingCategory.value = null
    form.reset()
    form.type = activeTab.value
    form.parent_id = null
    form.base_points = 0
    form.points = 0
    form.unit = 'шт'
    form.is_multiselect = false
    form.is_active = true
    showModal.value = true
}

const editCategory = (category) => {
    editingCategory.value = category
    form.name = category.name
    form.description = category.description || ''
    form.type = category.type
    form.parent_id = category.parent_id || null
    form.base_points = category.base_points || 0
    form.points = category.points || 0
    form.unit = category.unit || 'шт'
    form.is_multiselect = category.is_multiselect || false
    form.is_active = category.is_active !== false
    showModal.value = true
}

const submitForm = () => {
    if (editingCategory.value) {
        form.put(route('admin.scoring.categories.update', editingCategory.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal()
            }
        })
    } else {
        form.post(route('admin.scoring.categories.store'), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal()
            }
        })
    }
}

const deleteCategory = (id) => {
    if (confirm('Вы уверены, что хотите удалить эту категорию? Это действие нельзя отменить.')) {
        router.delete(route('admin.scoring.categories.destroy', id), {
            preserveScroll: true
        })
    }
}

const closeModal = () => {
    showModal.value = false
    editingCategory.value = null
    form.reset()
}
</script>
