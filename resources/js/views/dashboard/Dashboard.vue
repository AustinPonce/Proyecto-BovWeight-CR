```vue
<template>

    <div
        class="min-h-screen bg-cover bg-center pb-24"
        style="background-image:url('https://lh3.googleusercontent.com/aida-public/AB6AXuCLHZzUCL65MTgsR8GnutZns0aYdALA_kC4LqZEa0XVDkoRW4ws6UkiB4WMtT2BxwI1K_5iOaGcT8xpyWdnHEK9N2M6bDf7uNdUAE33hqtnNPqvTpBk-temHuiMUiIQtEZNb_lhKKZai4nEFOktu80B8f1TeCxL0sEwLFd6b-Ql7jLUxgOoi8OL7vC2lHV-qvMR8Wopcoq5W50R7z9NYrreOdbNCP9u32a7DmKccP_woZ3afcqagUm6NyqYmhlZKyvuEN-P08DM6MgK')"
    >

        <AppHeader
            title="Ganadería"
            iconRight="search"
            :showBack="false"
        />

        <div class="p-5">

            <BaseCard>

                <div class="flex justify-between items-center">

                    <div class="flex gap-4 items-center">

                        <div
                            class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center"
                        >
                            <span class="material-symbols-outlined">
                                person
                            </span>
                        </div>

                        <div>

                            <p class="text-gray-500">
                                Bienvenido
                            </p>

                            <h2 class="font-bold text-lg">
                                Austin Ponce
                            </h2>

                        </div>

                    </div>

                    <button
                        @click="router.push('/perfil')"
                    >
                        <span class="material-symbols-outlined">
                            settings
                        </span>
                    </button>

                </div>

            </BaseCard>

            <div class="mt-6">

                <BaseCard>

                    <div class="flex justify-between items-center">

                        <span class="text-gray-500">
                            Estado del Ganado
                        </span>

                        <span
                            class="bg-green-200 text-green-800 px-3 py-1 rounded-full text-xs"
                        >
                            Sano
                        </span>

                    </div>

                    <div class="mt-4">

                        <h2
                            class="text-5xl font-bold text-green-700"
                        >
                            124
                        </h2>

                        <p class="text-gray-500">
                            Cabezas totales
                        </p>

                    </div>

                </BaseCard>

            </div>

            <div
                class="grid grid-cols-2 gap-4 mt-6"
            >

                <button
                    @click="router.push('/bovinos/registrar/paso1')"
                    class="bg-green-400 rounded-2xl p-6 text-center text-white hover:bg-green-500 transition"
                >
                    📷

                    <p class="mt-3">
                        Registrar Bovino por Foto
                    </p>

                </button>

                <button
                    @click="router.push('/bovinos/registrar/paso2')"
                    class="bg-green-400 rounded-2xl p-6 text-center text-white hover:bg-green-500 transition"
                >
                    📄

                    <p class="mt-3">
                        Registrar Bovino Manual
                    </p>

                </button>

                <button
                    @click="router.push('/bovinos')"
                    class="bg-green-400 rounded-2xl p-6 text-center text-white hover:bg-green-500 transition"
                >
                    🐄

                    <p class="mt-3">
                        Mis Bovinos
                    </p>

                </button>

                <button
                    @click="router.push('/fincas')"
                    class="bg-green-400 rounded-2xl p-6 text-center text-white hover:bg-green-500 transition"
                >
                    📍

                    <p class="mt-3">
                        Mis Fincas
                    </p>

                </button>

            </div>

        </div>

        <BottomNav />

    </div>

</template>

<script setup>
import { useRouter } from 'vue-router'

import AppHeader from '../../components/AppHeader.vue'
import BaseCard from '../../components/BaseCard.vue'
import BottomNav from '../../components/BottomNav.vue'

const router = useRouter()
</script>
```
