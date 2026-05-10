import { setActivePinia, createPinia } from 'pinia'
import { useSupportStore } from '../support'
import { describe, it, expect, beforeEach } from 'vitest'

describe('Support Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('sets tickets correctly', () => {
    const store = useSupportStore()
    const mockTickets = [{ id: 1, subject: 'Test' }]
    store.setTickets(mockTickets)
    expect(store.tickets).toEqual(mockTickets)
  })

  it('sets current ticket correctly', () => {
    const store = useSupportStore()
    const mockTicket = { id: 1, subject: 'Test', messages: [] }
    store.setCurrentTicket(mockTicket)
    expect(store.currentTicket).toEqual(mockTicket)
  })

  it('sets faqs correctly', () => {
    const store = useSupportStore()
    const mockFaqs = [{ question: 'Q1', answer: 'A1' }]
    store.setFaqs(mockFaqs)
    expect(store.faqs).toEqual(mockFaqs)
  })

  it('sets locale correctly', () => {
    const store = useSupportStore()
    store.setLocale('en')
    expect(store.locale).toBe('en')
  })

  it('clears store correctly', () => {
    const store = useSupportStore()
    store.setTickets([{ id: 1 }])
    store.setCurrentTicket({ id: 1 })
    store.setFaqs([{ q: 'a' }])
    
    store.clearStore()
    
    expect(store.tickets).toHaveLength(0)
    expect(store.currentTicket).toBeNull()
    expect(store.faqs).toHaveLength(0)
  })
})
