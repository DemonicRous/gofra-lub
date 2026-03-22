<template>
    <AppLayout>
        <Head title="Статистика" />

        <div class="container mx-auto px-4 py-8">
            <div class="bg-white rounded-lg shadow-xl p-6">
                <h1 class="text-2xl font-bold mb-6">Статистика системы</h1>

                <!-- Общая статистика -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm">Всего пользователей</p>
                                <p class="text-3xl font-bold mt-2">{{ stats.total }}</p>
                            </div>
                            <svg class="w-12 h-12 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <div class="mt-4 text-blue-100 text-sm">
                            <span class="font-semibold">{{ stats.verified }}</span> подтвердили email
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-yellow-100 text-sm">Ожидают одобрения</p>
                                <p class="text-3xl font-bold mt-2">{{ stats.pending }}</p>
                            </div>
                            <svg class="w-12 h-12 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="mt-4 text-yellow-100 text-sm">
                            Требуют проверки
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm">Одобренные</p>
                                <p class="text-3xl font-bold mt-2">{{ stats.approved }}</p>
                            </div>
                            <svg class="w-12 h-12 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="mt-4 text-green-100 text-sm">
                            Активные пользователи
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-sm">Подтвержденные</p>
                                <p class="text-3xl font-bold mt-2">{{ stats.verified }}</p>
                            </div>
                            <svg class="w-12 h-12 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="mt-4 text-purple-100 text-sm">
                            Email подтвержден
                        </div>
                    </div>
                </div>

                <!-- Статистика по ролям -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-lg font-semibold mb-4">Распределение по ролям</h2>
                        <div class="space-y-3">
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span>Администраторы</span>
                                    <span>{{ stats.by_role?.admin || 0 }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-purple-600 rounded-full h-2"
                                         :style="{ width: `${getRolePercentage(stats.by_role?.admin)}%` }"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span>Менеджеры</span>
                                    <span>{{ stats.by_role?.manager || 0 }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 rounded-full h-2"
                                         :style="{ width: `${getRolePercentage(stats.by_role?.manager)}%` }"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span>Пользователи</span>
                                    <span>{{ stats.by_role?.user || 0 }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 rounded-full h-2"
                                         :style="{ width: `${getRolePercentage(stats.by_role?.user)}%` }"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Статистика по отделам -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-lg font-semibold mb-4">Распределение по отделам</h2>
                        <div class="space-y-3 max-h-64 overflow-y-auto">
                            <div v-for="(count, department) in stats.by_department" :key="department" class="space-y-1">
                                <div class="flex justify-between text-sm">
                                    <span class="truncate">{{ department }}</span>
                                    <span>{{ count }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-indigo-600 rounded-full h-2"
                                         :style="{ width: `${getDepartmentPercentage(count)}%` }"></div>
                                </div>
                            </div>
                            <div v-if="Object.keys(stats.by_department || {}).length === 0" class="text-gray-500 text-center py-4">
                                Нет данных
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Последние зарегистрированные пользователи -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="text-lg font-semibold mb-4">Последние зарегистрированные пользователи</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                            <tr class="text-left text-sm text-gray-500">
                                <th class="pb-3">ФИО</th>
                                <th class="pb-3">Email</th>
                                <th class="pb-3">Дата регистрации</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                            <tr v-for="user in stats.recent" :key="user.id" class="hover:bg-gray-100">
                                <td class="py-2">{{ user.full_name }}</td>
                                <td class="py-2">{{ user.email }}</td>
                                <td class="py-2">{{ formatDate(user.created_at) }}</td>
                            </tr>
                            <tr v-if="!stats.recent?.length">
                                <td colspan="3" class="py-4 text-center text-gray-500">
                                    Нет зарегистрированных пользователей
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    stats: {
        type: Object,
        required: true,
        default: () => ({
            total: 0,
            pending: 0,
            approved: 0,
            verified: 0,
            by_role: { admin: 0, manager: 0, user: 0 },
            by_department: {},
            recent: []
        })
    }
})

const getRolePercentage = (count) => {
    if (!props.stats.total || props.stats.total === 0) return 0
    return (count / props.stats.total) * 100
}

const getDepartmentPercentage = (count) => {
    if (!props.stats.approved || props.stats.approved === 0) return 0
    return (count / props.stats.approved) * 100
}

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>
