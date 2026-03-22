<template>
    <app-layout>
        <Head title="Подтверждение email" />

        <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full bg-white rounded-lg shadow-xl p-8 text-center">
                <div class="mb-6">
                    <svg class="mx-auto h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>

                <h2 class="text-2xl font-bold mb-4 text-gray-900">Подтверждение email</h2>

                <p class="mb-4 text-gray-600">
                    На ваш email отправлено письмо для подтверждения. Пожалуйста, перейдите по ссылке в письме.
                </p>

                <form @submit.prevent="resend" class="space-y-4">
                    <button
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Отправка...' : 'Отправить повторно' }}
                    </button>
                </form>

                <div class="mt-6 pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-500">
                        Если письмо не пришло, проверьте папку «Спам» или запросите повторную отправку.
                    </p>
                </div>

                <div class="mt-4">
                    <Link :href="route('logout')" method="post" as="button" class="text-sm text-gray-500 hover:text-gray-700">
                        Выйти из аккаунта
                    </Link>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const form = useForm({})

const resend = () => {
    form.post(route('verification.send'))
}
</script>
