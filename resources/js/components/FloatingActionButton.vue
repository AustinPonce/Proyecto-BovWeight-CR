<template>

    <button
        class="fixed bottom-24 right-6 w-16 h-16 bg-[#2ecc71] text-white rounded-full shadow-xl flex items-center justify-center active:scale-95"
        @click="$emit('click')"
    >
        <span class="material-symbols-outlined">
            add
        </span>
    </button>

</template>

<style scoped>
.fab {
    position: fixed;
    bottom: 90px;
    right: 20px;

    width: 60px;
    height: 60px;

    border: none;
    border-radius: 50%;

    background: #0f766e;
    color: white;

    font-size: 24px;
    cursor: pointer;
}
</style>