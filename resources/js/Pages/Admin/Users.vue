<template>
    <AppLayout>
        <Head title="Управление пользователями" />

        <div class="container mx-auto px-4 py-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 transition-colors duration-300">
                <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Управление пользователями</h1>

                <!-- Статистика -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 transition-colors duration-300">
                        <div class="text-sm text-blue-600 dark:text-blue-400">Всего</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</div>
                    </div>
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 transition-colors duration-300">
                        <div class="text-sm text-yellow-600 dark:text-yellow-400">Ожидают</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.pending }}</div>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 transition-colors duration-300">
                        <div class="text-sm text-green-600 dark:text-green-400">Одобрены</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.approved }}</div>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 transition-colors duration-300">
                        <div class="text-sm text-purple-600 dark:text-purple-400">Подтверждены</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.verified }}</div>
                    </div>
                </div>

                <!-- Фильтры -->
                <div class="mb-6 flex flex-wrap gap-4">
                    <select
                        v-model="filters.status"
                        @change="applyFilters"
                        class="px-3 py-2 border rounded-md bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="pending">Ожидают одобрения</option>
                        <option value="approved">Одобренные</option>
                        <option value="all">Все пользователи</option>
                    </select>

                    <input
                        v-model="filters.search"
                        @input="debouncedSearch"
                        type="text"
                        placeholder="Поиск по ФИО, email, никнейму..."
                        class="flex-1 px-3 py-2 border rounded-md bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />

                    <button
                        @click="bulkApprove"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="selectedUsers.length === 0"
                    >
                        Одобрить выбранных ({{ selectedUsers.length }})
                    </button>

                    <button
                        @click="bulkDelete"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="selectedUsers.length === 0"
                    >
                        Удалить выбранных ({{ selectedUsers.length }})
                    </button>
                </div>

                <!-- Таблица пользователей -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50">
                            <th class="px-4 py-3 w-12">
                                <input
                                    type="checkbox"
                                    :checked="isAllSelectedOnPage"
                                    @change="toggleSelectAll"
                                    class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 cursor-pointer w-4 h-4"
                                />
                            </th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">ФИО</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Никнейм</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Email</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Должность</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Отдел</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Роль</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Статус</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Действия</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-4 py-3">
                                <input
                                    type="checkbox"
                                    :value="user.id"
                                    v-model="selectedUsers"
                                    class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 cursor-pointer w-4 h-4"
                                />
                            </td>
                            <td class="px-4 py-3">
                                <Link :href="route('admin.users.show', user.id)" class="text-sm text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition">
                                    {{ user.full_name }}
                                </Link>
                            </td>
                            <td class="px-4 py-3 text-sm text-blue-600 dark:text-blue-400">@{{ user.nickname }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ user.email }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ user.position_name || '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ user.department_name || '—' }}</td>
                            <td class="px-4 py-3">
                                <select
                                    :value="user.role"
                                    @change="changeRole(user.id, $event.target.value)"
                                    class="text-sm border rounded px-2 py-1 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="user">Пользователь</option>
                                    <option value="manager">Менеджер</option>
                                    <option value="admin">Администратор</option>
                                </select>
                            </td>
                            <td class="px-4 py-3">
                                    <span v-if="user.approved_at" class="px-2 py-1 text-xs rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                        Одобрен
                                    </span>
                                <span v-else-if="user.email_verified_at" class="px-2 py-1 text-xs rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">
                                        Ожидает
                                    </span>
                                <span v-else class="px-2 py-1 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                        Email не подтвержден
                                    </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <Link
                                        :href="route('admin.users.show', user.id)"
                                        class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-800/50 rounded-full transition"
                                        title="Просмотр"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </Link>
                                    <button
                                        v-if="!user.approved_at && user.email_verified_at"
                                        @click="approveUser(user.id)"
                                        class="inline-flex items-center justify-center w-8 h-8 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 hover:bg-green-200 dark:hover:bg-green-800/50 rounded-full transition"
                                        title="Одобрить"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                    <button
                                        @click="deleteUser(user.id)"
                                        class="inline-flex items-center justify-center w-8 h-8 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-800/50 rounded-full transition"
                                        title="Удалить"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="users.data?.length === 0">
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                Нет пользователей для отображения
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Пагинация -->
                <div class="mt-6">
                    <Pagination :links="users.links" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/UI/Pagination.vue'

