<template>
    <AppLayout>
        <Head title="Профиль пользователя" />

        <div class="container mx-auto px-4 py-8">
            <div class="max-w-4xl mx-auto">
                <!-- Заголовок -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Профиль пользователя</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">Управление личной информацией и настройками аккаунта</p>
                </div>

                <!-- Основная информация -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl overflow-hidden transition-colors duration-300 mb-6">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-white">Личная информация</h2>
                                <p class="text-blue-100 text-sm">Основные данные пользователя</p>
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="updateProfile" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Фамилия -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Фамилия <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.last_name"
                                    type="text"
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    :class="form.errors.last_name ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                    placeholder="Введите фамилию"
                                />
                                <p v-if="form.errors.last_name" class="text-red-500 text-xs mt-1">{{ form.errors.last_name }}</p>
                            </div>

                            <!-- Имя -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Имя <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.first_name"
                                    type="text"
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    :class="form.errors.first_name ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                    placeholder="Введите имя"
                                />
                                <p v-if="form.errors.first_name" class="text-red-500 text-xs mt-1">{{ form.errors.first_name }}</p>
                            </div>

                            <!-- Отчество -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Отчество
                                </label>
                                <input
                                    v-model="form.patronymic"
                                    type="text"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 dark:bg-gray-700 dark:text-white"
                                    placeholder="Введите отчество"
                                />
                                <p v-if="form.errors.patronymic" class="text-red-500 text-xs mt-1">{{ form.errors.patronymic }}</p>
                            </div>

                            <!-- Никнейм (только для просмотра) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Никнейм
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <input
                                        :value="'@' + (user?.nickname || '—')"
                                        type="text"
                                        disabled
                                        class="w-full pl-10 pr-4 py-2 bg-gray-100 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg cursor-not-allowed text-gray-600 dark:text-gray-300"
                                    />
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Никнейм генерируется автоматически и не может быть изменен</p>
                            </div>

                            <!-- Должность (только для просмотра) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Должность
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <input
                                        :value="user?.position_name || user?.position || '—'"
                                        type="text"
                                        disabled
                                        class="w-full pl-10 pr-4 py-2 bg-gray-100 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg cursor-not-allowed text-gray-600 dark:text-gray-300"
                                    />
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Для изменения должности обратитесь к администратору</p>
                            </div>

                            <!-- Отдел (только для просмотра) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Отдел
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                    <input
                                        :value="user?.department_name || user?.department || '—'"
                                        type="text"
                                        disabled
                                        class="w-full pl-10 pr-4 py-2 bg-gray-100 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg cursor-not-allowed text-gray-600 dark:text-gray-300"
                                    />
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Для изменения отдела обратитесь к администратору</p>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    :class="form.errors.email ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                    placeholder="example@sybox.ru"
                                />
                            </div>
                            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Допустимые домены: @sybox.ru, @uralkarton.ru, @yandex.ru
                                <span v-if="user?.email !== form.email" class="text-yellow-600 dark:text-yellow-500 block mt-1">
                                    ⚠️ При изменении email потребуется повторное подтверждение
                                </span>
                            </p>
                        </div>

                        <!-- Кнопки действий -->
                        <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <button
                                type="button"
                                @click="resetForm"
                                class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                            >
                                Отмена
                            </button>
                            <button
                                type="submit"
                                class="px-6 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg transition shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="form.processing"
                            >
                                <svg v-if="form.processing" class="animate-spin inline w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Сохранение...' : 'Сохранить изменения' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Безопасность аккаунта -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl overflow-hidden transition-colors duration-300 mb-6">
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 dark:from-purple-600 dark:to-purple-700 px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6-4h12a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6a2 2 0 012-2zm10-4V6a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-white">Безопасность аккаунта</h2>
                                <p class="text-purple-100 text-sm">Управление паролем и безопасностью</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Изменение пароля -->
                        <div class="mb-6">
                            <button
                                @click="showChangePassword = !showChangePassword"
                                class="flex items-center space-x-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 0121 9z"></path>
                                </svg>
                                <span>{{ showChangePassword ? 'Скрыть форму' : 'Изменить пароль' }}</span>
                            </button>

                            <transition
                                enter-active-class="transition-all duration-300 ease-out"
                                enter-from-class="opacity-0 transform -translate-y-4"
                                enter-to-class="opacity-100 transform translate-y-0"
                                leave-active-class="transition-all duration-200 ease-in"
                                leave-from-class="opacity-100 transform translate-y-0"
                                leave-to-class="opacity-0 transform -translate-y-4"
                            >
                                <div v-if="showChangePassword" class="mt-4 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Текущий пароль <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6-4h12a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6a2 2 0 012-2zm10-4V6a4 4 0 00-8 0v4h8z"></path>
                                                </svg>
                                            </div>
                                            <input
                                                v-model="passwordForm.current_password"
                                                :type="showCurrentPassword ? 'text' : 'password'"
                                                class="w-full pl-10 pr-10 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                :class="passwordForm.errors.current_password ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                                placeholder="Введите текущий пароль"
                                            />
                                            <button
                                                type="button"
                                                @click="showCurrentPassword = !showCurrentPassword"
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
                                            >
                                                <svg v-if="!showCurrentPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <p v-if="passwordForm.errors.current_password" class="text-red-500 text-xs mt-1">{{ passwordForm.errors.current_password }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Новый пароль <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6-4h12a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6a2 2 0 012-2zm10-4V6a4 4 0 00-8 0v4h8z"></path>
                                                </svg>
                                            </div>
                                            <input
                                                v-model="passwordForm.new_password"
                                                :type="showNewPassword ? 'text' : 'password'"
                                                class="w-full pl-10 pr-10 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                :class="passwordForm.errors.new_password ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                                placeholder="Минимум 8 символов"
                                            />
                                            <button
                                                type="button"
                                                @click="showNewPassword = !showNewPassword"
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
                                            >
                                                <svg v-if="!showNewPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <p v-if="passwordForm.errors.new_password" class="text-red-500 text-xs mt-1">{{ passwordForm.errors.new_password }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Пароль должен содержать минимум 8 символов</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Подтверждение пароля <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                            <input
                                                v-model="passwordForm.new_password_confirmation"
                                                :type="showConfirmPassword ? 'text' : 'password'"
                                                class="w-full pl-10 pr-10 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                :class="passwordForm.errors.new_password_confirmation ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                                placeholder="Повторите новый пароль"
                                            />
                                            <button
                                                type="button"
                                                @click="showConfirmPassword = !showConfirmPassword"
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
                                            >
                                                <svg v-if="!showConfirmPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <p v-if="passwordForm.errors.new_password_confirmation" class="text-red-500 text-xs mt-1">{{ passwordForm.errors.new_password_confirmation }}</p>
                                    </div>

                                    <div class="flex justify-end">
                                        <button
                                            @click="updatePassword"
                                            class="px-6 py-2 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white rounded-lg transition shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                            :disabled="passwordForm.processing"
                                        >
                                            <svg v-if="passwordForm.processing" class="animate-spin inline w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            {{ passwordForm.processing ? 'Изменение...' : 'Изменить пароль' }}
                                        </button>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>
                </div>

                <!-- Опасная зона -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl overflow-hidden transition-colors duration-300">
                    <div class="bg-gradient-to-r from-red-500 to-red-600 dark:from-red-600 dark:to-red-700 px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-white">Опасная зона</h2>
                                <p class="text-red-100 text-sm">Необратимые действия с аккаунтом</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Удаление аккаунта</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    После удаления аккаунта все ваши данные будут безвозвратно удалены.
                                    Это действие невозможно отменить.
                                </p>
                            </div>
                            <button
                                @click="showDeleteConfirm = true"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition shadow-md"
                            >
                                Удалить
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно подтверждения удаления -->
        <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50 p-4" @click.self="closeDeleteModal">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md transition-colors duration-300">
                <div class="bg-gradient-to-r from-red-500 to-red-600 dark:from-red-600 dark:to-red-700 px-6 py-4 rounded-t-lg">
                    <h2 class="text-xl font-bold text-white">Подтверждение удаления</h2>
                </div>

                <form @submit.prevent="deleteAccount" class="p-6">
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Вы уверены, что хотите удалить свой аккаунт? Это действие необратимо.
                    </p>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Введите пароль для подтверждения <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6-4h12a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6a2 2 0 012-2zm10-4V6a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input
                                v-model="deleteForm.password"
                                :type="showDeletePassword ? 'text' : 'password'"
                                class="w-full pl-10 pr-10 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                :class="deleteForm.errors.password ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                placeholder="Введите пароль"
                            />
                            <button
                                type="button"
                                @click="showDeletePassword = !showDeletePassword"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
                            >
                                <svg v-if="!showDeletePassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            </button>
                        </div>
                        <p v-if="deleteForm.errors.password" class="text-red-500 text-xs mt-1">{{ deleteForm.errors.password }}</p>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="closeDeleteModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                        >
                            Отмена
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="deleteForm.processing"
                        >
                            <svg v-if="deleteForm.processing" class="animate-spin inline w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ deleteForm.processing ? 'Удаление...' : 'Да, удалить аккаунт' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, useForm, usePage, router } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const page = usePage()
