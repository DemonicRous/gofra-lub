<!-- resources/js/Pages/Scoring/Index.vue -->
<template>
    <AppLayout>
        <Head title="Ведомости баллов" />

        <div class="container mx-auto px-4 py-8">
            <!-- Заголовок -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Ведомости баллов
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Управление вашими месячными ведомостями
                </p>
            </div>

            <!-- Текущая ведомость -->
            <div v-if="currentSheet" class="mb-8">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <div>
                                <p class="text-blue-100 text-sm">Текущий месяц</p>
                                <h2 class="text-2xl font-bold text-white">
                                    {{ formatMonth(currentSheet.period_date) }}
                                </h2>
                                <div class="mt-2 flex items-center gap-2">
                                    <span :class="getStatusBadgeClass(currentSheet.status)" class="px-2 py-1 text-xs rounded-full">
                                        {{ getStatusName(currentSheet.status) }}
                                    </span>
                                    <span class="text-white/80 text-sm">
                                        {{ currentSheet.total_points }} баллов
                                    </span>
                                </div>
                            </div>
                            <Link
                                :href="route('scoring.sheets.show', currentSheet.id)"
                                class="bg-white/20 hover:bg-white/30 text-white px-6 py-2 rounded-lg transition"
                            >
                                {{ currentSheet.status === 'draft' ? 'Заполнить ведомость →' : 'Просмотреть ведомость →' }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- История ведомостей -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        История ведомостей
                    </h2>
                </div>

                <div v-if="sheets.data && sheets.data.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="sheet in sheets.data"
                        :key="sheet.id"
                        class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                    >
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                        {{ formatMonth(sheet.period_date) }}
                                    </h3>
                                    <span :class="getStatusBadgeClass(sheet.status)" class="px-2 py-0.5 text-xs rounded-full">
                                        {{ getStatusName(sheet.status) }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                    <span>✅ {{ sheet.total_points }} баллов</span>
                                    <span v-if="sheet.confirmed_at">📅 Подтверждена: {{ formatDate(sheet.confirmed_at) }}</span>
                                    <span v-if="sheet.approved_by">✓ Утверждена</span>
                                </div>
                            </div>
                            <Link
                                :href="route('scoring.sheets.show', sheet.id)"
                                class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition"
                            >
                                Просмотр →
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400">Нет ведомостей</p>
                </div>

                <!-- Пагинация -->
                <div v-if="sheets.links && sheets.links.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <Pagination :links="sheets.links" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/UI/Pagination.vue'

const props = defineProps({
    sheets: {
        type: Object,
        required: true
    },
    currentSheet: {
        type: Object,
        default: null
    }
})

const formatMonth = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('ru-RU', { month: 'long', year: 'numeric' })
}

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
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
</script>
