<template>

    <div class="min-h-screen">

        <AppHeader
            title="Reportes"
            :showBack="true"
            iconRight="person"
        />

        <main
            class="max-w-lg mx-auto px-5 py-6 pb-28"
        >

            <BaseCard class="mb-6">

                <h2
                    class="text-green-700 font-semibold text-lg"
                >
                    Resumen Mensual
                </h2>

                <p class="text-gray-500 mt-2">
                    Monitorea el desempeño de tu ganadería de manera eficiente.
                </p>

            </BaseCard>

            <div class="space-y-4">

                <BaseCard
                    class="cursor-pointer hover:bg-gray-50"
                >
                    <div class="flex items-center gap-4">

                        <div
                            class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center"
                        >
                            <span class="material-symbols-outlined text-green-700">
                                bar_chart
                            </span>
                        </div>

                        <div class="flex-1">
                            <h3 class="font-semibold">
                                Reporte de peso
                            </h3>

                            <p class="text-sm text-gray-500">
                                Análisis detallado de ganancia de masa corporal.
                            </p>
                        </div>

                    </div>
                </BaseCard>

                <BaseCard>
                    <div class="flex items-center gap-4">

                        <div
                            class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center"
                        >
                            <span class="material-symbols-outlined text-purple-600">
                                show_chart
                            </span>
                        </div>

                        <div class="flex-1">
                            <h3 class="font-semibold">
                                Producción
                            </h3>

                            <p class="text-sm text-gray-500">
                                Métricas de producción láctea y derivados.
                            </p>
                        </div>

                    </div>
                </BaseCard>

                <BaseCard>
                    <div class="flex items-center gap-4">

                        <div
                            class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center"
                        >
                            <span class="material-symbols-outlined text-purple-600">
                                calendar_month
                            </span>
                        </div>

                        <div class="flex-1">
                            <h3 class="font-semibold">
                                Historial
                            </h3>

                            <p class="text-sm text-gray-500">
                                Registro cronológico de eventos y manejos.
                            </p>
                        </div>

                    </div>
                </BaseCard>

            </div>

            <div
                class="grid grid-cols-2 gap-4 mt-6"
            >

                <BaseCard>

                    <span
                        class="material-symbols-outlined text-green-700"
                    >
                        trending_up
                    </span>

                    <p
                        class="text-xs text-green-700 uppercase mt-2"
                    >
                        Eficiencia
                    </p>

                    <h3
                        class="text-2xl font-bold text-green-700"
                    >
                        +12.4%
                    </h3>

                </BaseCard>

                <BaseCard>

                    <span
                        class="material-symbols-outlined text-purple-600"
                    >
                        health_and_safety
                    </span>

                    <p
                        class="text-xs text-purple-600 uppercase mt-2"
                    >
                        Salud
                    </p>

                    <h3
                        class="text-2xl font-bold text-purple-600"
                    >
                        98%
                    </h3>

                </BaseCard>

            </div>

        </main>

        <BottomNav />

    </div>

</template>

<script setup>
import AppHeader from '../../components/AppHeader.vue'
import BaseCard from '../../components/BaseCard.vue'
import BottomNav from '../../components/BottomNav.vue'
</script>