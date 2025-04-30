import { mount } from '@vue/test-utils'
import AppFooter from '../AppFooter.vue'

describe('AppFooter', () => {
  it('renders correctly', () => {
    const wrapper = mount(AppFooter)
    const currentYear = new Date().getFullYear()

    expect(wrapper.find('.app-footer').exists()).toBe(true)
    expect(wrapper.text()).toContain(`© ${currentYear} TODOKE`)
    expect(wrapper.find('[to="/terms"]').exists()).toBe(true)
    expect(wrapper.find('[to="/privacy"]').exists()).toBe(true)
    expect(wrapper.findAll('.social-link').length).toBe(3)
  })

  it('contains correct social links', () => {
    const wrapper = mount(AppFooter)
    const links = wrapper.findAll('.social-link')

    expect(links[0].text()).toContain('Facebook')
    expect(links[1].text()).toContain('Twitter')
    expect(links[2].text()).toContain('Instagram')
  })
})
