<template>
    <AppLayout>
        <Head title="Регистрация" />

        <div class="bg-gray-50 dark:bg-gray-900 min-h-screen">
            <div class="container mx-auto px-4 py-8 pt-5 md:pt-8">
                <div class="max-w-2xl mx-auto">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 md:p-8 transition-colors duration-300">
                        <div class="text-center mb-6">
                            <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white">
                                Регистрация
                            </h2>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                Заполните форму для создания аккаунта
                            </p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Фамилия -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                        Фамилия <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.last_name"
                                        type="text"
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        :class="form.errors.last_name ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                        placeholder="Введите фамилию"
                                    />
                                    <p v-if="form.errors.last_name" class="text-red-500 text-xs mt-1">{{ form.errors.last_name }}</p>
                                </div>

                                <!-- Имя -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                        Имя <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.first_name"
                                        type="text"
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        :class="form.errors.first_name ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                        placeholder="Введите имя"
                                    />
                                    <p v-if="form.errors.first_name" class="text-red-500 text-xs mt-1">{{ form.errors.first_name }}</p>
                                </div>

                                <!-- Отчество -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                        Отчество
                                    </label>
                                    <input
                                        v-model="form.patronymic"
                                        type="text"
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        :class="form.errors.patronymic ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                        placeholder="Введите отчество"
                                    />
                                    <p v-if="form.errors.patronymic" class="text-red-500 text-xs mt-1">{{ form.errors.patronymic }}</p>
                                </div>

                                <!-- Отдел -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                        Отдел <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.department_id"
                                        @change="onDepartmentChange"
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        :class="form.errors.department_id ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                    >
                                        <option value="">Выберите отдел</option>
                                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                            {{ dept.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.department_id" class="text-red-500 text-xs mt-1">{{ form.errors.department_id }}</p>
                                </div>

                                <!-- Должность -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                        Должность <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.position_id"
                                        :disabled="!form.department_id"
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                                        :class="form.errors.position_id ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                    >
                                        <option value="">
                                            {{ form.department_id ? 'Выберите должность' : 'Сначала выберите отдел' }}
                                        </option>
                                        <option v-for="pos in getPositionsForDepartment" :key="pos.id" :value="pos.id">
                                            {{ pos.name }} ({{ getLevelName(pos.level) }})
                                        </option>
                                    </select>
                                    <p v-if="form.errors.position_id" class="text-red-500 text-xs mt-1">{{ form.errors.position_id }}</p>
                                </div>

                                <!-- Email -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                        Email <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        :class="form.errors.email ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                        placeholder="example@sybox.ru"
                                    />
                                    <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                                    <p class="text-gray-500 dark:text-gray-400 text-xs mt-1">
                                        Допустимые домены: @sybox.ru, @uralkarton.ru, @yandex.ru
                                    </p>
                                </div>

                                <!-- Пароль -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                        Пароль <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            v-model="form.password"
                                            :type="showPassword ? 'text' : 'password'"
                                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white pr-10"
                                            :class="form.errors.password ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                            placeholder="Минимум 8 символов"
                                        />
                                        <button
                                            type="button"
                                            @click="showPassword = !showPassword"
                                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
                                        >
                                            <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                                    <p class="text-gray-500 dark:text-gray-400 text-xs mt-1">
                                        Пароль должен содержать минимум 8 символов
                                    </p>
                                </div>

                                <!-- Подтверждение пароля -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                        Подтверждение пароля <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.password_confirmation"
                                        type="password"
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        :class="form.errors.password_confirmation ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                        placeholder="Повторите пароль"
                                    />
                                    <p v-if="form.errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ form.errors.password_confirmation }}</p>
                                </div>
                            </div>

                            <!-- Информация о никнейме -->
                            <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-md">
                                <div class="flex items-start space-x-2">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-sm text-blue-800 dark:text-blue-300">
                                        <strong>Информация:</strong> Ваш никнейм будет автоматически сгенерирован из email
                                        (например, из email a.chernov@mail.ru будет создан никнейм a.chernov)
                                    </p>
                                </div>
                            </div>

                            <button
                                type="submit"
                                class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold py-2 px-4 rounded-md transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="form.processing"
                            >
                                <svg v-if="form.processing" class="animate-spin inline w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Регистрация...' : 'Зарегистрироваться' }}
                            </button>
                        </form>

                        <div class="mt-6 text-center">
                            <Link :href="route('login')" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300 transition">
                                Уже есть аккаунт? Войти
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    departments: {
        type: Array,
        default: () => []
    },
    allPositions: {
        type: Array,
        default: () => []
    }
})

const form = useForm({
    last_name: '',
    first_name: '',
    patronymic: '',
    department_id: '',
    position_id: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const showPassword = ref(false)

const getLevelName = (level) => {
    const levels = {
        'junior': 'Младший',
        'middle': 'Специалист',
        'senior': 'Старший',
        'lead': 'Ведущий',
        'head': 'Руководитель'
    }
    return levels[level] || level
}

// Получаем должности для выбранного отдела из переданных данных
const getPositionsForDepartment = computed(() => {
    if (!form.department_id) return []
    return props.allPositions.filter(pos => pos.department_id === parseInt(form.department_id))
})

// Обработчик изменения отдела
const onDepartmentChange = () => {
    form.position_id = ''
}

const submit = () => {
    form.post(route('register'), {
        onError: (errors) => {
            console.error('Ошибки валидации:', errors)
        }
    })
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
</style>
