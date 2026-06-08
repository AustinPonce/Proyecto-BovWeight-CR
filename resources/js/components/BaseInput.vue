<template>
    <div class="space-y-2">

        <label
            v-if="label"
            class="text-xs font-bold text-gray-500 ml-1"
        >
            {{ label }}
        </label>

        <div class="relative group">

            <span
                v-if="icon"
                class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-green-600 transition-colors"
            >
                {{ icon }}
            </span>

            <input
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
                :type="type"
                :placeholder="placeholder"
                class="w-full pl-12 pr-4 py-3 bg-gray-100 rounded-xl border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none transition-all"
            />

        </div>

    </div>
</template>

<script setup>
defineProps({
    modelValue: String,
    label: String,
    placeholder: String,
    icon: String,
    type: {
        type: String,
        default: 'text'
    }
})

defineEmits(['update:modelValue'])
</script>