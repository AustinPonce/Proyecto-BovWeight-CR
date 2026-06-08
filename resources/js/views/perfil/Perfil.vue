<template>

    <div class="min-h-screen relative overflow-x-hidden">

        <div class="fixed inset-0 -z-10">
            <div
                class="absolute inset-0 bg-cover bg-center"
                style="background-image:url('https://lh3.googleusercontent.com/aida-public/AB6AXuCLHZzUCL65MTgsR8GnutZns0aYdALA_kC4LqZEa0XVDkoRW4ws6UkiB4WMtT2BxwI1K_5iOaGcT8xpyWdnHEK9N2M6bDf7uNdUAE33hqtnNPqvTpBk-temHuiMUiIQtEZNb_lhKKZai4nEFOktu80B8f1TeCxL0sEwLFd6b-Ql7jLUxgOoi8OL7vC2lHV-qvMR8Wopcoq5W50R7z9NYrreOdbNCP9u32a7DmKccP_woZ3afcqagUm6NyqYmhlZKyvuEN-P08DM6MgK')"
            />

            <div class="absolute inset-0 bg-white/20 backdrop-blur-[2px]" />
        </div>

        <AppHeader
            title="Perfil"
            iconRight="settings"
            :showBack="true"
        />

        <main class="px-5 pt-8 pb-28 flex flex-col items-center">

            <BaseCard class="w-full max-w-md">

                <div class="flex flex-col items-center">

                    <div class="relative mb-4">

                        <div
                            class="w-32 h-32 rounded-full overflow-hidden border-4 border-gray-100"
                        >
                            <img
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuDcnt151cmOdXpvBViWCz2dGiUxWFjigiZeLKaoJ2Pl3zPATMkIyp9wh1tXDj8cWPWLHmlfuwqGsHIZQfDcXSBkXR1VotH68ezflZ9EpYqCqhcxakRl8P7RVwKapewsgOGnh1AQCCzZk9AiCIgsxsKk9pkmYZYFqVV6FqsGLUtzANVtdlg35IvtYcvW3M1CpYmSCBJylHtVKewEkuYuHkD_y6HJDYGe61l8sBi6vqWiB7EOG4TtA7S7JSTpf9r0OS024Z75O0enR3N8"
                                class="w-full h-full object-cover"
                            >
                        </div>

                        <div
                            class="absolute bottom-1 right-1 bg-green-600 text-white p-2 rounded-full"
                        >
                            <span class="material-symbols-outlined text-sm">
                                verified
                            </span>
                        </div>

                    </div>

                    <h2 class="text-2xl font-bold">
                        Austin Ponce
                    </h2>

                    <p class="text-gray-500">
                        ponceaustin@gmail.com
                    </p>

                    <button
                        class="mt-4 bg-green-100 text-green-800 px-6 py-3 rounded-full flex items-center gap-2"
                    >
                        <span class="material-symbols-outlined">
                            edit
                        </span>

                        Editar Perfil
                    </button>

                </div>

            </BaseCard>

            <div
                class="w-full max-w-md grid grid-cols-2 gap-4 mt-5"
            >

                <BaseCard>

                    <span class="material-symbols-outlined text-purple-600">
                        database
                    </span>

                    <p class="text-xs text-gray-500 mt-2">
                        REGISTROS
                    </p>

                    <h3 class="text-2xl font-bold">
                        1248
                    </h3>

                </BaseCard>

                <BaseCard>

                    <span class="material-symbols-outlined text-green-600">
                        landscape
                    </span>

                    <p class="text-xs text-gray-500 mt-2">
                        HECTÁREAS
                    </p>

                    <h3 class="text-2xl font-bold">
                        45.5
                    </h3>

                </BaseCard>

            </div>

            <div
                class="w-full max-w-md mt-5 space-y-3"
            >

                <BaseCard class="cursor-pointer">
                    <div class="flex justify-between items-center">

                        <div class="flex items-center gap-3">

                            <span class="material-symbols-outlined text-green-700">
                                history
                            </span>

                            <span>
                                Historial de Actividad
                            </span>

                        </div>

                        <span class="material-symbols-outlined">
                            chevron_right
                        </span>

                    </div>
                </BaseCard>

                <BaseCard class="cursor-pointer">
                    <div class="flex justify-between items-center">

                        <div class="flex items-center gap-3">

                            <span class="material-symbols-outlined text-green-700">
                                notifications_active
                            </span>

                            <span>
                                Notificaciones
                            </span>

                        </div>

                        <span class="material-symbols-outlined">
                            chevron_right
                        </span>

                    </div>
                </BaseCard>

                <BaseCard class="cursor-pointer">
                    <div class="flex justify-between items-center text-red-600">

                        <div class="flex items-center gap-3">

                            <span class="material-symbols-outlined">
                                logout
                            </span>

                            <span>
                                Cerrar Sesión
                            </span>

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