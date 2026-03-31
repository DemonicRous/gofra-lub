<!-- resources/js/Pages/Scoring/Sheet.vue -->
<template>
    <AppLayout>
        <Head :title="`Ведомость - ${formatMonth(sheet.period_date)}`" />

        <div class="container mx-auto px-4 py-8">
            <!-- Заголовок и навигация -->
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <div>
                    <Link :href="route('scoring.index')" class="inline-flex items-center gap-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Назад к списку
                    </Link>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                        Ведомость за {{ formatMonth(sheet.period_date) }}
                    </h1>
                    <div class="flex items-center gap-2 mt-1">
                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                            <div class="w-6 h-6 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white text-xs font-medium">
                                {{ sheet.user?.first_name?.charAt(0) || 'U' }}
                            </div>
                            <span>{{ sheet.user?.full_name || sheet.user?.short_name || sheet.user?.name || '—' }}</span>
                        </div>
                        <span class="text-gray-400 dark:text-gray-600">•</span>
                        <span class="text-gray-500 dark:text-gray-400">
                            {{ getDepartmentName(sheet.user?.scoring_department) }}
                        </span>
                    </div>
                </div>

                <div class="flex gap-3">
                    <!-- Кнопка экспорта -->
                    <button
                        @click="exportSheet"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 flex items-center gap-2 text-gray-700 dark:text-gray-300"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Экспорт
                    </button>

                    <!-- Кнопка подтверждения -->
                    <button
                        v-if="isEditable && sheet.status === 'draft'"
                        @click="confirmSheet"
                        class="px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-xl transition-all duration-200 shadow-md hover:shadow-lg flex items-center gap-2 disabled:opacity-50"
                        :disabled="confirming"
                    >
                        <svg v-if="confirming" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Подтвердить ведомость</span>
                    </button>
                </div>
            </div>

            <!-- Статус и итоги -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Статус</p>
                            <p class="text-lg font-semibold">
                                <span :class="getStatusBadgeClass(sheet.status)" class="px-3 py-1 text-sm rounded-full">
                                    {{ getStatusName(sheet.status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Всего баллов</p>
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ sheet.total_points }}</p>
                        </div>
                    </div>
                    <div v-if="sheet.confirmed_at" class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Дата подтверждения</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ formatDateTime(sheet.confirmed_at) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Заявки -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-wrap items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Заявки
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ sheet.requests?.length || 0 }})</span>
                    </h2>
                    <button
                        v-if="isEditable && sheet.status === 'draft'"
                        @click="openAddEntryModal"
                        class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-xl transition-all duration-200 shadow-md hover:shadow-lg flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Добавить заявку
                    </button>
                </div>

                <div v-if="sheet.requests && sheet.requests.length > 0" class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div v-for="request in sheet.requests" :key="request.id" class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-all duration-200">
                        <!-- Заголовок заявки -->
                        <div class="flex flex-wrap justify-between items-start gap-4 mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 flex-wrap mb-2">
                                    <span class="text-lg font-semibold text-gray-900 dark:text-white">
                                        Заявка №{{ request.request_number || '—' }}
                                    </span>
                                    <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-full">
                                        {{ formatDate(request.created_at) }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-3 text-sm">
                                    <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <span>{{ request.counterparty || 'Контрагент не указан' }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span>{{ request.manager_name || '—' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    {{ request.total_points || 0 }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">баллов</div>
                            </div>
                        </div>

                        <!-- Варианты -->
                        <div v-if="request.variants && request.variants.length > 0" class="ml-6 space-y-3">
                            <div v-for="variant in request.variants" :key="variant.id" class="border-l-2 border-blue-200 dark:border-blue-800 pl-4">
                                <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                            <svg class="w-3 h-3 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                        </div>
                                        <span class="font-medium text-gray-700 dark:text-gray-300">
                                            {{ variant.name || 'Вариант' }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ variant.entries?.length || 0 }} работ
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                                            +{{ variant.total_points || 0 }}
                                        </span>
                                        <button
                                            v-if="isEditable && sheet.status === 'draft'"
                                            @click="editVariant(variant.id)"
                                            class="p-1.5 text-blue-500 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition"
                                            title="Редактировать вариант"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            v-if="isEditable && sheet.status === 'draft'"
                                            @click="deleteVariant(variant.id)"
                                            class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"
                                            title="Удалить вариант"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="space-y-1.5 mt-2">
                                    <div v-for="entry in variant.entries" :key="entry.id" class="flex items-center justify-between text-sm py-1">
                                        <div class="flex items-center gap-2">
                                            <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                                            <span class="text-gray-600 dark:text-gray-400">
                                                {{ entry.category?.parent?.name }} → {{ entry.category?.name }}
                                            </span>
                                        </div>
                                        <span class="text-blue-600 dark:text-blue-400 font-medium">
                                            +{{ entry.points }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="ml-6 text-sm text-gray-500 dark:text-gray-400 italic">
                            Нет вариантов
                        </div>

                        <!-- Кнопки действий для заявки -->
                        <div v-if="isEditable && sheet.status === 'draft'" class="mt-4 flex justify-end gap-2">
                            <button
                                @click="editRequest(request.id)"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 dark:text-blue-300 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 rounded-lg transition-all duration-200"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Редактировать
                            </button>
                            <button
                                @click="deleteRequest(request.id)"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 dark:text-red-300 dark:bg-red-900/30 dark:hover:bg-red-900/50 rounded-lg transition-all duration-200"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Удалить
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="p-12 text-center">
                    <div class="w-20 h-20 mx-auto bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400">Нет заявок</p>
                    <p v-if="isEditable && sheet.status === 'draft'" class="text-gray-400 dark:text-gray-500 text-sm mt-2">
                        Нажмите "Добавить заявку" чтобы начать
                    </p>
                </div>
            </div>
        </div>

        <!-- Модальные окна -->
        <EntryFormModal
            :show="showAddModal"
            :sheet="sheet"
            :categories="categories"
            :managers="managers"
            @close="closeAddModal"
            @saved="onEntrySaved"
        />

        <EditRequestModal
            v-if="editingRequest"
            :show="showEditModal"
            :request="editingRequest"
            :sheet="sheet"
            :categories="categories"
            :managers="managers"
            @close="closeEditModal"
            @saved="onEntrySaved"
        />

        <EditVariantModal
            v-if="editingVariant"
            :show="showEditVariantModal"
            :variant="editingVariant"
            :request="currentRequest"
            :sheet="sheet"
            :categories="categories"
            @close="closeEditVariantModal"
            @saved="onEntrySaved"
        />
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import EditVariantModal from "@/Components/Scoring/EditVariantModal.vue";
import EditRequestModal from "@/Components/Scoring/EditRequestModal.vue";
import EntryFormModal from "@/Components/Scoring/EntryFormModal.vue";

const props = defineProps({
    sheet: {
        type: Object,
        required: true
    },
    isEditable: {
        type: Boolean,
        default: false
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

const showAddModal = ref(false)
const showEditModal = ref(false)
const showEditVariantModal = ref(false)
const confirming = ref(false)
const editingRequest = ref(null)
const editingVariant = ref(null)
const currentRequest = ref(null)

const formatMonth = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('ru-RU', { month: 'long', year: 'numeric' })
}

const formatDateTime = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleString('ru-RU')
}

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', year: 'numeric' })
}

const getDepartmentName = (department) => {
    const map = {
        constructor: 'Отдел конструкторов',
        designer: 'Отдел дизайнеров'
    }
    return map[department] || department
}

const getStatusName = (status) => {
    const map = {
        draft: 'Черновик',
        confirmed: 'Подтверждена',
        approved: 'Утверждена'
    }
    return map[status] || status
}

const getStatusBadgeClass = (status) => {
    const map = {
        draft: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        confirmed: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        approved: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
    }
    return map[status] || ''
}

const exportSheet = () => {
    window.location.href = route('scoring.export.sheet', props.sheet.id)
}

const openAddEntryModal = () => {
    showAddModal.value = true
}

const closeAddModal = () => {
    showAddModal.value = false
}

const editRequest = (requestId) => {
    const request = props.sheet.requests?.find(r => r.id === requestId)
    if (request) {
        editingRequest.value = request
        showEditModal.value = true
    }
}

const closeEditModal = () => {
    showEditModal.value = false
    editingRequest.value = null
}

const editVariant = (variantId) => {
    for (const request of props.sheet.requests || []) {
        const variant = request.variants?.find(v => v.id === variantId)
        if (variant) {
            editingVariant.value = variant
            currentRequest.value = request
            showEditVariantModal.value = true
            break
        }
    }
}

const closeEditVariantModal = () => {
    showEditVariantModal.value = false
    editingVariant.value = null
    currentRequest.value = null
}

const onEntrySaved = () => {
    closeAddModal()
    closeEditModal()
    closeEditVariantModal()
    router.reload()
}

const confirmSheet = () => {
    if (confirm('Подтвердить ведомость? После подтверждения вы не сможете редактировать записи.')) {
        confirming.value = true
        router.post(route('scoring.sheets.confirm', props.sheet.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                confirming.value = false
            },
            onError: () => {
                confirming.value = false
            }
        })
    }
}

const deleteRequest = (requestId) => {
    if (confirm('Удалить эту заявку? Все варианты и баллы будут удалены.')) {
        router.delete(route('scoring.requests.destroy', requestId), {
            preserveScroll: true,
            onSuccess: () => {
                router.reload()
            }
        })
    }
}

const deleteVariant = (variantId) => {
    if (confirm('Удалить этот вариант? Все баллы будут удалены.')) {
        router.delete(route('scoring.variants.destroy', variantId), {
            preserveScroll: true,
            onSuccess: () => {
                router.reload()
            }
        })
    }
}
</script>

<style scoped>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin { animation: spin 1s linear infinite; }
</style>