const user = computed(() => page.props.auth?.user ?? null)

// Форма для обновления профиля
const form = useForm({
    last_name: '',
    first_name: '',
    patronymic: '',
    email: '',
    position: '',
    department: ''
})

// Форма для смены пароля
const passwordForm = useForm({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
})

// Форма для удаления аккаунта
const deleteForm = useForm({
    password: '',
})

// Состояние UI
const showChangePassword = ref(false)
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)
const showDeleteConfirm = ref(false)
const showDeletePassword = ref(false)

// Следим за изменениями пользователя и обновляем форму
watch(user, (newUser) => {
    if (newUser) {
        form.last_name = newUser.last_name || ''
        form.first_name = newUser.first_name || ''
        form.patronymic = newUser.patronymic || ''
        form.email = newUser.email || ''
        form.position = newUser.position_name || newUser.position || ''
        form.department = newUser.department_name || newUser.department || ''
    }
}, { immediate: true, deep: true })

// Обновление профиля
const updateProfile = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // После успешного обновления, обновляем форму из актуальных данных пользователя
            if (user.value) {
                form.last_name = user.value.last_name || ''
                form.first_name = user.value.first_name || ''
                form.patronymic = user.value.patronymic || ''
                form.email = user.value.email || ''
                form.position = user.value.position_name || user.value.position || ''
                form.department = user.value.department_name || user.value.department || ''
            }
            form.clearErrors()
        }
    })
}

// Сброс формы
const resetForm = () => {
    if (user.value) {
        form.last_name = user.value.last_name || ''
        form.first_name = user.value.first_name || ''
        form.patronymic = user.value.patronymic || ''
        form.email = user.value.email || ''
        form.position = user.value.position_name || user.value.position || ''
        form.department = user.value.department_name || user.value.department || ''
    }
    form.clearErrors()
}

// Обновление пароля
const updatePassword = () => {
    passwordForm.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset()
            showChangePassword.value = false
            showCurrentPassword.value = false
            showNewPassword.value = false
            showConfirmPassword.value = false
        }
    })
}

// Удаление аккаунта
const deleteAccount = () => {
    deleteForm.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            closeDeleteModal()
        }
    })
}

// Закрытие модального окна
const closeDeleteModal = () => {
    showDeleteConfirm.value = false
    deleteForm.reset()
    deleteForm.clearErrors()
    showDeletePassword.value = false
}
</script>

<style scoped>
@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Плавные переходы для модального окна */
.fixed {
    transition: background-color 0.3s ease;
}
</style>
