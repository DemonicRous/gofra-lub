<!-- resources/js/Components/Tasks/TaskStats.vue -->

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
        <!-- Всего задач -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Всего задач</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total || 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- В работе -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">В работе</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.by_status?.in_progress || 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Выполнено -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Выполнено</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.by_status?.completed || 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Просрочено -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Просрочено</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.overdue || 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Прогресс -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Прогресс</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.completion_rate || 0 }}%</p>
                </div>
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-2">
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-purple-600 rounded-full h-2 transition-all duration-300" :style="{ width: `${stats.completion_rate || 0}%` }"></div>
                </div>
            </div>
        </div>

        <!-- Средняя скорость -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Активных задач</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ activeTasks }}</p>
                </div>
                <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            by_status: {
                backlog: 0,
                todo: 0,
                in_progress: 0,
                in_review: 0,
                completed: 0,
                cancelled: 0
            },
            overdue: 0,
            completion_rate: 0
        })
    }
})

const activeTasks = computed(() => {
    return (props.stats.by_status?.backlog || 0) +
        (props.stats.by_status?.todo || 0) +
        (props.stats.by_status?.in_progress || 0) +
        (props.stats.by_status?.in_review || 0)
})
</script>
