<template>
    <app-layout>
        <Head title="Регистрация" />

        <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl w-full bg-white rounded-lg shadow-xl p-8">
                <h2 class="text-center text-3xl font-extrabold text-gray-900 mb-6">Регистрация</h2>

                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Фамилия -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Фамилия <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.last_name"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.last_name }"
                            />
                            <p v-if="form.errors.last_name" class="text-red-500 text-xs mt-1">{{ form.errors.last_name }}</p>
                        </div>

                        <!-- Имя -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Имя <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.first_name"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.first_name }"
                            />
                            <p v-if="form.errors.first_name" class="text-red-500 text-xs mt-1">{{ form.errors.first_name }}</p>
                        </div>

                        <!-- Отчество -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Отчество
                            </label>
                            <input
                                v-model="form.patronymic"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.patronymic }"
                            />
                            <p v-if="form.errors.patronymic" class="text-red-500 text-xs mt-1">{{ form.errors.patronymic }}</p>
                        </div>

                        <!-- Должность -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Должность <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.position"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.position }"
                            />
                            <p v-if="form.errors.position" class="text-red-500 text-xs mt-1">{{ form.errors.position }}</p>
                        </div>

                        <!-- Отдел -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Отдел <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.department"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.department }"
                            />
                            <p v-if="form.errors.department" class="text-red-500 text-xs mt-1">{{ form.errors.department }}</p>
                        </div>

                        <!-- Email -->
                        <div class="mb-4 md:col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.email }"
                            />
                            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                            <p class="text-gray-500 text-xs mt-1">
                                Допустимые домены: @sybox.ru, @uralkarton.ru, @yandex.ru
                            </p>
                        </div>

                        <!-- Пароль -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Пароль <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.password"
                                type="password"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.password }"
                            />
                            <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                            <p class="text-gray-500 text-xs mt-1">Минимум 8 символов</p>
                        </div>

                        <!-- Подтверждение пароля -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Подтверждение пароля <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.password_confirmation }"
                            />
                            <p v-if="form.errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ form.errors.password_confirmation }}</p>
                        </div>
                    </div>

                    <!-- Информация о никнейме -->
                    <div class="mt-2 mb-4 p-3 bg-blue-50 rounded-md">
                        <p class="text-sm text-blue-800">
                            <strong>Информация:</strong> Ваш никнейм будет автоматически сгенерирован из email (например, из email a.ivanov@sybox.ru будет создан никнейм a.ivanov)
                        </p>
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition duration-300"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Регистрация...' : 'Зарегистрироваться' }}
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <Link :href="route('login')" class="text-sm text-blue-600 hover:text-blue-500">
                        Уже есть аккаунт? Войти
                    </Link>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from "@/Layouts/AppLayout.vue";

const form = useForm({
    last_name: '',
    first_name: '',
    patronymic: '',
    position: '',
    department: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const submit = () => {
    form.post(route('register'))
}
</script>
