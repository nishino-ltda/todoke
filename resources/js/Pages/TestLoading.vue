<template>
  <div>
    <h1>LoadingIndicator Test Page</h1>
    
    <v-btn 
      data-cy="show-full-loader"
      @click="showFullLoader = true"
      class="mr-4"
    >
      Show Full Loader
    </v-btn>
    
    <v-btn 
      data-cy="hide-full-loader"
      @click="showFullLoader = false"
    >
      Hide Full Loader
    </v-btn>

    <LoadingIndicator
      :isLoading="showFullLoader"
      fullPage
      message="Loading page content..."
    />

    <v-divider class="my-8"></v-divider>

    <v-btn 
      data-cy="show-inline-loader"
      @click="showInlineLoader = true"
      class="mr-4"
    >
      Show Inline Loader
    </v-btn>
    
    <v-btn 
      data-cy="hide-inline-loader"
      @click="showInlineLoader = false"
    >
      Hide Inline Loader
    </v-btn>

    <div class="mt-4">
      <LoadingIndicator
        :isLoading="showInlineLoader"
        message="Loading data..."
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import LoadingIndicator from '@/Components/LoadingIndicator.vue'

const showFullLoader = ref(false)
const showInlineLoader = ref(false)
</script>
