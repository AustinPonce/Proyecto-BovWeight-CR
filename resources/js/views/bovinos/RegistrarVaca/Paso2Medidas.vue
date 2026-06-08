<template>

  <div class="min-h-screen">

    <AppHeader
      title="Registrar Vaca"
      :showBack="true"
    />

    <main class="px-5 py-6 pb-32 space-y-4">

      <BaseCard>

        <h2 class="text-green-700 font-semibold mb-4">
          General
        </h2>

        <div class="space-y-4">

          <BaseInput
            v-model="arete"
            label="Número de Arete"
          />

          <BaseInput
            v-model="raza"
            label="Raza"
          />

          <BaseInput
            v-model="edad"
            label="Edad (meses)"
            type="number"
          />

          <BaseInput
            v-model="sexo"
            label="Sexo"
          />

        </div>

      </BaseCard>

      <BaseCard>

        <h2 class="text-green-700 font-semibold mb-4">
          Medidas
        </h2>

        <div class="space-y-4">

          <BaseInput
            v-model="largo"
            label="Largo del cuerpo"
          />

          <BaseInput
            v-model="altura"
            label="Altura"
          />

          <BaseInput
            v-model="perimetro"
            label="Perímetro torácico"
          />

        </div>

      </BaseCard>

      <BaseCard>

        <h2 class="text-green-700 font-semibold mb-4">
          Ubicación
        </h2>

        <div class="space-y-4">

          <BaseInput
            v-model="finca"
            label="Finca"
          />

          <BaseInput
            v-model="fecha"
            type="date"
            label="Fecha"
          />

        </div>

      </BaseCard>

    </main>

    <div
      class="fixed bottom-0 left-0 right-0 bg-white border-t p-4 flex gap-3"
    >

      <button
        class="flex-1 border-2 border-green-700 text-green-700 py-4 rounded-xl"
      >
        Cancelar
      </button>

      <BaseButton
        class="flex-1"
        icon="save"
      >
        Guardar
      </BaseButton>

    </div>

  </div>

</template>

<script setup>
import { ref } from 'vue'

import AppHeader from '../../../components/AppHeader.vue'
import BaseCard from '../../../components/BaseCard.vue'
import BaseInput from '../../../components/BaseInput.vue'
import BaseButton from '../../../components/BaseButton.vue'

const arete = ref('')
const raza = ref('')
const edad = ref('')
const sexo = ref('')

const largo = ref('')
const altura = ref('')
const perimetro = ref('')

const finca = ref('')
const fecha = ref('')
</script>