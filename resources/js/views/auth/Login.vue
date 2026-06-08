<template>
    <div class="relative min-h-screen flex items-center justify-center px-5">

        <div class="fixed inset-0">
            <div class="absolute inset-0 bg-black/20 z-10"></div>

            <img
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuCLHZzUCL65MTgsR8GnutZns0aYdALA_kC4LqZEa0XVDkoRW4ws6UkiB4WMtT2BxwI1K_5iOaGcT8xpyWdnHEK9N2M6bDf7uNdUAE33hqtnNPqvTpBk-temHuiMUiIQtEZNb_lhKKZai4nEFOktu80B8f1TeCxL0sEwLFd6b-Ql7jLUxgOoi8OL7vC2lHV-qvMR8Wopcoq5W50R7z9NYrreOdbNCP9u32a7DmKccP_woZ3afcqagUm6NyqYmhlZKyvuEN-P08DM6MgK"
                class="w-full h-full object-cover"
            />
        </div>

        <div
            class="relative z-20 w-full max-w-md bg-white rounded-3xl shadow-xl p-8"
        >

            <div class="text-center mb-8">

                <div
                    class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-4"
                >
                    <span class="material-symbols-outlined text-4xl text-green-700">
                        agriculture
                    </span>
                </div>

                <h1 class="text-4xl font-bold text-green-700">
                    Ganadería
                </h1>

                <p class="text-gray-500 mt-2">
                    Iniciar sesión
                </p>

            </div>

            <form class="space-y-4">

                <BaseInput
                    v-model="email"
                    label="Correo electrónico"
                    icon="mail"
                    placeholder="ejemplo@correo.com"
                    type="email"
                />

                <BaseInput
                    v-model="password"
                    label="Contraseña"
                    icon="lock"
                    placeholder="••••••••"
                    type="password"
                />

                <div class="text-right">

                    <button
                        type="button"
                        class="text-green-700 text-sm"
                    >
                        ¿Olvidaste tu contraseña?
                    </button>

                </div>

                <BaseButton
                    icon="arrow_forward"
                    @click="login"
                >
                    Iniciar Sesión
                </BaseButton>

            </form>

            <div class="text-center mt-6 pt-4 border-t">

                <span class="text-gray-500">
                    ¿No tienes una cuenta?
                </span>

                <RouterLink
                    to="/registro"
                    class="text-green-700 font-semibold ml-2"
                >
                    Regístrate
                </RouterLink>

            </div>

        </div>

    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

import BaseInput from '../../components/BaseInput.vue'
import BaseButton from '../../components/BaseButton.vue'

const router = useRouter()

const email = ref('')
const password = ref('')

const login = () => {
    router.push('/dashboard')
}
</script>