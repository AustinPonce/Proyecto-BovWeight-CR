<template>

    <div class="min-h-screen flex flex-col relative">

        <AppHeader
            title="Ganadería"
            iconRight="person"
        />

        <main
            class="flex-1 flex items-center justify-center px-5 pb-24"
        >

            <BaseCard
                class="max-w-md w-full text-center"
            >

                <div
                    class="w-20 h-20 mx-auto rounded-full bg-gray-100 flex items-center justify-center mb-4"
                >
                    <span
                        class="material-symbols-outlined text-5xl text-gray-400"
                    >
                        notifications_off
                    </span>
                </div>

                <h2
                    class="text-xl font-semibold text-gray-700"
                >
                    No tienes notificaciones
                </h2>

                <p
                    class="text-gray-500 mt-3"
                >
                    Te avisaremos cuando haya novedades importantes sobre tu ganado o tareas pendientes.
                </p>

            </BaseCard>

        </main>

        <BottomNav />

    </div>

</template>

<script setup>
import AppHeader from '../../components/AppHeader.vue'
import BaseCard from '../../components/BaseCard.vue'
import BottomNav from '../../components/BottomNav.vue'
</script>