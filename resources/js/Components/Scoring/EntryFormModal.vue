<!-- resources/js/Pages/Scoring/components/EntryFormModal.vue -->
<template>
    <div v-if="show" class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50 p-4" @click.self="$emit('close')">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 px-6 py-4 sticky top-0 z-10">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-white">Добавление записи</h2>
                    <button @click="$emit('close')" class="text-white hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <form @submit.prevent="submit" class="p-6 space-y-6">
                <!-- Информация о заявке -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Номер заявки
                        </label>
                        <input
                            v-model="form.request_number"
                            type="text"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Например: 4854"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Контрагент
                        </label>
                        <input
                            v-model="form.counterparty"
                            type="text"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Например: ООО БМГ"
                        />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Менеджер
                        </label>
                        <select
                            v-model="form.manager_id"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        >
                            <option value="">Выберите менеджера</option>
                            <option v-for="manager in managers" :key="manager.id" :value="manager.id">
                                {{ manager.full_name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Категории работ -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Выберите выполненные работы <span class="text-red-500">*</span>
                    </label>

                    <!-- Отладочная информация -->
                    <div v-if="categories.length === 0" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-4">
                        <p class="text-yellow-800 dark:text-yellow-300 text-sm">
                            ⚠️ Нет доступных категорий. Обратитесь к администратору для настройки категорий.
                        </p>
                        <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-2">
                            Отладка: categories.length = {{ categories.length }}
                        </p>
                    </div>

                    <!-- Для каждой родительской категории -->
                    <div
                        v-for="parent in categories"
                        :key="parent.id"
                        class="mb-6 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden"
                    >
                        <!-- Заголовок категории с базовыми баллами -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-3 flex-wrap">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ parent.name }}</h3>
                                <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                    Базовые баллы: {{ formatPoints(parent.base_points) }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ parent.is_multiselect ? '✓ Можно выбрать несколько' : '◉ Выберите один вариант' }}
                            </div>
                        </div>

                        <!-- Список подкатегорий -->
                        <div v-if="parent.children && parent.children.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
                            <div
                                v-for="child in parent.children"
                                :key="child.id"
                                class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition cursor-pointer"
                                :class="{
                                    'bg-blue-50 dark:bg-blue-900/20': isSelected(parent.id, child.id),
                                    'opacity-50 cursor-not-allowed': !parent.is_multiselect && hasSelectedInParent(parent.id) && !isSelected(parent.id, child.id)
                                }"
                                @click="toggleCategory(parent, child)"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3">
                                            <input
                                                :type="parent.is_multiselect ? 'checkbox' : 'radio'"
                                                :checked="isSelected(parent.id, child.id)"
                                                :disabled="!parent.is_multiselect && hasSelectedInParent(parent.id) && !isSelected(parent.id, child.id)"
                                                @change="toggleCategory(parent, child)"
                                                class="w-4 h-4 text-blue-600"
                                                :class="{ 'rounded-full': !parent.is_multiselect }"
                                            />
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-white">{{ child.name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    Дополнительные баллы: +{{ formatPoints(child.points) }}
                                                    <span class="text-blue-600 dark:text-blue-400 ml-2">
                                                        (Итого: {{ formatPoints(Number(parent.base_points) + Number(child.points)) }})
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                                            +{{ formatPoints(Number(parent.base_points) + Number(child.points)) }}
                                        </div>
                                        <div class="text-xs text-gray-500">баллов</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="p-4 text-center text-gray-500 text-sm">
                            Нет подкатегорий для этой группы
                        </div>

                        <!-- Итого по категории -->
                        <div v-if="getCategoryTotal(parent.id) > 0" class="bg-gray-50 dark:bg-gray-700/50 px-4 py-2 text-right border-t border-gray-200 dark:border-gray-700">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Итого по категории:</span>
                            <span class="ml-2 text-lg font-bold text-blue-600 dark:text-blue-400">
                                {{ formatPoints(getCategoryTotal(parent.id)) }}
                            </span>
                        </div>
                    </div>

                    <p v-if="selectedCategoriesCount === 0 && showError" class="text-red-500 text-xs mt-1">
                        Выберите хотя бы одну работу
                    </p>
                </div>

                <!-- Варианты конструкции -->
                <div v-if="showVariantsInput">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Варианты конструкции
                        <span class="text-xs text-gray-500">(если есть несколько вариантов)</span>
                    </label>
                    <div class="space-y-2">
                        <div
                            v-for="(variant, index) in form.variants"
                            :key="index"
                            class="flex gap-2"
                        >
                            <input
                                v-model="variant.name"
                                type="text"
                                class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Название варианта"
                            />
                            <button
                                type="button"
                                @click="removeVariant(index)"
                                class="text-red-500 hover:text-red-700 p-2"
                                title="Удалить вариант"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="addVariant"
                        class="mt-2 text-blue-600 dark:text-blue-400 text-sm hover:underline flex items-center gap-1"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Добавить вариант
                    </button>
                </div>

                <!-- Примечания -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Примечания
                    </label>
                    <textarea
                        v-model="form.notes"
                        rows="2"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="Дополнительная информация, комментарии..."
                    ></textarea>
                </div>

                <!-- Предварительный расчет баллов -->
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-lg p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <span class="font-medium text-gray-700 dark:text-gray-300">Итого баллов:</span>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ getPointsBreakdown() }}
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                                {{ formatPoints(totalPoints) }}
                            </span>
                            <span class="text-sm text-gray-500 ml-1">баллов</span>
                        </div>
                    </div>
                </div>

                <!-- Кнопки -->
                <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-700">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 rounded-md transition"
                    >
                        Отмена
                    </button>
                    <button
                        type="submit"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition disabled:opacity-50 flex items-center gap-2"
                        :disabled="form.processing || selectedCategoriesCount === 0"
                    >
                        <svg v-if="form.processing" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Сохранение...' : 'Добавить запись' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    sheet: {
        type: Object,
        required: true
    },
    categories: {
        type: Array,
        default: () => []
    },
    managers: {
        type: Array,
        default: () => []
    }
})

