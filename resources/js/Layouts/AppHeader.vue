<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { computed, ref, onMounted } from 'vue'

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

onMounted(() => {
    const savedTheme = localStorage.getItem('theme') || 'light'
    theme.value = savedTheme
    applyTheme(savedTheme)
})

const closeMobileMenu = () => {
    mobileMenuOpen.value = false
}
</script>

<template>
    <header class="sticky top-0 z-50 bg-white dark:bg-gray-900 shadow-md transition-colors duration-300">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 lg:h-20">
                <!-- Логотип -->
                <Link
                    :href="isAuthenticated ? route('dashboard') : route('pages.home')"
                    class="flex items-center space-x-2 group"
                >
                    <div class="w-8 h-8 bg-linear-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <span class="text-xl font-bold bg-linear-to-r from-blue-600 to-blue-500 bg-clip-text text-transparent dark:from-blue-400 dark:to-blue-300">
                        Логотип
                    </span>
                </Link>

                <!-- Десктопное меню -->
                <nav class="hidden lg:flex items-center space-x-1">
                    <Link
                        :href="route('pages.home')"
                        class="px-3 py-2 rounded-md text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200"
                    >
                        Главная
                    </Link>

                    <template v-if="isAuthenticated">
                        <Link
                            :href="route('dashboard')"
                            class="px-3 py-2 rounded-md text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200"
                        >
                            Панель управления
                        </Link>

                        <Link
                            v-if="isAdmin"
                            :href="route('admin.users')"
                            class="px-3 py-2 rounded-md text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200"
                        >
                            Пользователи
                        </Link>

                        <Link
                            v-if="isAdmin"
                            :href="route('admin.statistics')"
                            class="px-3 py-2 rounded-md text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200"
                        >
                            Статистика
                        </Link>
                    </template>

                    <template v-else>
                        <Link
                            :href="route('pages.home')"
                            class="px-3 py-2 rounded-md text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200"
                        >
                            О нас
                        </Link>
                        <Link
                            :href="route('pages.home')"
                            class="px-3 py-2 rounded-md text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200"
                        >
                            Контакты
                        </Link>
                    </template>
                </nav>

                <!-- Правая панель -->
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <!-- Кнопка переключения темы -->
                    <button
                        @click="toggleTheme"
                        class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200 cursor-pointer"
                        :title="theme === 'light' ? 'Тёмная тема' : 'Светлая тема'"
                    >
                        <svg v-if="theme === 'light'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </button>

                    <!-- Профиль (авторизован) -->
                    <div v-if="isAuthenticated" class="relative group">
                        <button class="flex items-center space-x-2 p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200 cursor-pointer">
                            <div class="w-8 h-8 bg-linear-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                {{ user?.first_name?.charAt(0) || user?.email?.charAt(0) || 'U' }}
                            </div>
                            <div class="hidden sm:block text-left">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ user?.short_name || user?.full_name || user?.email }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ getUserRoleName(user?.role) }}
                                </p>
                            </div>
                            <svg class="hidden sm:block w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Выпадающее меню -->
                        <div class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 border border-gray-200 dark:border-gray-700">
                            <div class="py-2">
                                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ user?.full_name || user?.email }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ user?.email }}
                                    </p>
                                </div>

                                <Link :href="route('profile.edit')" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
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

                    <!-- Кнопки входа/регистрации (не авторизован) -->
                    <div v-else class="hidden sm:flex items-center space-x-2">
                        <Link
                            :href="route('login')"
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition"
                        >
                            Вход
                        </Link>
                        <Link
                            :href="route('register')"
                            class="px-4 py-2 bg-linear-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg transition-all duration-200 shadow-md hover:shadow-lg"
                        >
                            Регистрация
                        </Link>
                    </div>

                    <!-- Бургер-меню (мобильное) -->
                    <button
                        class="burger-button lg:hidden p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200 cursor-pointer"
                        @click="mobileMenuOpen = !mobileMenuOpen"
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

            <!-- Мобильное меню -->
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="transform -translate-y-full opacity-0"
                enter-to-class="transform translate-y-0 opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="transform translate-y-0 opacity-100"
                leave-to-class="transform -translate-y-full opacity-0"
            >
                <div v-show="mobileMenuOpen" class="mobile-menu lg:hidden py-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col space-y-2">
                        <Link
                            :href="route('pages.home')"
                            class="px-4 py-2 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                            @click="closeMobileMenu"
                        >
                            Главная
                        </Link>

                        <template v-if="isAuthenticated">
                            <Link
                                :href="route('dashboard')"
                                class="px-4 py-2 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                @click="closeMobileMenu"
                            >
                                Панель управления
                            </Link>

                            <Link
                                v-if="isAdmin"
                                :href="route('admin.users')"
                                class="px-4 py-2 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                @click="closeMobileMenu"
                            >
                                Управление пользователями
                            </Link>

                            <Link
                                v-if="isAdmin"
                                :href="route('admin.statistics')"
                                class="px-4 py-2 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                @click="closeMobileMenu"
                            >
                                Статистика
                            </Link>
                        </template>

                        <template v-else>
                            <Link
                                :href="route('pages.home')"
                                class="px-4 py-2 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                @click="closeMobileMenu"
                            >
                                О нас
                            </Link>
                            <Link
                                :href="route('pages.home')"
                                class="px-4 py-2 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                @click="closeMobileMenu"
                            >
                                Контакты
                            </Link>

                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <Link
                                    :href="route('login')"
                                    class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                    @click="closeMobileMenu"
                                >
                                    Вход
                                </Link>
                                <Link
                                    :href="route('register')"
                                    class="block px-4 py-2 mt-2 bg-linear-to-r from-blue-500 to-blue-600 text-white rounded-lg text-center transition"
                                    @click="closeMobileMenu"
                                >
                                    Регистрация
                                </Link>
                            </div>
                        </template>
                    </div>
                </div>
            </transition>
        </div>
    </header>
</template>

<style scoped>
.mobile-menu {
    max-height: calc(100vh - 4rem);
    overflow-y: auto;
}

/* Плавные переходы для выпадающего меню */
.group:hover .group-hover\:opacity-100 {
    opacity: 1;
    visibility: visible;
}
</style>