const props = defineProps({
    users: {
        type: Object,
        required: true
    },
    stats: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({ status: 'pending', search: '' })
    }
})

const selectedUsers = ref([])

// Следим за изменением данных пользователей (пагинация, фильтры)
watch(() => props.users.data, () => {
    // Очищаем выбранных пользователей при смене страницы или фильтров
    selectedUsers.value = []
}, { deep: true })

// Проверка, выбраны ли все пользователи на текущей странице
const isAllSelectedOnPage = computed(() => {
    if (!props.users.data || props.users.data.length === 0) return false
    const currentPageUserIds = props.users.data.map(u => u.id)
    return currentPageUserIds.length > 0 && currentPageUserIds.every(id => selectedUsers.value.includes(id))
})

// Переключение выбора всех пользователей на странице
const toggleSelectAll = () => {
    if (!props.users.data || props.users.data.length === 0) return

    const currentPageUserIds = props.users.data.map(u => u.id)

    if (isAllSelectedOnPage.value) {
        // Снимаем выделение со всех на текущей странице
        selectedUsers.value = selectedUsers.value.filter(id => !currentPageUserIds.includes(id))
    } else {
        // Выделяем всех на текущей странице
        const newSelected = [...new Set([...selectedUsers.value, ...currentPageUserIds])]
        selectedUsers.value = newSelected
    }
}

let searchTimeout = null
const debouncedSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        applyFilters()
    }, 300)
}

const applyFilters = () => {
    router.get(route('admin.users'), {
        status: props.filters.status,
        search: props.filters.search
    }, {
        preserveState: true,
        preserveScroll: true
    })
}

const approveUser = (id) => {
    if (confirm('Одобрить этого пользователя?')) {
        router.post(route('admin.users.approve', id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                selectedUsers.value = selectedUsers.value.filter(uid => uid !== id)
            }
        })
    }
}

const deleteUser = (id) => {
    if (confirm('Вы уверены, что хотите удалить этого пользователя?')) {
        router.delete(route('admin.users.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                selectedUsers.value = selectedUsers.value.filter(uid => uid !== id)
            }
        })
    }
}

const changeRole = (id, role) => {
    router.post(route('admin.users.assign-role', id), { role }, {
        preserveScroll: true
    })
}

const bulkApprove = () => {
    if (selectedUsers.value.length === 0) {
        alert('Выберите пользователей для одобрения')
        return
    }
    if (confirm(`Одобрить ${selectedUsers.value.length} пользователей?`)) {
        router.post(route('admin.users.bulk-approve'), { user_ids: selectedUsers.value }, {
            preserveScroll: true,
            onSuccess: () => {
                selectedUsers.value = []
            }
        })
    }
}

const bulkDelete = () => {
    if (selectedUsers.value.length === 0) {
        alert('Выберите пользователей для удаления')
        return
    }
    if (confirm(`Удалить ${selectedUsers.value.length} пользователей? Это действие необратимо.`)) {
        router.delete(route('admin.users.bulk-destroy'), {
            data: { user_ids: selectedUsers.value },
            preserveScroll: true,
            onSuccess: () => {
                selectedUsers.value = []
            }
        })
    }
}
</script>

<style scoped>
/* Плавные переходы для таблицы */
tbody tr {
    transition: background-color 0.2s ease;
}

/* Стили для чекбоксов */
input[type="checkbox"] {
    cursor: pointer;
}

/* Адаптивность для мобильных устройств */
@media (max-width: 768px) {
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }

    table {
        font-size: 0.875rem;
    }

    td, th {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }

    .flex.gap-2 {
        gap: 0.5rem;
    }
}

/* Отключенные кнопки */
button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
