<template>
    <div v-if="links.length > 3" class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <!-- Информация о количестве записей -->
        <div class="text-sm text-gray-600 dark:text-gray-400">
            Показано с <span class="font-medium text-gray-900 dark:text-white">{{ from }}</span>
            по <span class="font-medium text-gray-900 dark:text-white">{{ to }}</span>
            из <span class="font-medium text-gray-900 dark:text-white">{{ total }}</span> записей
        </div>

        <!-- Пагинация -->
        <div class="flex items-center gap-1">
            <!-- Первая страница -->
            <Link
                v-if="hasPreviousPages"
                :href="firstPageUrl"
                class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-400 dark:hover:border-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:focus:ring-offset-gray-800 transition-all duration-200 hover:scale-105"
                title="Первая страница"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
            </Link>

            <!-- Предыдущая страница -->
            <Link
                v-if="prevPageUrl"
                :href="prevPageUrl"
                class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-400 dark:hover:border-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:focus:ring-offset-gray-800 transition-all duration-200 hover:scale-105"
                title="Предыдущая страница"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </Link>
            <button
                v-else
                class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 opacity-50 cursor-not-allowed transition-all duration-200"
                disabled
                title="Предыдущая страница"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>

            <!-- Номера страниц -->
            <template v-for="page in visiblePages" :key="page">
                <Link
                    v-if="page !== '...'"
                    :href="getPageUrl(page)"
                    class="inline-flex items-center justify-center min-w-[40px] h-10 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200"
                    :class="page === currentPage
                        ? 'bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 text-white border-transparent shadow-md hover:shadow-lg cursor-default'
                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-400 dark:hover:border-gray-500 hover:scale-105'"
                >
                    {{ page }}
                </Link>
                <span
                    v-else
                    class="px-3 py-2 text-gray-500 dark:text-gray-400 select-none"
                >
                    ...
                </span>
            </template>

            <!-- Следующая страница -->
            <Link
                v-if="nextPageUrl"
                :href="nextPageUrl"
                class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-400 dark:hover:border-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:focus:ring-offset-gray-800 transition-all duration-200 hover:scale-105"
                title="Следующая страница"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </Link>
            <button
                v-else
                class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 opacity-50 cursor-not-allowed transition-all duration-200"
                disabled
                title="Следующая страница"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>

            <!-- Последняя страница -->
            <Link
                v-if="hasNextPages"
                :href="lastPageUrl"
                class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-400 dark:hover:border-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:focus:ring-offset-gray-800 transition-all duration-200 hover:scale-105"
                title="Последняя страница"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                </svg>
            </Link>
        </div>
    </div>

    <!-- Компактная версия для мобильных устройств -->
    <div v-else-if="links.length > 1" class="flex items-center justify-center gap-2">
        <Link
            v-if="prevPageUrl"
            :href="prevPageUrl"
            class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-400 dark:hover:border-gray-500 transition-all duration-200"
            title="Предыдущая страница"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </Link>

        <span class="text-sm text-gray-600 dark:text-gray-400">
            Страница <span class="font-medium text-gray-900 dark:text-white">{{ currentPage }}</span>
            из <span class="font-medium text-gray-900 dark:text-white">{{ lastPage }}</span>
        </span>

        <Link
            v-if="nextPageUrl"
            :href="nextPageUrl"
            class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-400 dark:hover:border-gray-500 transition-all duration-200"
            title="Следующая страница"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </Link>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    links: {
        type: Array,
        required: true,
        default: () => []
    },
    from: {
        type: Number,
        default: null
    },
    to: {
        type: Number,
        default: null
    },
    total: {
        type: Number,
        default: null
    }
})

// Определяем текущую страницу и общее количество страниц
const currentPage = computed(() => {
    const activeLink = props.links.find(link => link.active)
    if (activeLink && activeLink.label) {
        const match = activeLink.label.match(/\d+/)
        return match ? parseInt(match[0]) : 1
    }
    return 1
})

const lastPage = computed(() => {
    const lastLink = props.links[props.links.length - 2]
    if (lastLink && lastLink.label) {
        const match = lastLink.label.match(/\d+/)
        return match ? parseInt(match[0]) : 1
    }
    return 1
})

// URL для навигации
const prevPageUrl = computed(() => {
    const prevLink = props.links.find(link => link.label === '&laquo; Previous' || link.label === '‹')
    return prevLink?.url || null
})

const nextPageUrl = computed(() => {
    const nextLink = props.links.find(link => link.label === 'Next &raquo;' || link.label === '›')
    return nextLink?.url || null
})

const firstPageUrl = computed(() => {
    const firstLink = props.links[0]
    return firstLink?.url || null
})

const lastPageUrl = computed(() => {
    const lastLink = props.links[props.links.length - 1]
    return lastLink?.url || null
})

const hasPreviousPages = computed(() => {
    return currentPage.value > 1
})

const hasNextPages = computed(() => {
    return currentPage.value < lastPage.value
})

// Формируем видимые страницы (с ...)
const visiblePages = computed(() => {
    const current = currentPage.value
    const last = lastPage.value
    const delta = 2

    const range = []
    const rangeWithDots = []
    let l

    for (let i = 1; i <= last; i++) {
        if (i === 1 || i === last || (i >= current - delta && i <= current + delta)) {
            range.push(i)
        }
    }

    range.forEach((i) => {
        if (l) {
            if (i - l === 2) {
                rangeWithDots.push(l + 1)
            } else if (i - l !== 1) {
                rangeWithDots.push('...')
            }
        }
        rangeWithDots.push(i)
        l = i
    })

    return rangeWithDots
})

// Получаем URL для конкретной страницы
const getPageUrl = (page) => {
    const link = props.links.find(l => l.label === String(page))
    return link?.url || null
}
</script>

<style scoped>
/* Анимация при наведении */
.hover\:scale-105:hover {
    transform: scale(1.05);
}

/* Плавное появление */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.inline-flex {
    animation: fadeIn 0.2s ease-out;
}
</style>
