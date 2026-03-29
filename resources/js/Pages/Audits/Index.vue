<!-- resources/js/Pages/Audits/Index.vue -->
<template>
    <AppLayout>
        <Head title="Выездные аудиты" />

        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 pb-20">
            <!-- Шапка -->
            <div class="sticky top-0 z-10 bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 shadow-lg">
                <div class="px-4 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-bold text-white">Выездные аудиты</h1>
                            <p class="text-blue-100 text-sm mt-0.5">Фиксация результатов</p>
                        </div>
                        <Link :href="route('audits.create')"
                              class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition flex items-center gap-2 text-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="hidden sm:inline">Новый аудит</span>
                        </Link>
                    </div>
                </div>

                <!-- Фильтры (горизонтальный скролл) -->
                <div class="px-4 pb-3 overflow-x-auto scrollbar-hide">
                    <div class="flex gap-2">
                        <button v-for="status in statusFilters" :key="status.value"
                                @click="setFilter('status', status.value)"
                                :class="[
                                'px-3 py-1.5 rounded-full text-sm whitespace-nowrap transition',
                                filters.status === status.value
                                    ? 'bg-white text-blue-600 font-medium'
                                    : 'bg-white/20 text-white'
                            ]">
                            {{ status.label }} ({{ getStatusCount(status.value) }})
                        </button>
                    </div>
                </div>
            </div>

            <!-- Статистика (карточки) -->
            <div class="px-4 py-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-3 shadow-sm">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total || 0 }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Всего</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-3 shadow-sm">
                        <div class="text-2xl font-bold text-yellow-600">{{ stats.by_status?.in_progress || 0 }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">В процессе</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-3 shadow-sm">
                        <div class="text-2xl font-bold text-green-600">{{ stats.by_status?.completed || 0 }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Завершено</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-3 shadow-sm">
                        <div class="text-2xl font-bold text-blue-600">{{ stats.this_month || 0 }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">За месяц</div>
                    </div>
                </div>
            </div>

            <!-- Поиск -->
            <div class="px-4 mb-4">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input v-model="searchQuery" @input="debouncedSearch" type="text"
                           placeholder="Поиск по названию, клиенту, адресу..."
                           class="w-full pl-10 pr-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Список аудитов -->
            <div class="px-4 space-y-3">
                <div v-for="audit in audits.data" :key="audit.id"
                     @click="openAudit(audit.id)"
                     class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden active:scale-[0.98] transition-transform cursor-pointer">
                    <!-- Статусная полоска -->
                    <div class="h-1" :class="getStatusBarClass(audit.status)"></div>

                    <div class="p-4">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="font-semibold text-gray-900 dark:text-white flex-1">{{ audit.title }}</h3>
                            <span :class="getStatusBadgeClass(audit.status)" class="text-xs px-2 py-1 rounded-full ml-2 whitespace-nowrap">
                                {{ audit.status_name }}
                            </span>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div v-if="audit.client_name" class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>{{ audit.client_name }}</span>
                            </div>

                            <div v-if="audit.audit_date" class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>{{ formatDate(audit.audit_date) }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <span>{{ audit.assignee_name }}</span>
                                </div>

                                <!-- Миниатюры фото -->
                                <div v-if="audit.media?.length" class="flex -space-x-2">
                                    <div v-for="(photo, idx) in audit.media.slice(0, 3)" :key="idx"
                                         class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-xs border-2 border-white dark:border-gray-800">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="audits.data?.length === 0" class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400">Нет аудитов для отображения</p>
                    <Link :href="route('audits.create')" class="inline-block mt-4 text-blue-600 dark:text-blue-400">
                        Создать первый аудит
                    </Link>
                </div>
            </div>

            <!-- Пагинация -->
            <div class="px-4 mt-6 mb-4">
                <Pagination :links="audits.links" />
            </div>

            <!-- Кнопка быстрого создания (плавающая) -->
            <Link :href="route('audits.create')"
                  class="fixed bottom-20 right-4 sm:bottom-6 sm:right-6 bg-blue-600 hover:bg-blue-700 text-white rounded-full p-4 shadow-lg transition-transform active:scale-95 z-20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </Link>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/UI/Pagination.vue'

const props = defineProps({
    audits: { type: Object, required: true },
    stats: { type: Object, default: () => ({}) },
    users: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ status: 'all', search: '' }) }
})

const searchQuery = ref(props.filters.search || '')
let searchTimeout = null

const statusFilters = [
    { value: 'all', label: 'Все' },
    { value: 'draft', label: 'Черновики' },
    { value: 'in_progress', label: 'В процессе' },
    { value: 'completed', label: 'Завершены' },
    { value: 'cancelled', label: 'Отменены' }
]

const setFilter = (key, value) => {
    router.get(route('audits.index'), { ...props.filters, [key]: value }, {
        preserveState: true,
        preserveScroll: true
    })
}

const getStatusCount = (status) => {
    if (status === 'all') return props.stats.total || 0
    return props.stats.by_status?.[status] || 0
}

const getStatusBarClass = (status) => {
    const map = {
        draft: 'bg-gray-400',
        in_progress: 'bg-yellow-500',
        completed: 'bg-green-500',
        cancelled: 'bg-red-500'
    }
    return map[status] || 'bg-gray-400'
}

const getStatusBadgeClass = (status) => {
    const map = {
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        in_progress: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        completed: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
    }
    return map[status] || ''
}

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
}

const openAudit = (id) => {
    router.get(route('audits.show', id))
}

const debouncedSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        router.get(route('audits.index'), { ...props.filters, search: searchQuery.value }, {
            preserveState: true,
            preserveScroll: true
        })
    }, 500)
}
</script>
