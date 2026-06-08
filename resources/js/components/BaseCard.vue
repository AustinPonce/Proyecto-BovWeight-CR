<template>

    <div
        class="bg-white rounded-2xl p-4 shadow-[0px_8px_24px_rgba(0,0,0,0.08)] border border-gray-100"
    >
        <slot />
    </div>

</template>
<style scoped>
.card {
    background: white;
    border-radius: 16px;
    padding: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
}
</style>