<template>
    <div class="stat-card">

        <span class="title">
            {{ title }}
        </span>

        <h2>{{ value }}</h2>

    </div>
</template>

<script setup>
defineProps({
    title: String,
    value: [String, Number]
})
</script>

<style scoped>
.stat-card {
    background: white;
    padding: 20px;
    border-radius: 16px;
    text-align: center;
}

.title {
    color: gray;
}
</style>