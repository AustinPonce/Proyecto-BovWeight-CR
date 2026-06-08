<template>
  <div class="min-h-screen bg-background">

    <AppHeader
      title="Registrar Vaca"
      :showBack="true"
    />

    <main class="px-5 pt-6 pb-40 flex flex-col gap-4">

      <BaseCard>

        <h2 class="text-green-700 font-semibold mb-4">
          Información General
        </h2>

        <div class="space-y-4">

          <BaseInput
            v-model="ubicacion"
            label="Ubicación"
            placeholder="Ej. Lote Norte"
          />

          <BaseInput
            v-model="finca"
            label="Finca"
            placeholder="Nombre de la propiedad"
          />

          <BaseInput
            v-model="fecha"
            label="Fecha"
            type="date"
          />

        </div>

      </BaseCard>

      <BaseCard>

        <div class="flex justify-between items-center mb-4">
          <h2 class="text-green-700 font-semibold">
            Imagen
          </h2>
        </div>

        <div
          class="aspect-video rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-100"
        >
          <span class="material-symbols-outlined text-5xl text-gray-400">
            add_a_photo
          </span>
        </div>

        <BaseButton
          class="mt-4"
          icon="photo_camera"
        >
          Tomar fotografía para calcular medidas
        </BaseButton>

        <div class="mt-4 space-y-3">

          <label class="flex items-center gap-3">
            <input
              v-model="fotoCostado"
              type="checkbox"
            />
            Tomar foto de costado
          </label>

          <label class="flex items-center gap-3">
            <input
              v-model="galeria"
              type="checkbox"
            />
            Subir desde galería
          </label>

        </div>

      </BaseCard>

    </main>

    <div
      class="fixed bottom-0 left-0 right-0 bg-white border-t p-4 space-y-2"
    >

      <BaseButton icon="save">
        Guardar Registro
      </BaseButton>

      <button
        class="w-full bg-gray-800 text-white py-4 rounded-xl"
      >
        Cancelar
      </button>

    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'

import AppHeader from '../../../components/AppHeader.vue'
import BaseCard from '../../../components/BaseCard.vue'
import BaseInput from '../../../components/BaseInput.vue'
import BaseButton from '../../../components/BaseButton.vue'

const ubicacion = ref('')
const finca = ref('')
const fecha = ref('')

const fotoCostado = ref(false)
const galeria = ref(false)
</script>