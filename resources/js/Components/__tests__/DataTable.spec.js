import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import DataTable from '../DataTable.vue'

// Mock Vuetify components
const vuetifyStubs = {
  VCard: {
    template: '<div class="v-card"><slot /></div>'
  },
  VCardTitle: {
    template: '<div class="v-card-title"><slot /></div>'
  },
  VTextField: {
    template: '<input class="v-text-field" v-model="modelValue" />',
    props: ['modelValue']
  },
  VDataTable: {
    template: '<div class="v-data-table"><slot name="header" /><div v-for="item in items" class="item">{{ item.name }}</div><slot name="no-data" v-if="!items.length" /></div>',
    props: ['items', 'headers', 'search', 'loading']
  },
  VSpacer: {
    template: '<div class="v-spacer"></div>'
  },
  VSkeletonLoader: {
    template: '<div class="v-skeleton-loader"></div>'
  },
  VIcon: {
    template: '<i class="v-icon"></i>'
  }
}

describe('DataTable', () => {
  const headers = [{ title: 'Name', key: 'name' }]
  const items = [{ id: 1, name: 'Item 1' }, { id: 2, name: 'Item 2' }]

  it('renders correctly with data', () => {
    const wrapper = mount(DataTable, {
      props: {
        title: 'Test Table',
        headers,
        items
      },
      global: {
        stubs: vuetifyStubs
      }
    })
    
    expect(wrapper.find('[data-cy="table-title"]').text()).toBe('Test Table')
    expect(wrapper.findAll('.item')).toHaveLength(2)
  })

  it('shows search field when showSearch is true', () => {
    const wrapper = mount(DataTable, {
      props: {
        headers,
        items,
        showSearch: true
      },
      global: {
        stubs: vuetifyStubs
      }
    })
    
    expect(wrapper.find('[data-cy="table-search"]').exists()).toBe(true)
  })

  it('shows no data message when items are empty', () => {
    const wrapper = mount(DataTable, {
      props: {
        headers,
        items: []
      },
      global: {
        stubs: vuetifyStubs
      }
    })
    
    expect(wrapper.text()).toContain('No data available')
  })
})
