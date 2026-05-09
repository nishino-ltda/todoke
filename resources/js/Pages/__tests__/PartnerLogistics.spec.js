import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import RegionIndex from '../Partner/Regions/Index.vue'
import NodeIndex from '../Partner/Nodes/Index.vue'
import partnerService from '../../services/partner'
import { createPinia, setActivePinia } from 'pinia'

// Mock partnerService
vi.mock('../../services/partner', () => ({
  default: {
    getRegions: vi.fn(),
    createRegion: vi.fn(),
    updateRegion: vi.fn(),
    deleteRegion: vi.fn(),
    getNodes: vi.fn(),
    createNode: vi.fn(),
    updateNode: vi.fn(),
    deleteNode: vi.fn()
  }
}))

// Mock Inertia
vi.mock('@inertiajs/vue3', () => ({
  usePage: () => ({ url: '/partner/regions' }),
  router: { visit: vi.fn() }
}))

const stubs = {
  PartnerLayout: { template: '<div class="partner-layout"><slot /></div>' },
  DataTable: {
    template: '<div class="data-table"><div v-for="item in items" :key="item.id" class="item">{{ item.name }} <slot name="item.actions" :item="item" /></div></div>',
    props: ['items', 'headers', 'loading']
  },
  AppModal: {
    template: '<div class="app-modal" v-if="modelValue"><div class="title">{{ title }}</div><slot /><div class="actions"><slot name="actions" /></div></div>',
    props: ['modelValue', 'title']
  },
  VBtn: { template: '<button class="v-btn" @click="$emit(\'click\')"><slot /></button>' },
  VTextField: { template: '<input class="v-text-field" :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />', props: ['modelValue', 'label'] },
  VSelect: { template: '<select class="v-select" :value="modelValue" @change="$emit(\'update:modelValue\', $event.target.value)"><option v-for="item in items" :key="item.id || item">{{ item.name || item }}</option></select>', props: ['modelValue', 'items', 'label'] },
  VSwitch: { template: '<input type="checkbox" class="v-switch" v-model="internalValue" @change="$emit(\'change\')" />', props: ['modelValue'], data() { return { internalValue: this.modelValue } } },
  VForm: { 
    template: '<form @submit.prevent><slot /></form>',
    methods: { validate: () => Promise.resolve({ valid: true }) }
  },
  VRow: { template: '<div class="v-row"><slot /></div>' },
  VCol: { template: '<div class="v-col"><slot /></div>' },
  VChip: { template: '<span class="v-chip"><slot /></span>' }
}

const messages = {
  'pt-BR': {
    partner: {
      regions: {
        title: 'Gerenciamento de Regiões',
        new: 'Nova Região'
      },
      nodes: {
        title: 'Gerenciamento de Nós',
        new: 'Novo Nó'
      },
      actions: {
          create: 'Criar',
          cancel: 'Cancelar',
          delete: 'Excluir',
          confirm_delete: 'Confirmar Exclusão'
      },
      orders: {
          actions: 'Ações'
      }
    },
    auth: {
        validation: {
            required: 'Obrigatório'
        }
    }
  }
}

describe('Partner Regions & Nodes', () => {
  let i18n

  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    i18n = createI18n({
      legacy: false,
      locale: 'pt-BR',
      messages
    })
  })

  describe('Regions Index', () => {
    beforeEach(() => {
      partnerService.getRegions.mockResolvedValue({
        data: [{ id: 1, name: 'Downtown', active: true, nodes_count: 5 }]
      })
    })

    it('fetches regions and renders pt-BR labels', async () => {
      const wrapper = mount(RegionIndex, { 
        global: { 
          plugins: [i18n],
          stubs 
        } 
      })
      await new Promise(resolve => setTimeout(resolve, 0))
      expect(partnerService.getRegions).toHaveBeenCalled()
      expect(wrapper.text()).toContain('Gerenciamento de Regiões')
    })

    it('can open create modal with translated title', async () => {
      const wrapper = mount(RegionIndex, { 
        global: { 
          plugins: [i18n],
          stubs 
        } 
      })
      await wrapper.find('[data-cy="create-region-btn"]').trigger('click')
      expect(wrapper.find('.app-modal .title').text()).toBe('Nova Região')
    })
  })

  describe('Nodes Index', () => {
    beforeEach(() => {
      partnerService.getNodes.mockResolvedValue({
        data: [{ id: 1, name: 'Central Hub', type: 'hub', region_name: 'Downtown' }]
      })
      partnerService.getRegions.mockResolvedValue({
        data: [{ id: 1, name: 'Downtown' }]
      })
    })

    it('fetches nodes and renders pt-BR labels', async () => {
      const wrapper = mount(NodeIndex, { 
        global: { 
          plugins: [i18n],
          stubs 
        } 
      })
      await new Promise(resolve => setTimeout(resolve, 0))
      expect(partnerService.getNodes).toHaveBeenCalled()
      expect(wrapper.text()).toContain('Gerenciamento de Nós')
    })
  })
})
