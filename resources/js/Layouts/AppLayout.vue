<!-- resources/js/Layouts/AppLayout.vue - обновленная версия с отступами -->

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <AppHeader />

        <!-- Добавляем отступ снизу для мобильной навигации -->
        <div class="pb-20 sm:pb-0">
            <slot />
        </div>

        <!-- Мобильная нижняя навигация -->
        <nav class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 sm:hidden z-20 shadow-lg">
            <div class="flex justify-around">
                <Link :href="route('dashboard')"
                      class="flex flex-col items-center py-3 px-3 transition-colors"
                      :class="isActive('dashboard') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-xs mt-1">Главная</span>
                </Link>

                <Link :href="route('tasks.index')"
                      class="flex flex-col items-center py-3 px-3 transition-colors"
                      :class="isActive('tasks') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span class="text-xs mt-1">Задачи</span>
                </Link>

                <Link :href="route('audits.index')"
                      class="flex flex-col items-center py-3 px-3 transition-colors"
                      :class="isActive('audits') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="text-xs mt-1">Аудиты</span>
                </Link>

                <Link :href="route('profile.edit')"
                      class="flex flex-col items-center py-3 px-3 transition-colors"
                      :class="isActive('profile') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-xs mt-1">Профиль</span>
                </Link>
            </div>
        </nav>
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'
import AppHeader from './AppHeader.vue'
import { Link } from '@inertiajs/vue3'

const page = usePage()

const isActive = (module) => {
    const url = page.url
    if (module === 'dashboard') return url === '/dashboard'
    if (module === 'tasks') return url.startsWith('/tasks')
    if (module === 'audits') return url.startsWith('/audits')
    if (module === 'profile') return url === '/profile'
    return false
}
</script>
