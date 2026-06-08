<template>

    <button
        :type="type"
        :disabled="disabled"
        class="w-full bg-[#2ecc71] hover:bg-[#27ae60] text-white font-semibold py-4 px-6 rounded-full shadow-lg active:scale-95 transition-all flex items-center justify-center gap-2"
    >

        <slot />

        <span
            v-if="icon"
            class="material-symbols-outlined"
        >
            {{ icon }}
        </span>

    </button>

</template>

<script setup>
defineProps({
    icon: String,

    type: {
        type: String,
        default: 'button'
    },

    disabled: {
        type: Boolean,
        default: false
    }
})
</script>