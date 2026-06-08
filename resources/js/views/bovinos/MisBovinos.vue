<template>

  <div class="min-h-screen bg-cover bg-center pb-24">

    <AppHeader
      title="Ganadería"
      iconRight="person"
    />

    <main
      class="max-w-lg mx-auto px-5 py-6"
    >

      <BaseInput
        v-model="buscar"
        icon="search"
        placeholder="Buscar ID, Rebaño o Arete..."
      />

      <BaseCard class="mt-10 text-center">

        <div
          class="w-24 h-24 mx-auto rounded-full bg-gray-100 flex items-center justify-center"
        >
          <span
            class="material-symbols-outlined text-6xl text-gray-400"
          >
            potted_plant
          </span>
        </div>

        <h2
          class="text-xl font-semibold mt-4"
        >
          Sin registros aún
        </h2>

        <p class="text-gray-500 mt-2">
          No hay ganado registrado actualmente en su inventario.
        </p>

        <BaseButton
          class="mt-6"
          icon="add_circle"
        >
          Agregar Bovino
        </BaseButton>

      </BaseCard>

      <div
        class="grid grid-cols-2 gap-4 mt-4"
      >

        <BaseCard class="text-center">

          <h2 class="text-4xl font-bold text-green-700">
            0
          </h2>

          <p class="text-gray-500 text-sm">
            ACTIVOS
          </p>

        </BaseCard>

        <BaseCard class="text-center">

          <h2 class="text-4xl font-bold text-purple-600">
            0
          </h2>

          <p class="text-gray-500 text-sm">
            CUARENTENA
          </p>

        </BaseCard>

      </div>

    </main>

    <BottomNav />

  </div>

</template>

<script setup>
import { ref } from 'vue'

import AppHeader from '../../components/AppHeader.vue'
import BaseInput from '../../components/BaseInput.vue'
import BaseCard from '../../components/BaseCard.vue'
import BaseButton from '../../components/BaseButton.vue'
import BottomNav from '../../components/BottomNav.vue'

const buscar = ref('')
</script>