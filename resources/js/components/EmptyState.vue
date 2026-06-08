<template>
    <div class="empty-state">

        <div class="icon">
            {{ icon }}
        </div>

        <h3>{{ title }}</h3>

        <p>{{ description }}</p>

    </div>
</template>

<script setup>
defineProps({
    icon: String,
    title: String,
    description: String
})
</script>

<style scoped>
.empty-state {
    text-align: center;
    padding: 40px 20px;
}
</style>