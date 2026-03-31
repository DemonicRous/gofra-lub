<!-- resources/js/Pages/Scoring/components/EditVariantModal.vue -->
<template>
    <div v-if="show" class="fixed inset-0 bg-black/60 dark:bg-black/80 flex items-center justify-center z-50 p-4" @click.self="close">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 dark:from-purple-600 dark:to-purple-700 px-6 py-4 sticky top-0 z-10 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-white">Редактирование варианта</h2>
                    </div>
                    <button @click="close" class="text-white hover:text-gray-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <p class="text-purple-100 text-sm mt-1 ml-13">Заявка №{{ request?.request_number || '—' }}</p>
            </div>

            <form @submit.prevent="submit" class="p-6 space-y-6">
                <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Название варианта
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all"
                        placeholder="Например: Стандартный вариант"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Выполненные работы <span class="text-red-500">*</span>
                    </label>

                    <div v-if="categories.length === 0" class="text-center py-12 text-gray-500 bg-gray-50 dark:bg-gray-700/30 rounded-xl">
                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p>Нет доступных категорий</p>
                    </div>

                    <div v-for="parent in categories" :key="parent.id" class="mb-3 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                        <div
                            @click="toggleAccordion(parent.id)"
                            class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 flex items-center justify-between cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                        >
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-gray-500 transition-transform" :class="{ 'rotate-90': isAccordionOpen(parent.id) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                <span class="font-medium text-gray-900 dark:text-white">{{ parent.name }}</span>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                    {{ formatPoints(parent.base_points) }} баллов
                                </span>
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ parent.is_multiselect ? '✓ Можно выбрать несколько' : '◉ Выберите один' }}
                            </div>
                        </div>

                        <div v-show="isAccordionOpen(parent.id)" class="divide-y divide-gray-100 dark:divide-gray-700">
                            <div
                                v-for="child in parent.children"
                                :key="child.id"
                                class="p-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer transition"
                                :class="{ 'bg-purple-50 dark:bg-purple-900/20': isSelected(parent.id, child.id) }"
                                @click.stop="toggleCategory(parent, child)"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3 flex-1">
                                        <input
                                            :type="parent.is_multiselect ? 'checkbox' : 'radio'"
                                            :checked="isSelected(parent.id, child.id)"
                                            @change.stop="toggleCategory(parent, child)"
                                            class="w-4 h-4 text-purple-600 rounded"
                                            :class="{ 'rounded-full': !parent.is_multiselect }"
                                        />
                                        <div>
                                            <div class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ child.name }}</div>
                                            <div class="text-xs text-gray-500">
                                                +{{ formatPoints(child.points) }} баллов
                                                <span class="text-purple-600 ml-1">
                                                    (итого: {{ formatPoints(parent.base_points + child.points) }})
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-sm font-semibold text-purple-600">
                                        +{{ formatPoints(parent.base_points + child.points) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Детализация баллов -->
                <div v-if="selectedCategoriesList.length > 0" class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Детализация баллов
                    </h4>
                    <div class="space-y-2">
                        <div v-for="item in selectedCategoriesList" :key="item.id" class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">{{ item.parent_name }} → {{ item.child_name }}</span>
                            <span class="font-medium text-purple-600 dark:text-purple-400">{{ formatPoints(item.points) }} баллов</span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-600 pt-2 mt-2 flex justify-between font-semibold">
                            <span>Итого:</span>
                            <span class="text-purple-600 dark:text-purple-400">{{ totalPoints }} баллов</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-xl p-5">
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Общий итог:</span>
                        <span class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                            {{ totalPoints }}
                        </span>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button
                        type="button"
                        @click="close"
                        class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl transition-all font-medium"
                    >
                        Отмена
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white rounded-xl transition-all font-medium shadow-md hover:shadow-lg disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        <svg v-if="form.processing" class="animate-spin inline w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Сохранение...' : 'Сохранить изменения' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    show: Boolean,
    variant: Object,
    request: Object,
    sheet: Object,
    categories: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['close', 'saved'])

const accordionState = ref({})
// Структура: { parentId: selectedChildId или [selectedChildIds] }
const selectedByParent = ref({})

const form = useForm({
    name: '',
    category_ids: []
})

// Функция обновления массива ID для отправки (объявляем ДО watch)
const updateFormCategoryIds = () => {
    const ids = []
    for (const parentId in selectedByParent.value) {
        const value = selectedByParent.value[parentId]
        if (Array.isArray(value)) {
            ids.push(...value)
        } else if (value) {
            ids.push(value)
        }
    }
    form.category_ids = ids
}

// Инициализация данными варианта
watch(() => props.variant, (variant) => {
    if (variant && props.categories.length > 0) {
        form.name = variant.name || ''

        // Сбрасываем выбранные категории
        selectedByParent.value = {}

        // Группируем записи по родительским категориям
        const entries = variant.entries || []

        entries.forEach(entry => {
            // Находим родительскую категорию для этой записи
            for (const parent of props.categories) {
                const child = parent.children?.find(c => c.id === entry.category_id)
                if (child) {
                    if (parent.is_multiselect) {
                        // Множественный выбор - массив ID
                        if (!selectedByParent.value[parent.id]) {
                            selectedByParent.value[parent.id] = []
                        }
                        if (!selectedByParent.value[parent.id].includes(entry.category_id)) {
                            selectedByParent.value[parent.id].push(entry.category_id)
                        }
                    } else {
                        // Одиночный выбор - один ID
                        selectedByParent.value[parent.id] = entry.category_id
                    }
                    break
                }
            }
        })

        // Обновляем form.category_ids для отправки
        updateFormCategoryIds()
    }
}, { immediate: true, deep: true })

// Проверка выбранной категории
const isSelected = (parentId, childId) => {
    const selected = selectedByParent.value[parentId]
    if (!selected) return false

    const parent = props.categories.find(p => p.id === parentId)
    if (parent?.is_multiselect) {
        return Array.isArray(selected) && selected.includes(childId)
    } else {
        return selected === childId
    }
}

// Переключение категории
const toggleCategory = (parent, child) => {
    const parentId = parent.id

    if (parent.is_multiselect) {
        // Множественный выбор
        if (!selectedByParent.value[parentId]) {
            selectedByParent.value[parentId] = []
        }

        const index = selectedByParent.value[parentId].indexOf(child.id)
        if (index === -1) {
            selectedByParent.value[parentId].push(child.id)
        } else {
            selectedByParent.value[parentId].splice(index, 1)
        }

        // Если массив пуст, удаляем ключ
        if (selectedByParent.value[parentId].length === 0) {
            delete selectedByParent.value[parentId]
        }
    } else {
        // Одиночный выбор
        if (selectedByParent.value[parentId] === child.id) {
            delete selectedByParent.value[parentId]
        } else {
            selectedByParent.value[parentId] = child.id
        }
    }

    // Обновляем массив ID для отправки
    updateFormCategoryIds()
}

// Получение списка выбранных категорий для детализации
const selectedCategoriesList = computed(() => {
    const result = []
    for (const parentId in selectedByParent.value) {
        const parent = props.categories.find(p => p.id === parseInt(parentId))
        if (!parent) continue

        const selected = selectedByParent.value[parentId]

        if (Array.isArray(selected)) {
            // Множественный выбор
            for (const childId of selected) {
                const child = parent.children?.find(c => c.id === childId)
                if (child) {
                    result.push({
                        id: child.id,
                        parent_name: parent.name,
                        child_name: child.name,
                        base_points: parent.base_points,
                        additional_points: child.points,
                        points: parent.base_points + child.points
                    })
                }
            }
        } else if (selected) {
            // Одиночный выбор
            const child = parent.children?.find(c => c.id === selected)
            if (child) {
                result.push({
                    id: child.id,
                    parent_name: parent.name,
                    child_name: child.name,
                    base_points: parent.base_points,
                    additional_points: child.points,
                    points: parent.base_points + child.points
                })
            }
        }
    }
    return result
})

// Форматирование чисел
const formatPoints = (value) => {
    if (value === null || value === undefined) return '0'
    const num = Number(value)
    if (isNaN(num)) return '0'
    if (num % 1 === 0) return num.toString()
    return num.toFixed(2).replace(/\.?0+$/, '')
}

// Общая сумма баллов
const totalPoints = computed(() => {
    let total = 0
    for (const item of selectedCategoriesList.value) {
        total += item.points
    }
    return formatPoints(total)
})

// Управление аккордеоном
const toggleAccordion = (parentId) => {
    accordionState.value[parentId] = !accordionState.value[parentId]
}

const isAccordionOpen = (parentId) => {
    return accordionState.value[parentId] || false
}

// Отправка формы
const submit = () => {
    if (selectedCategoriesList.value.length === 0) {
        alert('Пожалуйста, выберите хотя бы одну работу')
        return
    }

    form.put(route('scoring.variants.update', props.variant.id), {
        data: {
            name: form.name,
            category_ids: form.category_ids
        },
        onSuccess: () => emit('saved')
    })
}

const close = () => emit('close')
</script>

<style scoped>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin { animation: spin 1s linear infinite; }
.rotate-90 { transform: rotate(90deg); }
</style>
