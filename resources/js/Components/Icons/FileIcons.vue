<!-- resources/js/Components/Icons/FileIcons.vue -->
<template>
    <!-- PDF Icon -->
    <svg v-if="type === 'pdf'" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" style="color: #e74c3c">
        <path d="M20 8.83V6c0-1.1-.9-2-2-2H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2v-2.17c0-.53-.21-1.04-.59-1.41L14.83 8.59c-.37-.38-.88-.59-1.41-.59H10V4H8v4H4v2h4v2h2V8h3.17l6 6H16v2h6v-2h-2.17l-4.83-4.83c-.55-.55-1.29-.86-2.07-.86H10V8h2.59c.17 0 .34.02.51.07l4.24 4.24c.27.27.44.65.44 1.04V20c0 .55-.45 1-1 1H5c-.55 0-1-.45-1-1V5c0-.55.45-1 1-1h10v2h2V4h2c.55 0 1 .45 1 1v2.83l2 2V6c0-1.1-.9-2-2-2h-2V2h-2v2h-2V2h-2v2h-2V2H8v2H6c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2v-2.17l-2-2z"/>
    </svg>

    <!-- Word Icon -->
    <svg v-else-if="type === 'word'" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" style="color: #2b5797">
        <path d="M4 4c0-1.1.9-2 2-2h12c1.1 0 2 .9 2 2v16c0 1.1-.9 2-2 2H6c-1.1 0-2-.9-2-2V4zm2 0v16h12V4H6zm2 2h8v2H8V6zm0 4h8v2H8v-2zm0 4h5v2H8v-2z"/>
    </svg>

    <!-- Excel Icon -->
    <svg v-else-if="type === 'excel'" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" style="color: #1e7145">
        <path d="M4 4c0-1.1.9-2 2-2h12c1.1 0 2 .9 2 2v16c0 1.1-.9 2-2 2H6c-1.1 0-2-.9-2-2V4zm2 0v16h12V4H6zm2 2h8v2H8V6zm0 4h8v2H8v-2zm3 4h5v2h-5v-2z"/>
    </svg>

    <!-- Image Icon -->
    <svg v-else-if="type === 'image'" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" style="color: #9b59b6">
        <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
    </svg>

    <!-- Default File Icon -->
    <svg v-else class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" style="color: #7f8c8d">
        <path d="M6 2c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6H6zm0 2h7v5h5v11H6V4z"/>
    </svg>
</template>

<script setup>
defineProps({
    type: {
        type: String,
        default: 'file'
    }
})
</script>
