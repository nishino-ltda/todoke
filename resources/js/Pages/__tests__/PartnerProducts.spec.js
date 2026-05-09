import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import Index from '../Partner/Products/Index.vue'
import partnerService from '../../services/partner'
import { createPinia, setActivePinia } from 'pinia'

// Mock partnerService
vi.mock('../../services/partner', () => ({
  default: {
    getMenu: vi.fn(),
    createProduct: vi.fn(),
    updateProduct: vi.fn(),
    deleteProduct: vi.fn(),
    updateItemAvailability: vi.fn(),
    getAddons: vi.fn().mockResolvedValue({ data: [] })
  }
}))

// Mock Inertia
vi.mock('@inertiajs/vue3', () => ({
  usePage: () => ({ url: '/partner/products' }),
  router: { visit: vi.fn() }
}))

const stubs = {
  PartnerLayout: { template: '<div class="partner-layout"><slot /></div>' },
  DataTable: {
    template: '<div class="data-table"><div v-for="item in items" :key="item.id" class="item">{{ item.name }} <slot name="item.actions" :item="item" /><slot name="item.available" :item="item" /></div></div>',
    props: ['items', 'headers', 'loading']
  },
  AppModal: {
    template: '<div class="app-modal" v-if="modelValue"><div class="title">{{ title }}</div><slot /><div class="actions"><slot name="actions" /></div></div>',
    props: ['modelValue', 'title']
  },
  VBtn: { template: '<button class="v-btn" @click="$emit(\'click\')"><slot /></button>' },
  VTextField: { template: '<input class="v-text-field" :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />', props: ['modelValue', 'label'] },
  VTextarea: { template: '<textarea class="v-textarea" :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)"></textarea>', props: ['modelValue', 'label'] },
  VSelect: { template: '<select class="v-select" :value="modelValue" @change="$emit(\'update:modelValue\', $event.target.value)"><option v-for="item in items" :key="item">{{ item }}</option></select>', props: ['modelValue', 'items', 'label'] },
  VSwitch: { template: '<input type="checkbox" class="v-switch" v-model="internalValue" @change="$emit(\'change\')" />', props: ['modelValue'], data() { return { internalValue: this.modelValue } } },
  VAvatar: { template: '<div class="v-avatar"><slot /></div>' },
  VImg: { template: '<img class="v-img" :src="src" />', props: ['src'] },
  VForm: { 
    template: '<form @submit.prevent><slot /></form>',
    methods: { validate: () => Promise.resolve({ valid: true }) }
  },
  VRow: { template: '<div class="v-row"><slot /></div>' },
  VCol: { template: '<div class="v-col"><slot /></div>' }
}

const ptBrMessages = {
  partner: {
    products: {
      title: 'Gerenciamento de Produtos',
      add: 'Adicionar Produto',
      new: 'Novo Produto',
      success: {
        created: 'Produto criado com sucesso'
      }
    },
    actions: {
      create: 'Criar',
      cancel: 'Cancelar',
      delete: 'Excluir',
      confirm_delete: 'Confirmar Exclusão'
    },
    orders: {
      actions: 'Ações'
    },
    status: {
        available: 'disponível'
    }
  },
  auth: {
      validation: {
          required: 'Obrigatório'
      }
  }
}

describe('Partner Products Index', () => {
  let i18n

  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    
    i18n = createI18n({
      legacy: false,
      locale: 'pt-BR',
      messages: { 'pt-BR': ptBrMessages }
    })

    partnerService.getMenu.mockResolvedValue({
      data: [
        { id: 1, name: 'Pizza', price: 10, category: 'Pizza', available: true },
        { id: 2, name: 'Burger', price: 8, category: 'Burger', available: false }
      ]
    })
  })

  it('fetches products on mount and renders pt-BR labels', async () => {
    const wrapper = mount(Index, { 
      global: { 
        plugins: [i18n],
        stubs 
      } 
    })
    await new Promise(resolve => setTimeout(resolve, 0))
    expect(partnerService.getMenu).toHaveBeenCalled()
    expect(wrapper.text()).toContain('Gerenciamento de Produtos')
    expect(wrapper.text()).toContain('Adicionar Produto')
  })

  it('opens create modal when clicking add button with translated title', async () => {
    const wrapper = mount(Index, { 
      global: { 
        plugins: [i18n],
        stubs 
      } 
    })
    await wrapper.find('[data-cy="create-product-btn"]').trigger('click')
    expect(wrapper.find('.app-modal').exists()).toBe(true)
    expect(wrapper.find('.app-modal .title').text()).toBe('Novo Produto')
  })

  it('calls createProduct on form submission', async () => {
    partnerService.createProduct.mockResolvedValue({ success: true })
    const wrapper = mount(Index, { 
      global: { 
        plugins: [i18n],
        stubs 
      } 
    })
    
    await wrapper.find('[data-cy="create-product-btn"]').trigger('click')
    await wrapper.find('[data-cy="save-product-btn"]').trigger('click')
    
    expect(partnerService.createProduct).toHaveBeenCalled()
  })
})
