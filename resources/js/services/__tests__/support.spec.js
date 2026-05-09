import supportService from '../support'
import api from '../api'
import { useLogStore } from '../../stores/log.js'
import { vi } from 'vitest'
import { createTestingPinia } from '@pinia/testing'

describe('Support Service', () => {
  let logStore

  beforeEach(() => {
    createTestingPinia()
    logStore = useLogStore()
    vi.clearAllMocks()
  })

  describe('getTickets', () => {
    it('should fetch tickets and log the action', async () => {
      const mockResponse = { data: [{ id: 1, subject: 'Issue 1' }] }
      api.get = vi.fn().mockResolvedValueOnce(mockResponse)

      const result = await supportService.getTickets()

      expect(api.get).toHaveBeenCalledWith('/support/tickets')
      expect(result).toEqual(mockResponse)
      expect(logStore.log).toHaveBeenCalledWith('🎟️ Fetching support tickets')
      expect(logStore.log).toHaveBeenCalledWith('✅ Tickets loaded')
    })

    it('should log error on failure', async () => {
      const error = new Error('Network Error')
      api.get = vi.fn().mockRejectedValueOnce(error)

      await expect(supportService.getTickets()).rejects.toThrow(error)
      expect(logStore.log).toHaveBeenCalledWith('❌ Failed to load tickets', 'error')
    })
  })

  describe('getTicket', () => {
    it('should fetch a single ticket and log the action', async () => {
      const mockResponse = { data: { id: 1, subject: 'Issue 1' } }
      api.get = vi.fn().mockResolvedValueOnce(mockResponse)

      const result = await supportService.getTicket(1)

      expect(api.get).toHaveBeenCalledWith('/support/tickets/1')
      expect(result).toEqual(mockResponse)
      expect(logStore.log).toHaveBeenCalledWith('🎟️ Fetching ticket #1')
      expect(logStore.log).toHaveBeenCalledWith('✅ Ticket #1 loaded')
    })
  })

  describe('createTicket', () => {
    it('should post new ticket data and log success', async () => {
      const ticketData = { subject: 'New Issue', message: 'Help me' }
      const mockResponse = { data: { id: 2, ...ticketData } }
      api.post = vi.fn().mockResolvedValueOnce(mockResponse)

      const result = await supportService.createTicket(ticketData)

      expect(api.post).toHaveBeenCalledWith('/support/tickets', ticketData)
      expect(result).toEqual(mockResponse)
      expect(logStore.log).toHaveBeenCalledWith('🎟️ Creating new support ticket')
      expect(logStore.log).toHaveBeenCalledWith('✅ Ticket created successfully')
    })
  })

  describe('addReply', () => {
    it('should post reply and log success', async () => {
      const replyData = { message: 'Thanks for the update' }
      const mockResponse = { data: { id: 10, ...replyData } }
      api.post = vi.fn().mockResolvedValueOnce(mockResponse)

      const result = await supportService.addReply(1, replyData)

      expect(api.post).toHaveBeenCalledWith('/support/tickets/1/reply', replyData)
      expect(result).toEqual(mockResponse)
      expect(logStore.log).toHaveBeenCalledWith('💬 Adding reply to ticket #1')
      expect(logStore.log).toHaveBeenCalledWith('✅ Reply added')
    })
  })

  describe('getFaqs', () => {
    it('should fetch FAQs and log success', async () => {
      const mockResponse = { data: [{ question: 'How to use?', answer: 'Like this.' }] }
      api.get = vi.fn().mockResolvedValueOnce(mockResponse)

      const result = await supportService.getFaqs()

      expect(api.get).toHaveBeenCalledWith('/support/faq')
      expect(result).toEqual(mockResponse)
      expect(logStore.log).toHaveBeenCalledWith('📚 Fetching FAQs')
      expect(logStore.log).toHaveBeenCalledWith('✅ FAQs loaded')
    })
  })
})