// Добавьте отладочный вывод
console.log('EntryFormModal - categories:', props.categories)
console.log('EntryFormModal - categories length:', props.categories?.length)
console.log('EntryFormModal - managers:', props.managers)
console.log('EntryFormModal - managers length:', props.managers?.length)

const emit = defineEmits(['close', 'saved'])

// Храним выбранные категории
const selectedCategories = ref({})
const showError = ref(false)

const form = useForm({
    request_number: '',
    counterparty: '',
    manager_id: '',
    category_ids: [],
    variants: [],
    notes: ''
})

// Проверка, выбрана ли подкатегория
const isSelected = (parentId, childId) => {
    if (!selectedCategories.value[parentId]) return false

    const parent = getParentById(parentId)
    if (parent?.is_multiselect) {
        return selectedCategories.value[parentId].includes(childId)
    } else {
        return selectedCategories.value[parentId] === childId
    }
}

// Получить родительскую категорию по ID
const getParentById = (parentId) => {
    return props.categories.find(cat => cat.id === parentId)
}

// Проверить, есть ли выбранные в родительской категории
const hasSelectedInParent = (parentId) => {
    const parent = getParentById(parentId)
    if (parent?.is_multiselect) return false
    return selectedCategories.value[parentId] !== undefined
}

// Переключение выбора категории
const toggleCategory = (parent, child) => {
    if (parent.is_multiselect) {
        if (!selectedCategories.value[parent.id]) {
            selectedCategories.value[parent.id] = []
        }

        const index = selectedCategories.value[parent.id].indexOf(child.id)
        if (index === -1) {
            selectedCategories.value[parent.id].push(child.id)
        } else {
            selectedCategories.value[parent.id].splice(index, 1)
        }

        if (selectedCategories.value[parent.id].length === 0) {
            delete selectedCategories.value[parent.id]
        }
    } else {
        if (selectedCategories.value[parent.id] === child.id) {
            delete selectedCategories.value[parent.id]
        } else {
            selectedCategories.value[parent.id] = child.id
        }
    }

    updateFormCategoryIds()
    showError.value = false
}

