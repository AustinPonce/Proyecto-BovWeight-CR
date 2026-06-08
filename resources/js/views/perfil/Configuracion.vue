<template>

    <div class="min-h-screen relative">

        <div class="fixed inset-0 -z-10">
            <div
                class="absolute inset-0 bg-cover bg-center"
                style="background-image:url('https://lh3.googleusercontent.com/aida-public/AB6AXuCLHZzUCL65MTgsR8GnutZns0aYdALA_kC4LqZEa0XVDkoRW4ws6UkiB4WMtT2BxwI1K_5iOaGcT8xpyWdnHEK9N2M6bDf7uNdUAE33hqtnNPqvTpBk-temHuiMUiIQtEZNb_lhKKZai4nEFOktu80B8f1TeCxL0sEwLFd6b-Ql7jLUxgOoi8OL7vC2lHV-qvMR8Wopcoq5W50R7z9NYrreOdbNCP9u32a7DmKccP_woZ3afcqagUm6NyqYmhlZKyvuEN-P08DM6MgK')"
            />

            <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-white" />
        </div>

        <AppHeader
            title="Configuración"
            iconRight="settings"
            :showBack="true"
        />

        <main class="pt-24 pb-28 px-5">

            <div class="text-center mb-8">

                <div
                    class="w-24 h-24 rounded-full overflow-hidden mx-auto border-4 border-white"
                >
                    <img
                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuALtkIZ8Y-ZXXxeJAg17aec4rjV2I3Z0c9v48Rr0p30atsuJMNpilvn1lnmK9kQ3J-uCR_TULkYnh4jQ0n6IzFQpvKTHtaHI3Ao3SIyXGapm7WvJqDA_WU7u6Y_bLzj13FlYi5ZgY_gcIE3YnrjDO3ghRK8SeGG0oxPTRudmZk224sUWAAS71utgltBuJObBlYyVef1g0NXr6Nys_ID4LOBaJ4a3T91J6o-NXjKGYB03GsJwy35iGVKakyq1QDE_5bdRIxoFYE6z-5C"
                        class="w-full h-full object-cover"
                    >
                </div>

                <h2 class="text-white text-xl font-bold mt-4">
                    Austin Ponce
                </h2>

                <p class="text-white/80">
                    Administrador de Finca
                </p>

            </div>

            <div class="space-y-4">

                <BaseCard>
                    <div class="flex justify-between items-center">

                        <div class="flex items-center gap-4">

                            <span class="material-symbols-outlined text-green-700">
                                person
                            </span>

                            <div>
                                <p class="font-semibold">
                                    Editar perfil
                                </p>
                                <p class="text-sm text-gray-500">
                                    Información personal y contacto
                                </p>
                            </div>

                        </div>

                        <span class="material-symbols-outlined">
                            chevron_right
                        </span>

                    </div>
                </BaseCard>

                <BaseCard>
                    <div class="flex justify-between items-center">

                        <div class="flex items-center gap-4">

                            <span class="material-symbols-outlined text-green-700">
                                lock
                            </span>

                            <div>
                                <p class="font-semibold">
                                    Cambiar contraseña
                                </p>
                                <p class="text-sm text-gray-500">
                                    Actualiza tu seguridad
                                </p>
                            </div>

                        </div>

                        <span class="material-symbols-outlined">
                            chevron_right
                        </span>

                    </div>
                </BaseCard>

                <BaseCard>
                    <div class="flex justify-between items-center">

                        <div class="flex items-center gap-4">

                            <span class="material-symbols-outlined text-green-700">
                                notifications
                            </span>

                            <div>
                                <p class="font-semibold">
                                    Notificaciones
                                </p>
                                <p class="text-sm text-gray-500">
                                    Alertas de ganado y sistema
                                </p>
                            </div>

                        </div>

                        <span
                            class="px-2 py-1 rounded-lg bg-green-100 text-green-700 text-xs"
                        >
                            Activas
                        </span>

                    </div>
                </BaseCard>

                <BaseCard>
                    <div class="flex justify-between items-center text-red-600">

                        <div class="flex items-center gap-4">

                            <span class="material-symbols-outlined">
                                logout
                            </span>

                            <div>
                                <p class="font-semibold">
                                    Cerrar sesión
                                </p>
                                <p class="text-sm opacity-70">
                                    Finalizar sesión actual
                                </p>
                            </div>

                        </div>

                    </div>
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