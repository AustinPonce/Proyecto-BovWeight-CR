<template>
    <PageContainer>

        <AppHeader
            :title="title"
            :showBack="true"
        />

        <WizardProgress
            :current="currentStep"
            :total="2"
        />

        <div class="content">
            <slot />
        </div>

    </PageContainer>
</template>

<script setup>
import AppHeader from '../../../components/AppHeader.vue'
import PageContainer from '../../../components/PageContainer.vue'
import WizardProgress from '../../../components/WizardProgress.vue'

defineProps({
    title: String,
    currentStep: Number
})
</script>

<style scoped>
.content {
    margin-top: 20px;
}
</style>