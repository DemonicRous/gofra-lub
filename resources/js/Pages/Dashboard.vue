<template>
    <AppLayout>
        <Head title="Панель управления" />

        <div class="container mx-auto px-4 py-8">
            <!-- Приветственная карточка -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-xl p-6 mb-8 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold mb-2">
                            Добро пожаловать, {{ user?.full_name || 'Гость' }}!
                        </h1>
                        <p class="text-blue-100">
                            Вы успешно авторизованы и ваш аккаунт одобрен администратором.
                        </p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white/20">
                                {{ getUserRoleName(user?.role) }}
                            </span>
                            <span v-if="user?.department" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white/20">
                                {{ user.department }}
                            </span>
                            <span v-if="user?.position" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white/20">
                                {{ user.position }}
                            </span>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Информация о пользователе -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Личная информация -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-lg font-semibold mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Личная информация
                    </h2>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                            <span class="text-gray-600">ФИО:</span>
                            <span class="font-medium text-gray-900">{{ user?.full_name || '—' }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                            <span class="text-gray-600">Никнейм:</span>
                            <span class="font-medium text-blue-600">@{{ user?.nickname || '—' }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                            <span class="text-gray-600">Email:</span>
                            <span class="font-medium text-gray-900">{{ user?.email || '—' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Статус:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Активен
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Рабочая информация -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-lg font-semibold mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Рабочая информация
                    </h2>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                            <span class="text-gray-600">Должность:</span>
                            <span class="font-medium text-gray-900">{{ user?.position || '—' }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                            <span class="text-gray-600">Отдел:</span>
                            <span class="font-medium text-gray-900">{{ user?.department || '—' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Роль:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                  :class="getRoleBadgeClass(user?.role)">
                                {{ getUserRoleName(user?.role) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Статистика пользователя -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-lg font-semibold mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Активность
                    </h2>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                            <span class="text-gray-600">Дата регистрации:</span>
                            <span class="font-medium text-gray-900 text-sm">{{ formatDate(user?.created_at) }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                            <span class="text-gray-600">Email подтвержден:</span>
                            <span :class="user?.email_verified_at ? 'text-green-600' : 'text-red-600'" class="font-medium">
                                {{ user?.email_verified_at ? 'Да' : 'Нет' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Аккаунт одобрен:</span>
                            <span :class="user?.approved_at ? 'text-green-600' : 'text-yellow-600'" class="font-medium">
                                {{ user?.approved_at ? formatDate(user.approved_at) : 'Ожидает' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Статистика системы (только для администратора) -->
            <div v-if="isAdmin && stats" class="mb-8">
                <h2 class="text-xl font-bold mb-4">Статистика системы</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm">Всего пользователей</p>
                                <p class="text-2xl font-bold">{{ stats.totalUsers || 0 }}</p>
                            </div>
                            <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm">Активных пользователей</p>
                                <p class="text-2xl font-bold">{{ stats.activeUsers || 0 }}</p>
                            </div>
                            <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-yellow-100 text-sm">Ожидают одобрения</p>
                                <p class="text-2xl font-bold">{{ stats.pendingUsers || 0 }}</p>
                            </div>
                            <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-sm">Подтвержденных email</p>
                                <p class="text-2xl font-bold">{{ stats.verifiedUsers || 0 }}</p>
                            </div>
                            <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Быстрые действия -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h2 class="text-xl font-bold mb-4">Быстрые действия</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Link :href="route('profile.edit')"
                          class="flex items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition group">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-blue-200 transition">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">Редактировать профиль</div>
                            <div class="text-sm text-gray-500">Обновить личные данные</div>
                        </div>
                    </Link>

                    <div class="flex items-center p-3 bg-gray-50 rounded-lg cursor-not-allowed opacity-50">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="font-medium text-gray-400">Уведомления</div>
                            <div class="text-sm text-gray-400">Скоро будет доступно</div>
                        </div>
                    </div>

                    <div class="flex items-center p-3 bg-gray-50 rounded-lg cursor-not-allowed opacity-50">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="font-medium text-gray-400">Документы</div>
                            <div class="text-sm text-gray-400">Скоро будет доступно</div>
                        </div>
                    </div>

                    <button @click="logout"
                            class="flex items-center p-3 bg-gray-50 hover:bg-red-50 rounded-lg transition group">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-red-200 transition">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900 group-hover:text-red-600">Выйти из системы</div>
                            <div class="text-sm text-gray-500">Завершить сеанс</div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Админ-панель (только для администратора) -->
            <div v-if="isAdmin" class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Администрирование
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Link :href="route('admin.users')"
                          class="flex items-center justify-between p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition">
                        <div>
                            <div class="font-semibold text-purple-900">Управление пользователями</div>
                            <div class="text-sm text-purple-600">Просмотр, одобрение и управление пользователями</div>
                        </div>
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </Link>

                    <Link :href="route('admin.statistics')"
                          class="flex items-center justify-between p-4 bg-green-50 hover:bg-green-100 rounded-lg transition">
                        <div>
                            <div class="font-semibold text-green-900">Статистика</div>
                            <div class="text-sm text-green-600">Аналитика и отчеты по системе</div>
                        </div>
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const page = usePage()
const user = computed(() => page.props.auth?.user ?? null)
const stats = computed(() => page.props.stats ?? null)

// Проверка ролей пользователя
const isAdmin = computed(() => {
    return user.value?.roles?.includes('admin') || user.value?.role === 'admin'
})

const isManager = computed(() => {
    return user.value?.roles?.includes('manager') || user.value?.role === 'manager'
})

// Получение названия роли на русском
const getUserRoleName = (role) => {
    const roles = {
        'admin': 'Администратор',
        'manager': 'Менеджер',
        'user': 'Пользователь'
    }
    return roles[role] || 'Пользователь'
}

// Получение класса для бейджа роли
const getRoleBadgeClass = (role) => {
    const classes = {
        'admin': 'bg-purple-100 text-purple-800',
        'manager': 'bg-blue-100 text-blue-800',
        'user': 'bg-gray-100 text-gray-800'
    }
    return classes[role] || 'bg-gray-100 text-gray-800'
}

// Форматирование даты
const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

// Выход из системы
const logout = () => {
    if (confirm('Вы уверены, что хотите выйти из системы?')) {
        router.post(route('logout'))
    }
}
</script>
