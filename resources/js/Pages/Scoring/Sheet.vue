<!-- resources/js/Pages/Scoring/Sheet.vue -->
<template>
    <AppLayout>
        <Head :title="`Ведомость - ${formatMonth(sheet.period_date)}`" />

        <div class="container mx-auto px-4 py-8">
            <!-- Заголовок и навигация -->
            <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
                <div>
                    <Link :href="route('scoring.index')" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Назад к списку
                    </Link>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Ведомость за {{ formatMonth(sheet.period_date) }}
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        {{ sheet.user.full_name }} • {{ getDepartmentName(sheet.user.scoring_department) }}
                    </p>
                </div>

                <div class="flex gap-3">
                    <!-- Кнопка экспорта -->
                    <a
                        :href="route('scoring.export.sheet', sheet.id)"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Экспорт
                    </a>

                    <!-- Кнопка подтверждения -->
                    <button
                        v-if="isEditable && sheet.status === 'draft'"
                        @click="confirmSheet"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition flex items-center gap-2"
                        :disabled="confirming"
                    >
                        <svg v-if="confirming" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span v-else>Подтвердить ведомость</span>
                    </button>
                </div>
            </div>

            <!-- Статус и итоги -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Статус</p>
                        <p class="text-lg font-semibold">
                            <span :class="getStatusBadgeClass(sheet.status)" class="px-2 py-1 text-sm rounded-full">
                                {{ getStatusName(sheet.status) }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Всего баллов</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ sheet.total_points }}</p>
                    </div>
                    <div v-if="sheet.confirmed_at">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Дата подтверждения</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatDateTime(sheet.confirmed_at) }}</p>
                    </div>
                </div>
            </div>

            <!-- Записи -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Выполненные работы
                    </h2>
                    <button
                        v-if="isEditable && sheet.status === 'draft'"
                        @click="openAddEntryModal"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Добавить запись
                    </button>
                </div>

                <div v-if="sheet.entries && sheet.entries.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="entry in sheet.entries"
                        :key="entry.id"
                        class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                    >
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <!-- Основная категория -->
                                <div class="mb-2">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-medium">
                                            {{ entry.category.name }}
                                        </span>
                                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400">
                                            +{{ entry.points }} баллов
                                        </span>
                                    </div>
                                    <div v-if="entry.metadata?.selected_children_names?.length" class="mt-2">
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Выбранные подкатегории:</div>
                                        <div class="flex flex-wrap gap-1">
                                            <span
                                                v-for="childName in entry.metadata.selected_children_names"
                                                :key="childName"
                                                class="px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400"
                                            >
                                                {{ childName }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Информация о заявке -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-600 dark:text-gray-400 mt-3">
                                    <div v-if="entry.request_number">
                                        📋 Заявка №{{ entry.request_number }}
                                    </div>
                                    <div v-if="entry.counterparty">
                                        🏢 {{ entry.counterparty }}
                                    </div>
                                    <div v-if="entry.manager_name">
                                        👤 {{ entry.manager_name }}
                                    </div>
                                    <div v-if="entry.quantity > 1">
                                        🔢 Количество: {{ entry.quantity }}
                                    </div>
                                </div>

                                <!-- Детализация баллов -->
                                <div v-if="entry.metadata" class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                    <span>Базовые баллы: {{ entry.metadata.base_points }} × {{ entry.quantity }} = {{ entry.metadata.base_points * entry.quantity }}</span>
                                    <span v-if="entry.metadata.additional_points > 0" class="ml-2">
                                        + Дополнительные: {{ entry.metadata.additional_points }}
                                    </span>
                                </div>

                                <!-- Варианты конструкции -->
                                <div v-if="entry.variants && entry.variants.length > 0" class="mt-3">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Варианты конструкции:</p>
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            v-for="variant in entry.variants"
                                            :key="variant.id"
                                            class="px-2 py-1 text-xs rounded-full bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300"
                                        >
                                            {{ variant.name }}
                                        </span>
                                    </div>
                                </div>

                                <div v-if="entry.notes" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    📝 {{ entry.notes }}
                                </div>
                            </div>

                            <button
                                v-if="isEditable && sheet.status === 'draft'"
                                @click="deleteEntry(entry.id)"
                                class="text-red-500 hover:text-red-700 transition ml-2"
                                title="Удалить запись"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400">Нет записей</p>
                    <p v-if="isEditable && sheet.status === 'draft'" class="text-gray-400 dark:text-gray-500 text-sm mt-2">
                        Нажмите "Добавить запись" чтобы начать
                    </p>
                </div>
            </div>
        </div>

        <!-- Модальное окно добавления записи -->
        <EntryFormModal
            :show="showAddModal"
            :sheet="sheet"
            :categories="categories"
            :managers="managers"
            @close="closeAddModal"
            @saved="onEntrySaved"
        />
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import EntryFormModal from "@/Components/Scoring/EntryFormModal.vue";

const page = usePage()
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
const confirming = ref(false)

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
    return new Date(date).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
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

const openAddEntryModal = () => {
    showAddModal.value = true
}

const closeAddModal = () => {
    showAddModal.value = false
}

const onEntrySaved = () => {
    closeAddModal()
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

const deleteEntry = (entryId) => {
    if (confirm('Удалить эту запись?')) {
        router.delete(route('scoring.entries.destroy', entryId), {
            preserveScroll: true,
            onSuccess: () => {
                router.reload()
            }
        })
    }
}
</script>
