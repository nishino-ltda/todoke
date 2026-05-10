import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useSupportStore = defineStore('support', () => {
  const tickets = ref([]);
  const currentTicket = ref(null);
  const faqs = ref([]);
  const locale = ref('pt-BR');

  function setTickets(newTickets) {
    tickets.value = newTickets;
  }

  function setCurrentTicket(ticket) {
    currentTicket.value = ticket;
  }

  function setFaqs(newFaqs) {
    faqs.value = newFaqs;
  }

  function setLocale(newLocale) {
    locale.value = newLocale;
  }

  function clearStore() {
    tickets.value = [];
    currentTicket.value = null;
    faqs.value = [];
  }

  return {
    tickets,
    currentTicket,
    faqs,
    locale,
    setTickets,
    setCurrentTicket,
    setFaqs,
    setLocale,
    clearStore
  };
});
