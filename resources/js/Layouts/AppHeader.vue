<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { computed, ref, onMounted, onUnmounted } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth?.user ?? null)
const mobileMenuOpen = ref(false)
const theme = ref('light')

const isAuthenticated = computed(() => !!user.value)
const isAdmin = computed(() => {
    return user.value?.roles?.includes('admin') || user.value?.role === 'admin'
})

const getUserRoleName = (role) => {
    const roles = {
        'admin': 'Администратор',
        'manager': 'Менеджер',
        'user': 'Пользователь'
    }
    return roles[role] || 'Пользователь'
}

const logout = () => {
    if (confirm('Вы уверены, что хотите выйти?')) {
        router.post(route('logout'))
    }
}

const toggleTheme = () => {
    theme.value = theme.value === 'light' ? 'dark' : 'light'
    localStorage.setItem('theme', theme.value)
    applyTheme(theme.value)
}

const applyTheme = (selectedTheme) => {
    if (selectedTheme === 'dark') {
        document.documentElement.classList.add('dark')
    } else {
        document.documentElement.classList.remove('dark')
    }
}

// Закрытие меню
const closeMenu = () => {
    mobileMenuOpen.value = false
}

const toggleMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value
}

// Получаем текущий URL
const currentUrl = computed(() => page.url)

// Функции для определения активных страниц
const isActiveHome = () => {
    return currentUrl.value === '/'
}

const isActiveDashboard = () => {
    return currentUrl.value === '/dashboard'
}

const isActiveTodos = () => {
    return currentUrl.value.startsWith('/todos')
}

const isActiveAdminUsers = () => {
    return currentUrl.value === '/admin/users'
}

const isActiveAdminStatistics = () => {
    return currentUrl.value === '/admin/statistics'
}

const isActiveAdminDepartments = () => {
    return currentUrl.value === '/admin/departments' || currentUrl.value.startsWith('/admin/departments/')
}

const isActiveAdminPositions = () => {
    return currentUrl.value === '/admin/positions' || currentUrl.value.startsWith('/admin/positions/')
}

const isActiveProfile = () => {
    return currentUrl.value === '/profile'
}

onMounted(() => {
    const savedTheme = localStorage.getItem('theme') || 'light'
    theme.value = savedTheme
    applyTheme(savedTheme)
})

onUnmounted(() => {
    document.body.style.overflow = ''
})
</script>

