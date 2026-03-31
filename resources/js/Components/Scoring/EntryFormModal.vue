<!-- resources/js/Pages/Scoring/components/EntryFormModal.vue -->
<template>
    <div v-if="show" class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50 p-4" @click.self="closeModal">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <!-- Заголовок -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 px-6 py-4 sticky top-0 z-10">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-white">Добавление заявки</h2>
                    <button @click="closeModal" class="text-white hover:text-gray-200">
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

                <!-- Варианты конструкции -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Варианты конструкции <span class="text-red-500">*</span>
                        </label>
                        <button
                            type="button"
                            @click="addVariant"
                            class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-md hover:bg-blue-200 dark:hover:bg-blue-800/50 transition text-sm flex items-center gap-1"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Добавить вариант
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="(variant, vIndex) in form.variants"
                            :key="vIndex"
                            class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden"
                        >
                            <!-- Заголовок варианта -->
                            <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-2 flex items-center justify-between">
                                <div class="flex items-center gap-2 flex-1">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">
                                        Вариант {{ vIndex + 1 }}
                                    </span>
                                    <input
                                        v-model="variant.name"
                                        type="text"
                                        class="flex-1 max-w-xs px-3 py-1 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600"
                                        placeholder="Название варианта (например: Стандартный)"
                                    />
                                </div>
                                <button
                                    type="button"
                                    @click="removeVariant(vIndex)"
                                    class="text-red-500 hover:text-red-700 p-1"
                                    v-if="form.variants.length > 1"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>

                            <div class="p-4">
                                <!-- Категории для этого варианта (АККОРДЕОН) -->
                                <div class="mb-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Выполненные работы <span class="text-red-500">*</span>
                                    </label>

                                    <div v-if="categories.length === 0" class="text-center py-6 text-gray-500 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <p>Нет доступных категорий</p>
                                        <p class="text-sm mt-1">Обратитесь к администратору</p>
                                    </div>

                                    <!-- Аккордеон категорий -->
                                    <div v-for="parent in categories" :key="parent.id" class="mb-3 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                        <!-- Заголовок категории (кликабельный) -->
                                        <div
                                            @click="toggleAccordion(vIndex, parent.id)"
                                            class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 flex items-center justify-between cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                                        >
                                            <div class="flex items-center gap-3">
                                                <svg
                                                    class="w-5 h-5 text-gray-500 transition-transform"
                                                    :class="{ 'rotate-90': isAccordionOpen(vIndex, parent.id) }"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                                <span class="font-medium text-gray-900 dark:text-white">{{ parent.name }}</span>
                                                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                    {{ formatPoints(parent.base_points) }} баллов
                                                </span>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ parent.is_multiselect ? '✓ Можно выбрать несколько' : '◉ Выберите один' }}
                                            </div>
                                        </div>

                                        <!-- Содержимое категории (отображается при открытом аккордеоне) -->
                                        <div v-show="isAccordionOpen(vIndex, parent.id)" class="divide-y divide-gray-200 dark:divide-gray-700">
                                            <div
                                                v-for="child in parent.children"
                                                :key="child.id"
                                                class="p-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer transition"
                                                :class="{ 'bg-blue-50 dark:bg-blue-900/20': isSelected(vIndex, parent.id, child.id) }"
                                                @click.stop="toggleCategory(vIndex, parent, child)"
                                            >
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-3 flex-1">
                                                        <input
                                                            :type="parent.is_multiselect ? 'checkbox' : 'radio'"
                                                            :checked="isSelected(vIndex, parent.id, child.id)"
                                                            @change.stop="toggleCategory(vIndex, parent, child)"
                                                            class="w-4 h-4 text-blue-600"
                                                            :class="{ 'rounded-full': !parent.is_multiselect }"
                                                        />
                                                        <div>
                                                            <div class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ child.name }}</div>
                                                            <div class="text-xs text-gray-500">
                                                                +{{ formatPoints(child.points) }} баллов
                                                                <span class="text-blue-600 ml-1">
                                                                    (итого: {{ formatPoints(parent.base_points + child.points) }})
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-sm font-semibold text-blue-600">
                                                        +{{ formatPoints(parent.base_points + child.points) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Итого по варианту -->
                                <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 -mx-4 px-4 py-2 rounded-b-lg">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Баллов за вариант:</span>
                                        <span class="text-xl font-bold text-blue-600 dark:text-blue-400">
                                            {{ getVariantTotal(vIndex) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Общий итог -->
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Общий итог:</span>
                        <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            {{ totalPoints }}
                        </span>
                    </div>
                </div>

                <!-- Кнопки -->
                <div class="flex justify-end gap-3 pt-4 border-t">
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
                        :disabled="form.processing || !hasAnySelectedCategories"
                    >
                        <svg v-if="form.processing" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Сохранение...' : 'Добавить заявку' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
    show: Boolean,
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

const emit = defineEmits(['close', 'saved'])

// Состояние аккордеона для каждого варианта и категории
const accordionState = ref({})

// Форма с вариантами
const form = useForm({
    request_number: '',
    counterparty: '',
    manager_id: '',
    variants: [
        {
            name: '',
            category_ids: []
        }
    ]
})

// Выбранные категории для каждого варианта
const selectedCategories = ref({})

// Управление аккордеоном
const toggleAccordion = (variantIndex, parentId) => {
    const key = `${variantIndex}_${parentId}`
    accordionState.value[key] = !accordionState.value[key]
}

const isAccordionOpen = (variantIndex, parentId) => {
    const key = `${variantIndex}_${parentId}`
    return accordionState.value[key] || false
}

// Проверка выбранной категории
const isSelected = (variantIndex, parentId, childId) => {
    const key = `${variantIndex}_${parentId}`
    if (!selectedCategories.value[key]) return false

    const parent = getParentById(parentId)
    if (parent?.is_multiselect) {
        return selectedCategories.value[key].includes(childId)
    } else {
        return selectedCategories.value[key] === childId
    }
}

// Получить родительскую категорию
const getParentById = (parentId) => {
    return props.categories.find(cat => cat.id === parentId)
}

// Переключение категории
const toggleCategory = (variantIndex, parent, child) => {
    const key = `${variantIndex}_${parent.id}`

    if (!selectedCategories.value[key]) {
        selectedCategories.value[key] = parent.is_multiselect ? [] : null
    }

    if (parent.is_multiselect) {
        const index = selectedCategories.value[key].indexOf(child.id)
        if (index === -1) {
            selectedCategories.value[key].push(child.id)
        } else {
            selectedCategories.value[key].splice(index, 1)
        }
        if (selectedCategories.value[key].length === 0) {
            delete selectedCategories.value[key]
        }
    } else {
        if (selectedCategories.value[key] === child.id) {
            delete selectedCategories.value[key]
        } else {
            selectedCategories.value[key] = child.id
        }
    }

    // Обновляем category_ids для варианта
    updateVariantCategoryIds(variantIndex)
}

// Обновление category_ids для варианта
const updateVariantCategoryIds = (variantIndex) => {
    const ids = []
    for (const key in selectedCategories.value) {
        if (key.startsWith(`${variantIndex}_`)) {
            const value = selectedCategories.value[key]
            if (Array.isArray(value)) {
                ids.push(...value)
            } else if (value) {
                ids.push(value)
            }
        }
    }
    form.variants[variantIndex].category_ids = ids
}

// Форматирование чисел
const formatPoints = (value) => {
    if (value === null || value === undefined) return '0'
    const num = Number(value)
    if (isNaN(num)) return '0'
    if (num % 1 === 0) return num.toString()
    return num.toFixed(2).replace(/\.?0+$/, '')
}

// Получить сумму баллов для варианта (без количества)
const getVariantTotal = (variantIndex) => {
    let total = 0

    for (const key in selectedCategories.value) {
        if (key.startsWith(`${variantIndex}_`)) {
            const parts = key.split('_')
            const parentId = parseInt(parts[1])
            const parent = getParentById(parentId)
            if (!parent) continue

            const selected = selectedCategories.value[key]
            if (parent.is_multiselect && Array.isArray(selected)) {
                for (const childId of selected) {
                    const child = parent.children?.find(c => c.id === childId)
                    if (child) {
                        total += parent.base_points + child.points
                    }
                }
            } else if (selected) {
                const child = parent.children?.find(c => c.id === selected)
                if (child) {
                    total += parent.base_points + child.points
                }
            }
        }
    }
    return formatPoints(total)
}

// Общая сумма баллов
const totalPoints = computed(() => {
    let total = 0
    for (let i = 0; i < form.variants.length; i++) {
        total += parseFloat(getVariantTotal(i) || 0)
    }
    return formatPoints(total)
})

// Проверка, есть ли выбранные категории
const hasAnySelectedCategories = computed(() => {
    for (let i = 0; i < form.variants.length; i++) {
        if (form.variants[i].category_ids?.length > 0) {
            return true
        }
    }
    return false
})

// Добавить вариант
const addVariant = () => {
    form.variants.push({
        name: '',
        category_ids: []
    })
}

// Удалить вариант
const removeVariant = (index) => {
    form.variants.splice(index, 1)
    // Очищаем выбранные категории для удаленного варианта
    for (const key in selectedCategories.value) {
        if (key.startsWith(`${index}_`)) {
            delete selectedCategories.value[key]
        }
    }
    // Перенумеровываем оставшиеся ключи
    const newSelected = {}
    for (const key in selectedCategories.value) {
        const parts = key.split('_')
        const oldIndex = parseInt(parts[0])
        if (oldIndex > index) {
            const newKey = `${oldIndex - 1}_${parts[1]}`
            newSelected[newKey] = selectedCategories.value[key]
        } else {
            newSelected[key] = selectedCategories.value[key]
        }
    }
    selectedCategories.value = newSelected
}

// Отправка формы
const submit = () => {
    if (!hasAnySelectedCategories.value) {
        alert('Пожалуйста, выберите выполненные работы хотя бы для одного варианта')
        return
    }

    const selectedManager = props.managers.find(m => m.id === form.manager_id)

    const data = {
        request_number: form.request_number,
        counterparty: form.counterparty,
        manager_name: selectedManager ? selectedManager.full_name : '',
        variants: form.variants.map((variant, index) => ({
            name: variant.name || `Вариант ${index + 1}`,
            category_ids: variant.category_ids,
            sort_order: index
        }))
    }

    console.log('Отправляемые данные:', data)

    // Используем axios вместо Inertia для прямого POST запроса
    axios.post(route('scoring.requests.store', props.sheet.id), data, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
        .then(response => {
            console.log('Успех!', response.data)
            if (response.data.success) {
                emit('saved')
                resetForm()
                // Показываем уведомление об успехе
                alert('Заявка успешно добавлена!')
            } else {
                alert('Ошибка: ' + (response.data.error || 'Неизвестная ошибка'))
            }
        })
        .catch(error => {
            console.error('Ошибка сохранения:', error)
            if (error.response && error.response.data && error.response.data.error) {
                alert('Ошибка: ' + error.response.data.error)
            } else {
                alert('Ошибка при сохранении заявки')
            }
        })
}

// Сброс формы
const resetForm = () => {
    form.reset()
    form.variants = [{ name: '', category_ids: [] }]
    selectedCategories.value = {}
    accordionState.value = {}
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

/* Кастомный скроллбар */
.overflow-y-auto::-webkit-scrollbar {
    width: 8px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.dark .overflow-y-auto::-webkit-scrollbar-track {
    background: #1f2937;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb {
    background: #4b5563;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #6b7280;
}

/* Плавные переходы */
.rotate-90 {
    transform: rotate(90deg);
}

.transition {
    transition: all 0.2s ease;
}
</style>
