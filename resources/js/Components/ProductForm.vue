<template>
  <v-form ref="formRef" @submit.prevent="submit">
    <v-row>
      <v-col cols="12">
        <v-text-field
          v-model="modelValue.name"
          :label="t('partner.products.name')"
          required
          :rules="[v => !!v || t('auth.validation.required', { field: t('partner.products.name') })]"
          data-cy="product-name-input"
        ></v-text-field>
      </v-col>
      
      <v-col cols="12" sm="6">
        <v-text-field
          v-model.number="modelValue.price"
          :label="t('partner.products.price')"
          type="number"
          prefix="$"
          required
          :rules="[
            v => v !== null && v !== undefined || t('auth.validation.required', { field: t('partner.products.price') }),
            v => v > 0 || t('partner.products.validation.price_positive')
          ]"
          data-cy="product-price-input"
        ></v-text-field>
      </v-col>
      
      <v-col cols="12" sm="6">
        <v-select
          v-model="modelValue.category"
          :items="categories"
          :label="t('partner.products.category')"
          data-cy="product-category-select"
        ></v-select>
      </v-col>
      
      <v-col cols="12">
        <v-textarea
          v-model="modelValue.description"
          :label="t('partner.products.description')"
          rows="3"
          data-cy="product-description-input"
        ></v-textarea>
      </v-col>
      
      <v-col cols="12">
        <div class="mb-2">
          <label class="v-label d-block mb-1">{{ t('partner.products.image') }}</label>
          <div class="d-flex align-center gap-4">
            <v-avatar size="80" rounded="lg" class="elevation-1">
              <v-img :src="imagePreview || resolveImageUrl(modelValue.image)" cover>
                <template v-slot:placeholder>
                  <v-icon icon="mdi-food"></v-icon>
                </template>
              </v-img>
            </v-avatar>
            <v-file-input
              v-model="imageFile"
              :label="t('partner.products.select_image')"
              accept="image/*"
              prepend-icon="mdi-camera"
              variant="outlined"
              density="compact"
              hide-details
              @change="handleImageChange"
              class="flex-grow-1"
              data-cy="product-image-file"
            ></v-file-input>
          </div>
        </div>
      </v-col>
      
      <v-col cols="12" v-if="showAvailable">

        <v-switch
          v-model="modelValue.available"
          :label="t('partner.products.available')"
          color="primary"
          hide-details
          data-cy="product-available-switch"
        ></v-switch>
      </v-col>
    </v-row>
  </v-form>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  },
  availableAddons: {
    type: Array,
    default: () => []
  },
  showAvailable: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue', 'update:imageFile', 'submit']);

const { t } = useI18n();
const formRef = ref(null);
const imageFile = ref(null);
const imagePreview = ref(null);

const categories = [
  { title: t('partner.products.categories.pizza'), value: 'Pizza' },
  { title: t('partner.products.categories.burger'), value: 'Burger' },
  { title: t('partner.products.categories.dessert'), value: 'Dessert' },
  { title: t('partner.products.categories.drinks'), value: 'Drinks' },
  { title: t('partner.products.categories.sushi'), value: 'Sushi' }
];

const resolveImageUrl = (path) => {
  if (!path) return null;
  if (path.startsWith('http') || path.startsWith('data:')) return path;
  return `/storage/${path}`;
};

const handleImageChange = (event) => {
  const file = imageFile.value;
  if (file) {
    emit('update:imageFile', file);
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  } else {
    emit('update:imageFile', null);
    imagePreview.value = null;
  }
};

const validate = async () => {
  return await formRef.value.validate();
};

const submit = () => {
  emit('submit');
};

defineExpose({ validate });
</script>

<style scoped>
.gap-4 {
  gap: 1rem;
}
</style>
