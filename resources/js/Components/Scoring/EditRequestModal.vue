<!-- resources/js/Components/Scoring/EditRequestModal.vue -->
<template>
    <div v-if="show" class="fixed inset-0 bg-black/60 dark:bg-black/80 flex items-center justify-center z-50 p-4" @click.self="close">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 px-6 py-4 sticky top-0 z-10 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-white">Редактирование заявки</h2>
                    </div>
                    <button @click="close" class="text-white hover:text-gray-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <form @submit.prevent="submit" class="p-6 space-y-6">
                <!-- Информация о заявке -->
                <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Информация о заявке
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Номер заявки
                            </label>
                            <input
                                v-model="form.request_number"
                                type="text"
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all"
                                placeholder="Например: 4854"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Контрагент
                            </label>
                            <input
                                v-model="form.counterparty"
                                type="text"
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all"
                                placeholder="Например: ООО БМГ"
                            />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Менеджер
                            </label>
                            <select
                                v-model="form.manager_id"
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all"
                            >
                                <option value="">Выберите менеджера</option>
                                <option v-for="manager in managers" :key="manager.id" :value="manager.id">
                                    {{ manager.full_name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Варианты конструкции -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Варианты конструкции
                        </h3>
                        <button
                            type="button"
                            @click="addVariant"
                            class="px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-800/50 transition-all text-sm flex items-center gap-1"
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
                            class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden"
                        >
                            <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 flex items-center justify-between">
                                <div class="flex items-center gap-3 flex-1">
                                    <span class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-medium flex items-center justify-center">
                                        {{ vIndex + 1 }}
                                    </span>
                                    <input
                                        v-model="variant.name"
                                        type="text"
                                        class="flex-1 max-w-xs px-3 py-1.5 text-sm border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="Название варианта"
                                    />
                                </div>
                                <button
                                    type="button"
                                    @click="removeVariant(vIndex)"
                                    class="text-red-500 hover:text-red-700 p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition"
                                    v-if="form.variants.length > 1"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>

                            <div class="p-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                    Выполненные работы <span class="text-red-500">*</span>
                                </label>

                                <div v-if="categories.length === 0" class="text-center py-8 text-gray-500 bg-gray-50 dark:bg-gray-700/30 rounded-xl">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <p>Нет доступных категорий</p>
                                </div>

                                <div v-for="parent in categories" :key="parent.id" class="mb-3 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                                    <div
                                        @click="toggleAccordion(vIndex, parent.id)"
                                        class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 flex items-center justify-between cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                                    >
                                        <div class="flex items-center gap-3">
                                            <svg class="w-4 h-4 text-gray-500 transition-transform" :class="{ 'rotate-90': isAccordionOpen(vIndex, parent.id) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

                                    <div v-show="isAccordionOpen(vIndex, parent.id)" class="divide-y divide-gray-100 dark:divide-gray-700">
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
                                                        class="w-4 h-4 text-blue-600 rounded"
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

                                <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 -mx-4 px-4 py-3">
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
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl p-5">
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Общий итог:</span>
                        <span class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                            {{ totalPoints }}
                        </span>
                    </div>
                </div>

                <!-- Кнопки -->
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
                        class="px-5 py-2.5 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-xl transition-all font-medium shadow-md hover:shadow-lg disabled:opacity-50"
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
import axios from 'axios'

const props = defineProps({
    show: Boolean,
    request: Object,
    sheet: Object,
    categories: Array,
    managers: Array
})

const emit = defineEmits(['close', 'saved', 'updated'])

const accordionState = ref({})
const selectedCategories = ref({})
const saving = ref(false)

const form = ref({
    request_number: '',
    counterparty: '',
    manager_id: '',
    variants: []
})

const formatPoints = (value) => {
    if (value === null || value === undefined) return '0'
    const num = Number(value)
    if (isNaN(num)) return '0'
    if (num % 1 === 0) return num.toString()
    return num.toFixed(2).replace(/\.?0+$/, '')
}

// Инициализация формы данными заявки
watch(() => props.request, (request) => {
    if (request && props.categories) {
        const manager = props.managers?.find(m => m.full_name === request.manager_name)

        form.value.request_number = request.request_number || ''
        form.value.counterparty = request.counterparty || ''
        form.value.manager_id = manager?.id || ''

        if (request.variants) {
            form.value.variants = request.variants.map(v => ({
                id: v.id,
                name: v.name || '',
                category_ids: v.entries?.map(e => e.category_id) || []
            }))

            selectedCategories.value = {}
            request.variants.forEach((variant, vIndex) => {
                variant.entries?.forEach(entry => {
                    const category = props.categories.find(c =>
                        c.children?.some(child => child.id === entry.category_id)
                    )
                    if (category) {
                        const key = `${vIndex}_${category.id}`
                        if (!selectedCategories.value[key]) {
                            selectedCategories.value[key] = category.is_multiselect ? [] : null
                        }
                        if (category.is_multiselect) {
                            if (!selectedCategories.value[key].includes(entry.category_id)) {
                                selectedCategories.value[key].push(entry.category_id)
                            }
                        } else {
                            selectedCategories.value[key] = entry.category_id
                        }
                    }
                })
            })
        }
    }
}, { immediate: true, deep: true })

const toggleAccordion = (variantIndex, parentId) => {
    const key = `${variantIndex}_${parentId}`
    accordionState.value[key] = !accordionState.value[key]
}

const isAccordionOpen = (variantIndex, parentId) => {
    const key = `${variantIndex}_${parentId}`
    return accordionState.value[key] || false
}

const getParentById = (parentId) => {
    return props.categories?.find(cat => cat.id === parentId)
}

const isSelected = (variantIndex, parentId, childId) => {
    const key = `${variantIndex}_${parentId}`
    if (!selectedCategories.value[key]) return false

    const parent = getParentById(parentId)
    if (parent?.is_multiselect) {
        return selectedCategories.value[key].includes(childId)
    }
    return selectedCategories.value[key] === childId
}

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

    updateVariantCategoryIds(variantIndex)
}

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
    if (form.value.variants[variantIndex]) {
        form.value.variants[variantIndex].category_ids = ids
    }
}

