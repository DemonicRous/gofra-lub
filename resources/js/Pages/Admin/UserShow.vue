<template>
    <AppLayout>
        <Head :title="`Пользователь: ${user.full_name}`" />

        <div class="container mx-auto px-4 py-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl overflow-hidden transition-colors duration-300">
                <!-- Заголовок -->
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 px-6 py-4">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-white">Профиль пользователя</h1>
                            <p class="text-blue-100 mt-1">Детальная информация</p>
                        </div>
                        <Link :href="route('admin.users')" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-md transition">
                            ← Назад к списку
                        </Link>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Основная информация -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Личная информация -->
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 transition-colors duration-300">
                                <h2 class="text-lg font-semibold mb-4 flex items-center text-gray-900 dark:text-white">
                                    <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Личная информация
                                </h2>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm text-gray-500 dark:text-gray-400">Фамилия</label>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ user.last_name || '—' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-500 dark:text-gray-400">Имя</label>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ user.first_name || '—' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-500 dark:text-gray-400">Отчество</label>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ user.patronymic || '—' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-500 dark:text-gray-400">Полное имя</label>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ user.full_name || '—' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-500 dark:text-gray-400">Короткое имя</label>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ user.short_name || '—' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-500 dark:text-gray-400">Никнейм</label>
                                        <p class="font-medium text-blue-600 dark:text-blue-400">@{{ user.nickname || '—' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Рабочая информация -->
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 transition-colors duration-300">
                                <h2 class="text-lg font-semibold mb-4 flex items-center text-gray-900 dark:text-white">
                                    <svg class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    Рабочая информация
                                </h2>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm text-gray-500 dark:text-gray-400">Должность</label>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ user.position_name || user.position || '—' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-500 dark:text-gray-400">Отдел</label>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ user.department_name || user.department || '—' }}</p>
                                    </div>
                                    <div v-if="user.position_level">
                                        <label class="text-sm text-gray-500 dark:text-gray-400">Уровень должности</label>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ getLevelName(user.position_level) }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-500 dark:text-gray-400">Подотдел для баллов</label>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ user.scoring_department === 'constructor' ? 'Конструктор' : (user.scoring_department === 'designer' ? 'Дизайнер' : 'Не назначен') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Контактная информация -->
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 transition-colors duration-300">
                                <h2 class="text-lg font-semibold mb-4 flex items-center text-gray-900 dark:text-white">
                                    <svg class="w-5 h-5 mr-2 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    Контактная информация
                                </h2>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="text-sm text-gray-500 dark:text-gray-400">Email</label>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ user.email || '—' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Боковая панель с статусами и действиями -->
                        <div class="space-y-6">
                            <!-- Статусы -->
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 transition-colors duration-300">
                                <h2 class="text-lg font-semibold mb-4 flex items-center text-gray-900 dark:text-white">
                                    <svg class="w-5 h-5 mr-2 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Статусы
                                </h2>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Роль:</span>
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="getRoleBadgeClass(user.role)">
                                            {{ getUserRoleName(user.role) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Email подтвержден:</span>
                                        <span :class="user.email_verified_at ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" class="font-medium">
                                            {{ user.email_verified_at ? 'Да' : 'Нет' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Аккаунт одобрен:</span>
                                        <span :class="user.approved_at ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400'" class="font-medium">
                                            {{ user.approved_at ? 'Да' : 'Нет' }}
                                        </span>
                                    </div>
                                    <div v-if="user.approved_at" class="pt-2 border-t border-gray-200 dark:border-gray-600">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Дата одобрения:</span>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(user.approved_at) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Даты регистрации -->
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 transition-colors duration-300">
                                <h2 class="text-lg font-semibold mb-4 flex items-center text-gray-900 dark:text-white">
                                    <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Даты
                                </h2>
                                <div class="space-y-3">
                                    <div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Дата регистрации:</span>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(user.created_at) }}</p>
                                    </div>
                                    <div v-if="user.email_verified_at">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Email подтвержден:</span>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(user.email_verified_at) }}</p>
                                    </div>
                                    <div v-if="user.updated_at !== user.created_at">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Последнее обновление:</span>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(user.updated_at) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Действия -->
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 transition-colors duration-300">
                                <h2 class="text-lg font-semibold mb-4 flex items-center text-gray-900 dark:text-white">
                                    <svg class="w-5 h-5 mr-2 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Действия
                                </h2>
                                <div class="space-y-3">
                                    <button
                                        v-if="!user.approved_at && user.email_verified_at"
                                        @click="approveUser"
                                        class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition"
                                    >
                                        Одобрить пользователя
                                    </button>
                                    <button
                                        @click="editUser"
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition"
                                    >
                                        Редактировать
                                    </button>
                                    <button
                                        @click="deleteUser"
                                        class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition"
                                    >
                                        Удалить пользователя
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно редактирования -->
        <div v-if="showEditModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50 p-4" @click.self="closeModal">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto transition-colors duration-300">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 px-6 py-4 sticky top-0">
                    <h2 class="text-xl font-bold text-white">Редактирование пользователя</h2>
                </div>

                <form @submit.prevent="updateUser" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Фамилия *</label>
                            <input v-model="editForm.last_name" type="text" required
                                   class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Имя *</label>
                            <input v-model="editForm.first_name" type="text" required
                                   class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Отчество</label>
                            <input v-model="editForm.patronymic" type="text"
                                   class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Никнейм *</label>
                            <input v-model="editForm.nickname" type="text" required
                                   class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Должность *</label>
                            <select v-model="editForm.position_id" required
                                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Выберите должность</option>
                                <option v-for="position in allPositions" :key="position.id" :value="position.id">
                                    {{ position.name }} ({{ getLevelName(position.level) }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Отдел *</label>
                            <select v-model="editForm.department_id" required
                                    @change="onDepartmentChange"
                                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Выберите отдел</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                    {{ dept.name }}
                                </option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email *</label>
                            <input v-model="editForm.email" type="email" required
                                   class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Роль</label>
                            <select v-model="editForm.role" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="user">Пользователь</option>
                                <option value="manager">Менеджер</option>
                                <option value="admin">Администратор</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Подотдел для баллов</label>
                            <select v-model="editForm.scoring_department" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Не выбран</option>
                                <option value="constructor">Конструктор</option>
                                <option value="designer">Дизайнер</option>
                            </select>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Выберите подотдел для начисления баллов.</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t dark:border-gray-700">
                        <button type="button" @click="closeModal"
                                class="px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 rounded-md transition">
                            Отмена
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition"
                                :disabled="updating">
                            {{ updating ? 'Сохранение...' : 'Сохранить' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    user: {
        type: Object,
        required: true
    },
    departments: {
        type: Array,
        default: () => []
    },
    allPositions: {
        type: Array,
        default: () => []
    }
})

const showEditModal = ref(false)
const updating = ref(false)

const editForm = ref({
    last_name: props.user.last_name,
    first_name: props.user.first_name,
    patronymic: props.user.patronymic,
    nickname: props.user.nickname,
    department_id: props.user.department_id,
    position_id: props.user.position_id,
    email: props.user.email,
    role: props.user.role,
    scoring_department: props.user.scoring_department || ''
})

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

const getUserRoleName = (role) => {
    const roles = {
        'admin': 'Администратор',
        'manager': 'Менеджер',
        'user': 'Пользователь'
    }
    return roles[role] || 'Пользователь'
}

const getRoleBadgeClass = (role) => {
    const classes = {
        'admin': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
        'manager': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        'user': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    }
    return classes[role] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const onDepartmentChange = () => {
    editForm.value.position_id = ''
}

const approveUser = () => {
    if (confirm('Одобрить этого пользователя?')) {
        router.post(route('admin.users.approve', props.user.id), {}, {
            preserveScroll: true
        })
    }
}

const editUser = () => {
    showEditModal.value = true
}

const updateUser = () => {
    updating.value = true
    router.put(route('admin.users.update', props.user.id), editForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal()
            updating.value = false
        },
        onError: () => {
            updating.value = false
        }
    })
}

const deleteUser = () => {
    if (confirm('Вы уверены, что хотите удалить этого пользователя? Это действие необратимо.')) {
        router.delete(route('admin.users.destroy', props.user.id), {
            preserveScroll: true
        })
    }
}

const closeModal = () => {
    showEditModal.value = false
    editForm.value = {
        last_name: props.user.last_name,
        first_name: props.user.first_name,
        patronymic: props.user.patronymic,
        nickname: props.user.nickname,
        department_id: props.user.department_id,
        position_id: props.user.position_id,
        email: props.user.email,
        role: props.user.role,
        scoring_department: props.user.scoring_department || ''
    }
}
</script>