<template>
    <header class="sticky top-0 z-50 bg-white dark:bg-gray-900 shadow-md transition-colors duration-300">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 lg:h-20">
                <!-- Логотип GofraLub -->
                <Link
                    :href="isAuthenticated ? route('dashboard') : route('pages.home')"
                    class="flex items-center space-x-2 group cursor-pointer"
                >
                    <div class="w-9 h-9 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-md transition-all duration-300 group-hover:scale-105">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div class="text-xl font-bold">
                        <span class="text-gray-900 dark:text-white">Gofra</span>
                        <span class="bg-gradient-to-r from-blue-500 to-blue-600 bg-clip-text text-transparent">Lub</span>
                    </div>
                </Link>

                <!-- Десктопное меню -->
                <nav class="hidden lg:flex items-center space-x-6">
                    <Link
                        :href="route('pages.home')"
                        class="relative px-1 py-2 text-gray-700 dark:text-gray-300 transition-all duration-200 hover:text-blue-600 dark:hover:text-blue-400 cursor-pointer"
                        :class="{
                            'text-blue-600 dark:text-blue-400 font-semibold': isActiveHome(),
                            'after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-gradient-to-r after:from-blue-500 after:to-blue-600 after:rounded-full after:transition-all after:duration-300': isActiveHome()
                        }"
                    >
                        Главная
                    </Link>

                    <template v-if="isAuthenticated">
                        <Link
                            :href="route('dashboard')"
                            class="relative px-1 py-2 text-gray-700 dark:text-gray-300 transition-all duration-200 hover:text-blue-600 dark:hover:text-blue-400 cursor-pointer"
                            :class="{
                                'text-blue-600 dark:text-blue-400 font-semibold': isActiveDashboard(),
                                'after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-gradient-to-r after:from-blue-500 after:to-blue-600 after:rounded-full after:transition-all after:duration-300': isActiveDashboard()
                            }"
                        >
                            Панель управления
                        </Link>

                        <!-- Задачи -->
                        <Link
                            :href="route('todos.index')"
                            class="relative px-1 py-2 text-gray-700 dark:text-gray-300 transition-all duration-200 hover:text-blue-600 dark:hover:text-blue-400 cursor-pointer"
                            :class="{
                                'text-blue-600 dark:text-blue-400 font-semibold': isActiveTodos(),
                                'after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-gradient-to-r after:from-blue-500 after:to-blue-600 after:rounded-full after:transition-all after:duration-300': isActiveTodos()
                            }"
                        >
                            Задачи
                        </Link>

                        <!-- Выпадающее меню администрирования с исправленным зазором -->
                        <div v-if="isAdmin" class="relative group">
                            <button
                                class="relative px-1 py-2 text-gray-700 dark:text-gray-300 transition-all duration-200 hover:text-blue-600 dark:hover:text-blue-400 cursor-pointer flex items-center gap-1"
                                :class="{
                                    'text-blue-600 dark:text-blue-400 font-semibold': currentUrl.startsWith('/admin')
                                }"
                            >
                                Администрирование
                                <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Прозрачный мостик для плавного перехода -->
                            <div class="absolute left-0 pt-2 w-56 invisible group-hover:visible">
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700">
                                    <div class="py-2">
                                        <Link
                                            :href="route('admin.users')"
                                            class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer"
                                            :class="{ 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400': isActiveAdminUsers() }"
                                        >
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                            </svg>
                                            Пользователи
                                        </Link>
                                        <Link
                                            :href="route('admin.departments.index')"
                                            class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer"
                                            :class="{ 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400': isActiveAdminDepartments() }"
                                        >
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            Отделы
                                        </Link>
                                        <Link
                                            :href="route('admin.positions.index')"
                                            class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer"
                                            :class="{ 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400': isActiveAdminPositions() }"
                                        >
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            Должности
                                        </Link>
                                        <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                                        <Link
                                            :href="route('admin.statistics')"
                                            class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer"
                                            :class="{ 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400': isActiveAdminStatistics() }"
                                        >
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                            </svg>
                                            Статистика
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </nav>

                <!-- Правая панель -->
                <div class="flex items-center space-x-3">
                    <!-- Кнопка переключения темы -->
                    <button
                        @click="toggleTheme"
                        class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-300 cursor-pointer"
                        :title="theme === 'light' ? 'Тёмная тема' : 'Светлая тема'"
                    >
                        <svg v-if="theme === 'light'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </button>

                    <!-- Профиль (авторизован) с исправленным зазором -->
                    <div v-if="isAuthenticated" class="relative group">
                        <button class="flex items-center space-x-2 p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer">
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                {{ user?.first_name?.charAt(0) || user?.email?.charAt(0) || 'U' }}
                            </div>
                            <div class="hidden sm:block">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ user?.short_name || user?.full_name || user?.email }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ getUserRoleName(user?.role) }}
                                </p>
                            </div>
                            <svg class="hidden sm:block w-4 h-4 text-gray-400 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Прозрачный мостик для плавного перехода -->
                        <div class="absolute right-0 pt-2 w-56 invisible group-hover:visible">
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700">
                                <div class="py-2">
                                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ user?.full_name || user?.email }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ user?.email }}</p>
                                    </div>
                                    <Link :href="route('profile.edit')" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        Профиль
                                    </Link>
                                    <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                                    <button @click="logout" class="flex w-full items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition cursor-pointer">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        Выйти
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Кнопки входа/регистрации (не авторизован) -->
                    <div v-else class="hidden sm:flex items-center space-x-2">
                        <Link :href="route('login')" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-blue-600 transition cursor-pointer">Вход</Link>
                        <Link :href="route('register')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition shadow-md cursor-pointer">Регистрация</Link>
                    </div>

                    <!-- Бургер-меню (мобильное) -->
                    <button
                        @click="toggleMenu"
                        class="lg:hidden p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer"
                    >
                        <svg v-if="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Мобильное меню - плавное выезжание сверху -->
        <transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="-translate-y-full opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition-all duration-300 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="-translate-y-full opacity-0"
        >
            <div v-if="mobileMenuOpen" class="lg:hidden absolute top-full left-0 right-0 bg-white dark:bg-gray-900 shadow-lg border-t border-gray-200 dark:border-gray-700 z-50">
                <div class="container mx-auto px-4 py-4">
                    <!-- Профиль в мобильном меню (если авторизован) -->
                    <div v-if="isAuthenticated" class="pb-4 mb-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                {{ user?.first_name?.charAt(0) || user?.email?.charAt(0) || 'U' }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ user?.full_name || user?.email }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ user?.email }}</p>
                                <span class="inline-block mt-1 px-2 py-0.5 text-xs rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                                    {{ getUserRoleName(user?.role) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Навигация -->
                    <div class="flex flex-col space-y-1">
                        <Link
                            :href="route('pages.home')"
                            class="px-4 py-3 rounded-lg transition cursor-pointer"
                            :class="{
                                'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-semibold': isActiveHome(),
                                'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800': !isActiveHome()
                            }"
                            @click="closeMenu"
                        >
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                <span>Главная</span>
                            </div>
                        </Link>

                        <template v-if="isAuthenticated">
                            <Link
                                :href="route('dashboard')"
                                class="px-4 py-3 rounded-lg transition cursor-pointer"
                                :class="{
                                    'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-semibold': isActiveDashboard(),
                                    'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800': !isActiveDashboard()
                                }"
                                @click="closeMenu"
                            >
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                                    </svg>
                                    <span>Панель управления</span>
                                </div>
                            </Link>

                            <!-- Задачи (мобильное меню) -->
                            <Link
                                :href="route('todos.index')"
                                class="px-4 py-3 rounded-lg transition cursor-pointer"
                                :class="{
                                    'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-semibold': isActiveTodos(),
                                    'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800': !isActiveTodos()
                                }"
                                @click="closeMenu"
                            >
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <span>Задачи</span>
                                </div>
                            </Link>

                            <template v-if="isAdmin">
                                <!-- Заголовок админки -->
                                <div class="px-4 py-2 mt-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Администрирование
                                </div>

                                <!-- Управление пользователями -->
                                <Link
                                    :href="route('admin.users')"
                                    class="px-4 py-3 rounded-lg transition cursor-pointer pl-8"
                                    :class="{
                                        'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-semibold': isActiveAdminUsers(),
                                        'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800': !isActiveAdminUsers()
                                    }"
                                    @click="closeMenu"
                                >
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                        <span>Пользователи</span>
                                    </div>
                                </Link>

                                <!-- Отделы -->
                                <Link
                                    :href="route('admin.departments.index')"
                                    class="px-4 py-3 rounded-lg transition cursor-pointer pl-8"
                                    :class="{
                                        'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-semibold': isActiveAdminDepartments(),
                                        'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800': !isActiveAdminDepartments()
                                    }"
                                    @click="closeMenu"
                                >
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        <span>Отделы</span>
                                    </div>
                                </Link>

                                <!-- Должности -->
                                <Link
                                    :href="route('admin.positions.index')"
                                    class="px-4 py-3 rounded-lg transition cursor-pointer pl-8"
                                    :class="{
                                        'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-semibold': isActiveAdminPositions(),
                                        'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800': !isActiveAdminPositions()
                                    }"
                                    @click="closeMenu"
                                >
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <span>Должности</span>
                                    </div>
                                </Link>

                                <!-- Статистика -->
                                <Link
                                    :href="route('admin.statistics')"
                                    class="px-4 py-3 rounded-lg transition cursor-pointer pl-8"
                                    :class="{
                                        'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-semibold': isActiveAdminStatistics(),
                                        'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800': !isActiveAdminStatistics()
                                    }"
                                    @click="closeMenu"
                                >
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        <span>Статистика</span>
                                    </div>
                                </Link>
                            </template>

                            <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>

                            <!-- Профиль -->
                            <Link
                                :href="route('profile.edit')"
                                class="px-4 py-3 rounded-lg transition cursor-pointer"
                                :class="{
                                    'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-semibold': isActiveProfile(),
                                    'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800': !isActiveProfile()
                                }"
                                @click="closeMenu"
                            >
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span>Мой профиль</span>
                                </div>
                            </Link>

                            <!-- Выход -->
                            <button
                                @click="logout"
                                class="px-4 py-3 rounded-lg text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition text-left cursor-pointer"
                            >
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    <span>Выйти</span>
                                </div>
                            </button>
                        </template>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer"
                                @click="closeMenu"
                            >
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                    </svg>
                                    <span>Вход</span>
                                </div>
                            </Link>
                            <Link
                                :href="route('register')"
                                class="px-4 py-3 rounded-lg bg-blue-600 text-white text-center font-medium transition hover:bg-blue-700 cursor-pointer"
                                @click="closeMenu"
                            >
                                <div class="flex items-center justify-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                    </svg>
                                    <span>Регистрация</span>
                                </div>
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </transition>
    </header>
</template>

<style scoped>
/* Плавные переходы */
.group-hover\:rotate-180 {
    transform: rotate(180deg);
}
</style>
