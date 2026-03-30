<!-- resources/js/Pages/Scoring/Summary.vue -->
<template>
    <AppLayout>
        <Head title="Сводка по баллам" />

        <div class="container mx-auto px-4 py-8">
            <!-- Заголовок -->
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
                    <!-- Выбор месяца -->
                    <select
                        v-model="selectedMonth"
                        @change="changeMonth"
                        class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    >
                        <option v-for="month in months" :key="month.value" :value="month.value">
                            {{ month.label }}
                        </option>
                    </select>

                    <!-- Экспорт -->
                    <a
                        :href="route('scoring.export.summary', { date: selectedMonth })"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Экспорт сводки
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

            <!-- Таблицы по подотделам -->
            <div class="space-y-8">
                <!-- Конструкторы -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Кол-во записей</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Действия</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="sheet in constructorSheets" :key="sheet.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ sheet.user.full_name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ sheet.user.position?.name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                        <span :class="getStatusBadgeClass(sheet.status)" class="px-2 py-1 text-xs rounded-full">
                                            {{ getStatusName(sheet.status) }}
                                        </span>
                                </td>
                                <td class="px-6 py-4">
                                        <span class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                                            {{ sheet.total_points }}
                                        </span>
                                </td>
                                <td class="px-6 py-4">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ sheet.entries_count || 0 }}
                                        </span>
                                </td>
                                <td class="px-6 py-4">
                                    <Link
                                        :href="route('scoring.sheets.show', sheet.id)"
                                        class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300"
                                    >
                                        Просмотр →
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="constructorSheets.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    Нет данных за этот период
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Итого по отделу:</span>
                            <span class="text-xl font-bold text-blue-600 dark:text-blue-400">
                                {{ constructorTotalPoints }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Дизайнеры -->
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Кол-во записей</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Действия</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="sheet in designerSheets" :key="sheet.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ sheet.user.full_name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ sheet.user.position?.name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                        <span :class="getStatusBadgeClass(sheet.status)" class="px-2 py-1 text-xs rounded-full">
                                            {{ getStatusName(sheet.status) }}
                                        </span>
                                </td>
                                <td class="px-6 py-4">
                                        <span class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                                            {{ sheet.total_points }}
                                        </span>
                                </td>
                                <td class="px-6 py-4">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ sheet.entries_count || 0 }}
                                        </span>
                                </td>
                                <td class="px-6 py-4">
                                    <Link
                                        :href="route('scoring.sheets.show', sheet.id)"
                                        class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300"
                                    >
                                        Просмотр →
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="designerSheets.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    Нет данных за этот период
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Итого по отделу:</span>
                            <span class="text-xl font-bold text-blue-600 dark:text-blue-400">
                                {{ designerTotalPoints }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Детализация по категориям (опционально) -->
            <div v-if="showDetails" class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Детализация по категориям</h2>
                    <button @click="showDetails = false" class="text-gray-500 hover:text-gray-700">
                        Скрыть
                    </button>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div v-for="category in categoryStats" :key="category.name" class="border-b border-gray-200 dark:border-gray-700 pb-3">
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-gray-900 dark:text-white">{{ category.name }}</span>
                                <span class="text-blue-600 dark:text-blue-400 font-semibold">{{ category.points }} баллов</span>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ category.count }} записей
                            </div>
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
    summary: {
        type: Object,
        required: true
    },
    currentDate: {
        type: String,
        required: true
    },
    months: {
        type: Array,
        default: () => []
    }
})

const selectedMonth = ref(props.currentDate)
const showDetails = ref(false)

const constructorSheets = computed(() => {
    return props.summary.constructor?.sheets || []
})

const designerSheets = computed(() => {
    return props.summary.designer?.sheets || []
})

const constructorTotalPoints = computed(() => {
    return props.summary.constructor?.total_points || 0
})

const designerTotalPoints = computed(() => {
    return props.summary.designer?.total_points || 0
})

const totalPoints = computed(() => {
    return constructorTotalPoints.value + designerTotalPoints.value
})

const totalEmployees = computed(() => {
    return constructorSheets.value.length + designerSheets.value.length
})

const confirmedCount = computed(() => {
    const allSheets = [...constructorSheets.value, ...designerSheets.value]
    return allSheets.filter(s => s.status === 'confirmed' || s.status === 'approved').length
})

const averagePoints = computed(() => {
    if (totalEmployees.value === 0) return 0
    return (totalPoints.value / totalEmployees.value).toFixed(1)
})

// Агрегация по категориям (для детализации)
const categoryStats = computed(() => {
    const stats = {}
    const allSheets = [...constructorSheets.value, ...designerSheets.value]

    allSheets.forEach(sheet => {
        if (sheet.entries) {
            sheet.entries.forEach(entry => {
                const categoryName = entry.category?.name || 'Другое'
                if (!stats[categoryName]) {
                    stats[categoryName] = { points: 0, count: 0 }
                }
                stats[categoryName].points += entry.points
                stats[categoryName].count += 1
            })
        }
    })

    return Object.entries(stats).map(([name, data]) => ({
        name,
        points: data.points,
        count: data.count
    })).sort((a, b) => b.points - a.points)
})

const changeMonth = () => {
    router.get(route('scoring.summary'), { date: selectedMonth.value }, {
        preserveState: true,
        preserveScroll: true
    })
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