// Обновление массива ID выбранных категорий
const updateFormCategoryIds = () => {
    const ids = []
    for (const parentId in selectedCategories.value) {
        const value = selectedCategories.value[parentId]
        if (Array.isArray(value)) {
            ids.push(...value)
        } else {
            ids.push(value)
        }
    }
    form.category_ids = ids
}

// Количество выбранных категорий
const selectedCategoriesCount = computed(() => {
    return form.category_ids.length
})

// Получить сумму баллов по категории
const getCategoryTotal = (parentId) => {
    const parent = getParentById(parentId)
    if (!parent) return 0

    const selected = selectedCategories.value[parentId]
    if (!selected) return 0

    let total = 0
    if (parent.is_multiselect && Array.isArray(selected)) {
        for (const childId of selected) {
            const child = parent.children?.find(c => c.id === childId)
            if (child) {
                total += Number(parent.base_points) + Number(child.points)
            }
        }
    } else if (!parent.is_multiselect && selected) {
        const child = parent.children?.find(c => c.id === selected)
        if (child) {
            total = Number(parent.base_points) + Number(child.points)
        }
    }

    return total
}

// Получить общую сумму баллов
const totalPoints = computed(() => {
    let total = 0
    for (const parentId in selectedCategories.value) {
        total += getCategoryTotal(parseInt(parentId))
    }
    return total
})

// Получить детализацию баллов
const getPointsBreakdown = () => {
    const parts = []
    for (const parentId in selectedCategories.value) {
        const parent = getParentById(parseInt(parentId))
        if (!parent) continue

        const selected = selectedCategories.value[parentId]
        if (parent.is_multiselect && Array.isArray(selected)) {
            for (const childId of selected) {
                const child = parent.children?.find(c => c.id === childId)
                if (child) {
                    const points = Number(parent.base_points) + Number(child.points)
                    parts.push(`${parent.name} → ${child.name}: ${formatPoints(points)}`)
                }
            }
        } else if (!parent.is_multiselect && selected) {
            const child = parent.children?.find(c => c.id === selected)
            if (child) {
                const points = Number(parent.base_points) + Number(child.points)
                parts.push(`${parent.name} → ${child.name}: ${formatPoints(points)}`)
            }
        }
    }

    if (parts.length === 0) return 'Ничего не выбрано'
    return parts.join(' + ')
}

// Форматирование чисел
const formatPoints = (value) => {
    if (value === null || value === undefined) return '0'
    const num = Number(value)
    if (isNaN(num)) return '0'
    if (num % 1 === 0) return num.toString()
    return num.toFixed(2).replace(/\.?0+$/, '')
}

// Управление вариантами
const addVariant = () => {
    form.variants.push({ name: '' })
}

const removeVariant = (index) => {
    form.variants.splice(index, 1)
}

// Показываем поле вариантов для конструкторских работ
const showVariantsInput = computed(() => {
    for (const parentId in selectedCategories.value) {
        const parent = getParentById(parseInt(parentId))
        if (parent && (parent.name.includes('Конструкция') || parent.name.includes('каталога'))) {
            return true
        }
    }
    return false
})

// Автоматически добавляем пустой вариант
watch(selectedCategories, () => {
    if (showVariantsInput.value && form.variants.length === 0) {
        form.variants = [{ name: '' }]
    }
}, { deep: true })

// Отправка формы
const submit = () => {
    if (selectedCategoriesCount.value === 0) {
        showError.value = true
        return
    }

    // Получаем полное имя менеджера для сохранения
    const selectedManager = props.managers.find(m => m.id === form.manager_id)

    const data = {
        category_ids: form.category_ids,
        request_number: form.request_number,
        counterparty: form.counterparty,
        manager_name: selectedManager ? selectedManager.full_name : '',
        variants: form.variants.filter(v => v.name && v.name.trim()),
        notes: form.notes
    }

    form.post(route('scoring.entries.store', props.sheet.id), {
        data: data,
        preserveScroll: true,
        onSuccess: () => {
            emit('saved')
            resetForm()
        },
        onError: (errors) => {
            console.error('Ошибка сохранения:', errors)
        }
    })
}

// Сброс формы
const resetForm = () => {
    form.reset()
    selectedCategories.value = {}
    form.variants = []
    showError.value = false
}

const closeModal = () => {
    resetForm()
    emit('close')
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
