<script setup>
import { Head } from '@inertiajs/vue3'
import AppLayout from "@/Layouts/AppLayout.vue"
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth?.user ?? null)
const isAuthenticated = computed(() => !!user.value)
</script>

<template>
    <Head title="GofraLub - Упрощение работы отдела развития" />

    <app-layout>
        <!-- Hero Section -->
        <div class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-900 -z-10"></div>

            <div class="container mx-auto px-4 py-16 md:py-24">
                <div class="text-center max-w-4xl mx-auto">
                    <div class="mb-6">
                        <span class="inline-block px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full text-sm font-semibold">
                            Дипломный проект
                        </span>
                    </div>

                    <h1 class="text-4xl md:text-6xl font-bold text-gray-900 dark:text-white mb-6">
                        Gofra<span class="bg-gradient-to-r from-blue-500 to-blue-600 bg-clip-text text-transparent">Lub</span>
                    </h1>

                    <p class="text-lg md:text-xl text-gray-600 dark:text-gray-300 mb-4">
                        Упрощение работы отдела развития фабрики
                    </p>

                    <p class="text-md text-gray-500 dark:text-gray-400 mb-8 max-w-2xl mx-auto">
                        ЮжУралКартон — автоматизация процессов управления персоналом,
                        учета задач и контроля эффективности
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <template v-if="!isAuthenticated">
                            <Link
                                :href="route('register')"
                                class="px-8 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl"
                            >
                                Начать работу
                            </Link>
                            <Link
                                :href="route('login')"
                                class="px-8 py-3 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-semibold rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200"
                            >
                                Войти в систему
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                :href="route('dashboard')"
                                class="px-8 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl"
                            >
                                Перейти в панель управления
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- О проекте -->
        <div class="container mx-auto px-4 py-16">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    О проекте GofraLub
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
                    Система разработана для оптимизации работы отдела развития фабрики ЮжУралКартон,
                    автоматизации ключевых бизнес-процессов и повышения эффективности управления
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Карточка 1 -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 group">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                        Автоматизация
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Автоматизация рутинных процессов отдела развития, сокращение времени на выполнение задач
                    </p>
                </div>

                <!-- Карточка 2 -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 group">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                        Управление персоналом
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Централизованное управление сотрудниками, их ролями и правами доступа
                    </p>
                </div>

                <!-- Карточка 3 -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 group">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                        Контроль эффективности
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Мониторинг и анализ ключевых показателей эффективности отдела развития
                    </p>
                </div>

                <!-- Карточка 4 -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 group">
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                        Безопасность данных
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Защита конфиденциальной информации и разграничение доступа
                    </p>
                </div>
            </div>
        </div>

        <!-- Преимущества для фабрики -->
        <div class="bg-gray-50 dark:bg-gray-800/50 py-16">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        Преимущества для ЮжУралКартон
                    </h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
                        Внедрение GofraLub позволит значительно повысить эффективность работы отдела развития
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                Сокращение времени
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                Автоматизация процессов позволяет сократить время на обработку заявок и отчетов до 40%
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                Прозрачность работы
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                Полный контроль над выполнением задач и отчетностью сотрудников
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                Снижение издержек
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                Оптимизация работы отдела приводит к снижению операционных затрат
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                Удобная отчетность
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                Автоматическое формирование отчетов по ключевым показателям деятельности
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Технологии -->
        <div class="container mx-auto px-4 py-16">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Современные технологии
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
                    GofraLub построен на основе современных технологических решений
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                        </svg>
                    </div>
                    <p class="font-semibold text-gray-900 dark:text-white">Laravel</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Backend Framework</p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 mx-auto bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <p class="font-semibold text-gray-900 dark:text-white">Vue.js</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Frontend Framework</p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 mx-auto bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 4 4 4h8c2.5 0 4-2 4-4V7c0-2-1.5-4-4-4H8C5.5 3 4 5 4 7z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8M8 11h8M8 15h4"></path>
                        </svg>
                    </div>
                    <p class="font-semibold text-gray-900 dark:text-white">Inertia.js</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">SPA без API</p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 mx-auto bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-8 h-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                    </div>
                    <p class="font-semibold text-gray-900 dark:text-white">Tailwind CSS</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Styling</p>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 py-16">
            <div class="container mx-auto px-4 text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                    Готовы оптимизировать работу?
                </h2>
                <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                    Присоединяйтесь к GofraLub и упростите управление отделом развития
                </p>
                <template v-if="!isAuthenticated">
                    <Link
                        :href="route('register')"
                        class="inline-block px-8 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition-all duration-200 shadow-lg"
                    >
                        Начать работу
                    </Link>
                </template>
                <template v-else>
                    <Link
                        :href="route('dashboard')"
                        class="inline-block px-8 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition-all duration-200 shadow-lg"
                    >
                        Перейти в дашборд
                    </Link>
                </template>
            </div>
        </div>

        <!-- Footer info -->
        <div class="bg-gray-50 dark:bg-gray-800/50 py-8">
            <div class="container mx-auto px-4 text-center">
                <p class="text-gray-600 dark:text-gray-400">
                    © 2026 GofraLub. Дипломный проект для фабрики ЮжУралКартон
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">
                    Система разработана для оптимизации работы отдела развития
                </p>
            </div>
        </div>
    </app-layout>
</template>
