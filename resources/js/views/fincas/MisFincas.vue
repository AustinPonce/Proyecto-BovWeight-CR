<template>

    <div class="min-h-screen">

        <AppHeader
            title="Mis Fincas"
            iconRight="search"
            :showBack="true"
        />

        <main
            class="flex flex-col items-center justify-center px-5 pt-24 pb-32"
        >

            <BaseCard
                class="max-w-md w-full text-center p-8"
            >

                <div
                    class="w-24 h-24 mx-auto rounded-full bg-green-50 flex items-center justify-center mb-8"
                >
                    <span
                        class="material-symbols-outlined text-5xl text-green-700"
                    >
                        agriculture
                    </span>
                </div>

                <h2
                    class="text-xl font-semibold"
                >
                    Comienza tu viaje
                </h2>

                <p
                    class="text-gray-500 mt-3 mb-8"
                >
                    No hay fincas registradas en tu cuenta actualmente.
                    Registra tu primera propiedad para empezar a gestionar tu ganado.
                </p>

                <BaseButton icon="add">
                    Registrar Finca
                </BaseButton>

                <button
                    class="w-full mt-4 border-2 border-green-700 text-green-700 py-4 rounded-xl font-semibold"
                >
                    Importar Datos
                </button>

            </BaseCard>

            <div
                class="mt-6 bg-green-50 px-4 py-2 rounded-full flex items-center gap-2"
            >

                <span
                    class="material-symbols-outlined text-green-700"
                >
                    info
                </span>

                <span
                    class="text-sm text-green-700 font-medium"
                >
                    Aprende cómo gestionar tu primera finca
                </span>

            </div>

        </main>

        <BottomNav />

        <FloatingActionButton />

    </div>

</template>

<script setup>
import AppHeader from '../../components/AppHeader.vue'
import BaseCard from '../../components/BaseCard.vue'
import BaseButton from '../../components/BaseButton.vue'
import BottomNav from '../../components/BottomNav.vue'
import FloatingActionButton from '../../components/FloatingActionButton.vue'
</script>