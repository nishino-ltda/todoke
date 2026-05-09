import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
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
    template: '<div class="app-modal" v-if="modelValue"><slot /><div class="actions"><slot name="actions" /></div></div>',
    props: ['modelValue', 'title']
  },
  VBtn: { template: '<button class="v-btn" @click="$emit(\'click\')"><slot /></button>' },
  VTextField: { template: '<input class="v-text-field" :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />', props: ['modelValue'] },
  VSelect: { template: '<select class="v-select" :value="modelValue" @change="$emit(\'update:modelValue\', $event.target.value)"><option v-for="item in items" :key="item.id || item">{{ item.name || item }}</option></select>', props: ['modelValue', 'items'] },
  VSwitch: { template: '<input type="checkbox" class="v-switch" v-model="internalValue" @change="$emit(\'change\')" />', props: ['modelValue'], data() { return { internalValue: this.modelValue } } },
  VForm: { 
    template: '<form @submit.prevent><slot /></form>',
    methods: { validate: () => Promise.resolve({ valid: true }) }
  },
  VRow: { template: '<div class="v-row"><slot /></div>' },
  VCol: { template: '<div class="v-col"><slot /></div>' },
  VChip: { template: '<span class="v-chip"><slot /></span>' }
}

describe('Partner Regions & Nodes', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  describe('Regions Index', () => {
    beforeEach(() => {
      partnerService.getRegions.mockResolvedValue({
        data: [{ id: 1, name: 'Downtown', active: true, nodes_count: 5 }]
      })
    })

    it('fetches regions on mount', async () => {
      const wrapper = mount(RegionIndex, { global: { stubs } })
      await new Promise(resolve => setTimeout(resolve, 0))
      expect(partnerService.getRegions).toHaveBeenCalled()
      expect(wrapper.findAll('.item')).toHaveLength(1)
    })

    it('can create a region', async () => {
      partnerService.createRegion.mockResolvedValue({ success: true })
      const wrapper = mount(RegionIndex, { global: { stubs } })
      await wrapper.find('[data-cy="create-region-btn"]').trigger('click')
      await wrapper.find('[data-cy="save-region-btn"]').trigger('click')
      expect(partnerService.createRegion).toHaveBeenCalled()
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

    it('fetches nodes and regions on mount', async () => {
      const wrapper = mount(NodeIndex, { global: { stubs } })
      await new Promise(resolve => setTimeout(resolve, 0))
      expect(partnerService.getNodes).toHaveBeenCalled()
      expect(partnerService.getRegions).toHaveBeenCalled()
      expect(wrapper.findAll('.item')).toHaveLength(1)
    })

    it('can create a node', async () => {
      partnerService.createNode.mockResolvedValue({ success: true })
      const wrapper = mount(NodeIndex, { global: { stubs } })
      await wrapper.find('[data-cy="create-node-btn"]').trigger('click')
      await wrapper.find('[data-cy="save-node-btn"]').trigger('click')
      expect(partnerService.createNode).toHaveBeenCalled()
    })
  })
})
