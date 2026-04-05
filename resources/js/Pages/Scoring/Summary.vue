<template>
    <AppLayout>
        <Head title="Сводка по баллам" />

        <div class="container mx-auto px-4 py-8">
            <!-- Заголовок и фильтры -->
            <div class="flex items-center justify-between flex-wrap gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Сводка по баллам
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Общая информация по подотделам
                    </p>
                </div>
                <div class="flex gap-3">
                    <select
                        v-model="selectedMonth"
                        @change="changeMonth"
                        class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    >
                        <option v-for="month in months" :key="month.value" :value="month.value">
                            {{ month.label }}
                        </option>
                    </select>
                    <a
                        :href="route('scoring.export.summary', { date: selectedMonth })"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Экспорт в Excel
                    </a>
                </div>
            </div>

            <!-- Общая статистика -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-6 text-white">
                    <p class="text-blue-100 text-sm">Всего баллов</p>
                    <p class="text-3xl font-bold">{{ totalPoints }}</p>
                </div>
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-6 text-white">
                    <p class="text-green-100 text-sm">Сотрудников</p>
                    <p class="text-3xl font-bold">{{ totalEmployees }}</p>
                </div>
                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg p-6 text-white">
                    <p class="text-yellow-100 text-sm">Подтверждено</p>
                    <p class="text-3xl font-bold">{{ confirmedCount }}</p>
                </div>
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg p-6 text-white">
                    <p class="text-purple-100 text-sm">Средний балл</p>
                    <p class="text-3xl font-bold">{{ averagePoints }}</p>
                </div>
            </div>

            <!-- Таблица конструкторов -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-8">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Отдел конструкторов
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Сотрудник</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Статус</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Баллы</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Кол-во заявок</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">% от отдела</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Действия</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="sheet in constructorSheets" :key="sheet.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">
                                    {{ sheet.user?.full_name || '—' }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ sheet.user?.position?.name || 'Должность не указана' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                    <span :class="getStatusBadgeClass(sheet.status)" class="px-2 py-1 text-xs rounded-full">
                                        {{ getStatusName(sheet.status) }}
                                    </span>
                            </td>
                            <td class="px-6 py-4">
                                    <span class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                                        {{ formatPoints(sheet.total_points) }}
                                    </span>
                            </td>
                            <td class="px-6 py-4">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ sheet.requests_count || 0 }}
                                    </span>
                            </td>
                            <td class="px-6 py-4">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ getPercentage(sheet.total_points, constructorTotalPoints) }}%
                                    </span>
                            </td>
                            <td class="px-6 py-4">
                                <Link
                                    :href="route('scoring.sheets.show', sheet.id)"
                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-700"
                                >
                                    Просмотр →
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="constructorSheets.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                Нет данных за этот период
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Итого по отделу:</span>
                        <div class="flex gap-8">
                            <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                {{ formatPoints(constructorTotalPoints) }} баллов
                            </span>
                            <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                {{ constructorRequestsTotal }} заявок
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Таблица дизайнеров -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Отдел дизайнеров
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Сотрудник</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Статус</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Баллы</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Кол-во заявок</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">% от отдела</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Действия</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="sheet in designerSheets" :key="sheet.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">
                                    {{ sheet.user?.full_name || '—' }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ sheet.user?.position?.name || 'Должность не указана' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                    <span :class="getStatusBadgeClass(sheet.status)" class="px-2 py-1 text-xs rounded-full">
                                        {{ getStatusName(sheet.status) }}
                                    </span>
                            </td>
                            <td class="px-6 py-4">
                                    <span class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                                        {{ formatPoints(sheet.total_points) }}
                                    </span>
                            </td>
                            <td class="px-6 py-4">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ sheet.requests_count || 0 }}
                                    </span>
                            </td>
                            <td class="px-6 py-4">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ getPercentage(sheet.total_points, designerTotalPoints) }}%
                                    </span>
                            </td>
                            <td class="px-6 py-4">
                                <Link
                                    :href="route('scoring.sheets.show', sheet.id)"
                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-700"
                                >
                                    Просмотр →
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="designerSheets.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                Нет данных за этот период
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Итого по отделу:</span>
                        <div class="flex gap-8">
                            <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                {{ formatPoints(designerTotalPoints) }} баллов
                            </span>
                            <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                {{ designerRequestsTotal }} заявок
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    constructorSheets: { type: Array, default: () => [] },
    designerSheets: { type: Array, default: () => [] },
    currentDate: { type: String, required: true },
    months: { type: Array, default: () => [] }
})

const selectedMonth = ref(props.currentDate)

const formatPoints = (value) => {
    if (value === null || value === undefined) return '0'
    const num = Number(value)
    if (isNaN(num)) return '0'
    if (num % 1 === 0) return num.toString()
    return num.toFixed(2).replace(/\.?0+$/, '')
}

// Сумма баллов по отделам
const constructorTotalPoints = computed(() => {
    return props.constructorSheets.reduce((sum, sheet) => sum + (parseFloat(sheet.total_points) || 0), 0)
})
const designerTotalPoints = computed(() => {
    return props.designerSheets.reduce((sum, sheet) => sum + (parseFloat(sheet.total_points) || 0), 0)
})

// Общее количество заявок по отделам
const constructorRequestsTotal = computed(() => {
    return props.constructorSheets.reduce((sum, sheet) => sum + (sheet.requests_count || 0), 0)
})
const designerRequestsTotal = computed(() => {
    return props.designerSheets.reduce((sum, sheet) => sum + (sheet.requests_count || 0), 0)
})

// Общая статистика
const totalPoints = computed(() => constructorTotalPoints.value + designerTotalPoints.value)
const totalEmployees = computed(() => props.constructorSheets.length + props.designerSheets.length)
const confirmedCount = computed(() => {
    const all = [...props.constructorSheets, ...props.designerSheets]
    return all.filter(s => s.status === 'confirmed' || s.status === 'approved').length
})
const averagePoints = computed(() => totalEmployees.value === 0 ? 0 : (totalPoints.value / totalEmployees.value).toFixed(1))

// Процент от общего балла отдела (округление до целого)
const getPercentage = (points, total) => {
    if (!total || total === 0) return 0
    const percent = (parseFloat(points) / total) * 100
    return Math.round(percent)
}

const changeMonth = () => {
    router.get(route('scoring.summary'), { date: selectedMonth.value }, { preserveState: true, preserveScroll: true })
}

const getStatusName = (status) => {
    const map = { draft: 'Черновик', confirmed: 'Подтверждена', approved: 'Утверждена' }
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
