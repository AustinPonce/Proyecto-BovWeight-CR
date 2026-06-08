<template>

    <div class="min-h-screen flex items-center justify-center px-5">

        <div
            class="w-full max-w-md bg-white/95 backdrop-blur rounded-3xl shadow-xl p-8"
        >

            <div class="text-center mb-8">

                <h1 class="text-3xl font-bold">
                    Crear Cuenta
                </h1>

                <p class="text-gray-500">
                    Únete a la gestión ganadera inteligente
                </p>

            </div>

            <form class="space-y-4">

                <BaseInput
                    v-model="nombre"
                    label="Nombre completo"
                    icon="person"
                />

                <BaseInput
                    v-model="correo"
                    label="Correo electrónico"
                    icon="mail"
                />

                <BaseInput
                    v-model="password"
                    label="Contraseña"
                    icon="lock"
                    type="password"
                />

                <BaseInput
                    v-model="confirmar"
                    label="Confirmar contraseña"
                    icon="lock_reset"
                    type="password"
                />

                <BaseButton icon="arrow_forward">
                    Registrarse
                </BaseButton>

            </form>

            <div class="text-center mt-6">

                <span class="text-gray-500">
                    ¿Ya tienes una cuenta?
                </span>

                <RouterLink
                    to="/login"
                    class="text-green-700 font-semibold ml-1"
                >
                    Iniciar sesión
                </RouterLink>

            </div>

        </div>

    </div>

</template>

<script setup>
import { ref } from 'vue'

import BaseInput from '../../components/BaseInput.vue'
import BaseButton from '../../components/BaseButton.vue'

const nombre = ref('')
const correo = ref('')
const password = ref('')
const confirmar = ref('')
</script>