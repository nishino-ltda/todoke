import { mount } from '@vue/test-utils'
import { vi, describe, it, expect, beforeEach } from 'vitest'
import AddressInput from '../AddressInput.vue'
import mapService from '@/services/map'

// Mock mapService
vi.mock('@/services/map', () => ({
  default: {
    geocode: vi.fn()
  }
}))

// Mock i18n
vi.mock('vue-i18n', () => ({
  useI18n: () => ({
    t: (key) => key
  })
}))

const VTextareaStub = {
  template: `
    <div>
      <textarea 
        :value="modelValue" 
        @input="$emit('update:modelValue', $event.target.value)"
        class="v-textarea-stub"
      ></textarea>
      <div class="v-textarea-errors" v-if="errorMessages">
        {{ errorMessages.join(', ') }}
      </div>
    </div>
  `,
  props: ['modelValue', 'errorMessages']
}

const VComboboxStub = {
  template: `
    <div>
      <input 
        class="v-combobox-stub" 
        :value="search" 
        @input="$emit('update:search', $event.target.value)"
      />
      <div v-if="loading" class="loading-spinner">Loading...</div>
      <div v-for="item in items" :key="item.address" @click="$emit('update:modelValue', item)" class="item-stub">
        {{ item.address }}
      </div>
      <div class="v-combobox-errors" v-if="errorMessages">
        {{ errorMessages.join(', ') }}
      </div>
    </div>
  `,
  props: ['modelValue', 'search', 'items', 'loading', 'errorMessages']
}

describe('AddressInput', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.useFakeTimers()
  })

  it('renders a textarea by default (geocode=false)', () => {
    const wrapper = mount(AddressInput, {
      props: {
        modelValue: ''
      },
      global: {
        stubs: {
          'v-textarea': VTextareaStub,
          'v-combobox': VComboboxStub
        }
      }
    })
    expect(wrapper.find('.v-textarea-stub').exists()).toBe(true)
    expect(wrapper.find('.v-combobox-stub').exists()).toBe(false)
  })

  it('renders a combobox when geocode is true', () => {
    const wrapper = mount(AddressInput, {
      props: {
        modelValue: '',
        geocode: true
      },
      global: {
        stubs: {
          'v-textarea': VTextareaStub,
          'v-combobox': VComboboxStub
        }
      }
    })
    expect(wrapper.find('.v-combobox-stub').exists()).toBe(true)
    expect(wrapper.find('.v-textarea-stub').exists()).toBe(false)
  })

  it('emits update event when address changes in textarea mode', async () => {
    const wrapper = mount(AddressInput, {
      props: {
        modelValue: ''
      },
      global: {
        stubs: {
          'v-textarea': VTextareaStub
        }
      }
    })
    const testAddress = '123 Main St'
    await wrapper.find('.v-textarea-stub').setValue(testAddress)
    const emissions = wrapper.emitted('update:modelValue')
    expect(emissions).toBeTruthy()
    expect(emissions[emissions.length - 1]).toEqual([testAddress])
  })

  it('calls mapService.geocode when typing in combobox (debounced)', async () => {
    mapService.geocode.mockResolvedValue({ data: [{ address: '123 Main St', lat: 1, lng: 2 }] })
    
    const wrapper = mount(AddressInput, {
      props: {
        modelValue: '',
        geocode: true
      },
      global: {
        stubs: {
          'v-textarea': VTextareaStub,
          'v-combobox': VComboboxStub
        }
      }
    })

    const input = wrapper.find('.v-combobox-stub')
    await input.setValue('123 Main')
    
    // Should not call immediately
    expect(mapService.geocode).not.toHaveBeenCalled()
    
    // Advance timers
    vi.advanceTimersByTime(300)
    
    expect(mapService.geocode).toHaveBeenCalledWith('123 Main')
  })

  it('emits address object when selecting item from combobox', async () => {
    const mockResult = { address: '123 Main St', lat: 1, lng: 2 }
    mapService.geocode.mockResolvedValue({ data: [mockResult] })
    
    const wrapper = mount(AddressInput, {
      props: {
        modelValue: '',
        geocode: true
      },
      global: {
        stubs: {
          'v-textarea': VTextareaStub,
          'v-combobox': VComboboxStub
        }
      }
    })

    await wrapper.find('.v-combobox-stub').setValue('123 Main')
    vi.advanceTimersByTime(300)
    await vi.runAllTimersAsync()
    await wrapper.vm.$nextTick()
    
    expect(wrapper.find('.item-stub').exists()).toBe(true)
    await wrapper.find('.item-stub').trigger('click')
    
    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue')[0][0]).toEqual(mockResult)
  })

  it('displays loading spinner during geocoding', async () => {
    mapService.geocode.mockReturnValue(new Promise(() => {})) // Never resolves
    
    const wrapper = mount(AddressInput, {
      props: {
        modelValue: '',
        geocode: true
      },
      global: {
        stubs: {
          'v-textarea': VTextareaStub,
          'v-combobox': VComboboxStub
        }
      }
    })

    await wrapper.find('.v-combobox-stub').setValue('123 Main')
    vi.advanceTimersByTime(300)
    await wrapper.vm.$nextTick()
    
    expect(wrapper.find('.loading-spinner').exists()).toBe(true)
  })
})