const getVariantTotal = (variantIndex) => {
    let total = 0
    for (const key in selectedCategories.value) {
        if (key.startsWith(`${variantIndex}_`)) {
            const parentId = parseInt(key.split('_')[1])
            const parent = getParentById(parentId)
            if (!parent) continue

            const selected = selectedCategories.value[key]
            if (parent.is_multiselect && Array.isArray(selected)) {
                for (const childId of selected) {
                    const child = parent.children?.find(c => c.id === childId)
                    if (child) total += parent.base_points + child.points
                }
            } else if (selected) {
                const child = parent.children?.find(c => c.id === selected)
                if (child) total += parent.base_points + child.points
            }
        }
    }
    return formatPoints(total)
}

const totalPoints = computed(() => {
    let total = 0
    for (let i = 0; i < form.value.variants.length; i++) {
        total += parseFloat(getVariantTotal(i) || 0)
    }
    return formatPoints(total)
})

const addVariant = () => {
    form.value.variants.push({ name: '', category_ids: [] })
}

const removeVariant = (index) => {
    form.value.variants.splice(index, 1)
    for (const key in selectedCategories.value) {
        if (key.startsWith(`${index}_`)) {
            delete selectedCategories.value[key]
        }
    }
    const newSelected = {}
    for (const key in selectedCategories.value) {
        const parts = key.split('_')
        const oldIndex = parseInt(parts[0])
        if (oldIndex > index) {
            newSelected[`${oldIndex - 1}_${parts[1]}`] = selectedCategories.value[key]
        } else {
            newSelected[key] = selectedCategories.value[key]
        }
    }
    selectedCategories.value = newSelected
}

const submit = async () => {
    if (!form.value.variants.some(v => v.category_ids?.length > 0)) {
        alert('Пожалуйста, выберите выполненные работы хотя бы для одного варианта')
        return
    }

    const selectedManager = props.managers?.find(m => m.id === form.value.manager_id)

    const data = {
        request_number: form.value.request_number,
        counterparty: form.value.counterparty,
        manager_name: selectedManager?.full_name || '',
        variants: form.value.variants.map((variant, index) => ({
            id: variant.id,
            name: variant.name || `Вариант ${index + 1}`,
            category_ids: variant.category_ids,
            sort_order: index
        }))
    }

    saving.value = true

    try {
        const response = await axios.put(`/scoring/requests/${props.request.id}`, data, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })

        if (response.data.success) {
            // Отправляем обновлённые данные в родительский компонент
            emit('updated', response.data.request)
            emit('saved')
            // Закрываем модальное окно БЕЗ перезагрузки страницы
            emit('close')
        } else {
            alert('Ошибка: ' + (response.data.error || 'Неизвестная ошибка'))
        }
    } catch (error) {
        console.error('Ошибка:', error)
        const errorMsg = error.response?.data?.error || error.message || 'Неизвестная ошибка'
        alert('Ошибка при сохранении заявки: ' + errorMsg)
    } finally {
        saving.value = false
    }
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
