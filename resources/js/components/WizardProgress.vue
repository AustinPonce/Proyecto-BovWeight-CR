<template>
    <div class="wizard">

        <div
            v-for="n in total"
            :key="n"
            class="step"
            :class="{ active: n <= current }"
        />

    </div>
</template>

<script setup>
defineProps({
    current: Number,
    total: Number
})
</script>

<style scoped>
.wizard {
    display: flex;
    gap: 10px;
}

.step {
    flex: 1;
    height: 8px;
    background: #e5e7eb;
    border-radius: 999px;
}

.active {
    background: #0f766e;
}
</style>