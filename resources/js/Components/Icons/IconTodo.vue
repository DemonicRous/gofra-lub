<template>
    <svg
        :class="className"
        :fill="fill"
        :stroke="stroke"
        viewBox="0 0 24 24"
        xmlns="http://www.w3.org/2000/svg"
    >
        <slot />
    </svg>
</template>

<script setup>
defineProps({
    className: {
        type: String,
        default: 'w-5 h-5'
    },
    fill: {
        type: String,
        default: 'none'
    },
    stroke: {
        type: String,
        default: 'currentColor'
    }
})
</script>
