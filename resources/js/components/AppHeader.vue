<template>

    <header
        class="sticky top-0 z-50 bg-green-700 text-white shadow-lg rounded-b-xl px-5 py-4 flex items-center justify-between"
    >

        <div class="flex items-center gap-3">

            <button
                v-if="showBack"
                @click="$router.back()"
                class="p-2 hover:bg-white/10 rounded-full"
            >
                <span class="material-symbols-outlined">
                    arrow_back
                </span>
            </button>

            <h1 class="text-xl font-bold">
                {{ title }}
            </h1>

        </div>

        <button
            v-if="iconRight"
            class="p-2 hover:bg-white/10 rounded-full"
        >
            <span class="material-symbols-outlined">
                {{ iconRight }}
            </span>
        </button>

    </header>

</template>

<script setup>
defineProps({
    title: String,
    iconRight: String,

    showBack: {
        type: Boolean,
        default: false
    }
})
</script>