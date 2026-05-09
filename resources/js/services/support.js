import api from './api';
import { useLogStore } from '@/stores/log';

export const supportService = {
  /**
   * Get all support tickets for the authenticated user
   */
  async getTickets() {
    const logStore = useLogStore();
    logStore.log('🎟️ Fetching support tickets');
    try {
      const response = await api.get('/support/tickets');
      logStore.log('✅ Tickets loaded');
      return response;
    } catch (error) {
      logStore.log('❌ Failed to load tickets', 'error');
      throw error;
    }
  },

  /**
   * Get a single ticket details
   * @param {string|number} id 
   */
  async getTicket(id) {
    const logStore = useLogStore();
    logStore.log(`🎟️ Fetching ticket #${id}`);
    try {
      const response = await api.get(`/support/tickets/${id}`);
      logStore.log(`✅ Ticket #${id} loaded`);
      return response;
    } catch (error) {
      logStore.log(`❌ Failed to load ticket #${id}`, 'error');
      throw error;
    }
  },

  /**
   * Create a new support ticket
   * @param {Object} data 
   */
  async createTicket(data) {
    const logStore = useLogStore();
    logStore.log('🎟️ Creating new support ticket');
    try {
      const response = await api.post('/support/tickets', data);
      logStore.log('✅ Ticket created successfully');
      return response;
    } catch (error) {
      logStore.log('❌ Failed to create ticket', 'error');
      throw error;
    }
  },

  /**
   * Add a reply to a ticket
   * @param {string|number} ticketId 
   * @param {Object} data 
   */
  async addReply(ticketId, data) {
    const logStore = useLogStore();
    logStore.log(`💬 Adding reply to ticket #${ticketId}`);
    try {
      const response = await api.post(`/support/tickets/${ticketId}/reply`, data);
      logStore.log('✅ Reply added');
      return response;
    } catch (error) {
      logStore.log(`❌ Failed to add reply to ticket #${ticketId}`, 'error');
      throw error;
    }
  },

  /**
   * Get FAQ list
   */
  async getFaqs() {
    const logStore = useLogStore();
    logStore.log('📚 Fetching FAQs');
    try {
      const response = await api.get('/support/faq');
      logStore.log('✅ FAQs loaded');
      return response;
    } catch (error) {
      logStore.log('❌ Failed to load FAQs', 'error');
      throw error;
    }
  }
};

export default supportService;
