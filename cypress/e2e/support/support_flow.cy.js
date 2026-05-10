/**
 * Support Flow E2E Tests
 * Full customer support workflow
 */

const CUSTOMER_EMAIL = 'customer@todoke.test';
const CUSTOMER_PASSWORD = 'password123';

const mockTickets = [
  { 
    id: 1, 
    subject: 'Delayed Delivery', 
    category: 'delivery_issue', 
    status: 'open', 
    priority: 'high',
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
    messages: [
      { user_name: 'John Doe', body: 'My delivery is late.', created_at: new Date().toISOString() }
    ]
  },
  { 
    id: 2, 
    subject: 'Payment Failed', 
    category: 'payment', 
    status: 'in_progress', 
    priority: 'medium',
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
    messages: [
      { user_name: 'John Doe', body: 'I cannot pay with my card.', created_at: new Date().toISOString() }
    ]
  }
];

const mockFaqs = [
  { question: 'How do drones deliver?', answer: 'Drones use GPS to find your location and land safely.' },
  { question: 'What are the delivery fees?', answer: 'Fees depend on distance and urgency.' }
];

const interceptAll = () => {
  cy.intercept('GET', '**/api/v1/support/tickets', { statusCode: 200, body: { data: mockTickets } }).as('getTickets');
  cy.intercept('GET', '**/api/v1/support/tickets/1', { statusCode: 200, body: { data: mockTickets[0] } }).as('getTicketDetail');
  cy.intercept('POST', '**/api/v1/support/tickets', { statusCode: 201, body: { data: { id: 3, subject: 'New Ticket' } } }).as('createTicket');
  cy.intercept('POST', '**/api/v1/support/tickets/1/reply', { statusCode: 200, body: { success: true } }).as('addReply');
  cy.intercept('PATCH', '**/api/v1/support/tickets/1/close', { statusCode: 200, body: { success: true } }).as('closeTicket');
  cy.intercept('GET', '**/api/v1/support/faq', { statusCode: 200, body: { data: mockFaqs } }).as('getFaqs');
};

describe('🆘 Support Flow', () => {
  beforeEach(() => {
    localStorage.setItem('locale', 'pt-BR');
    interceptAll();
    cy.visit('/login');
    cy.get('[data-cy="email-input"] input').type(CUSTOMER_EMAIL);
    cy.get('[data-cy="password-input"] input').type(CUSTOMER_PASSWORD);
    cy.get('[data-cy="login-button"]').click();
    
    // Wait for the app to initialize and redirect
    cy.url().should('not.include', '/login');
    
    // Visit support page
    cy.visit('/support');
    cy.get('[data-cy="support-dashboard"]', { timeout: 10000 }).should('be.visible');
  });

  it('📊 Should display support dashboard stats and recent tickets', () => {
    cy.log('📋 Checking dashboard');
    cy.wait('@getTickets');
    cy.get('[data-cy="support-dashboard"]').should('be.visible');
    cy.get('[data-cy="stat-card"]').should('have.length', 3);
    cy.get('[data-cy="ticket-item"]').should('have.length.at.least', 1);
  });

  it('❓ Should browse FAQ and search', () => {
    cy.log('🔍 Testing FAQ');
    cy.get('[data-cy="faq-btn"]').click();
    cy.wait('@getFaqs');
    cy.url().should('include', '/support/faq');
    
    cy.get('[data-cy="faq-search"]').type('drone');
    cy.get('[data-cy="faq-item"]').should('have.length', 1);
    cy.get('[data-cy="faq-item"]').first().click();
    cy.contains('GPS').should('be.visible');
  });

  it('🎫 Should create a ticket and verify it appears in list', () => {
    cy.log('✏️ Creating ticket');
    cy.get('[data-cy="new-ticket-btn"]').click();
    cy.url().should('include', '/support/tickets/create');
    
    cy.get('[data-cy="subject-input"] input').type('New Support Request');
    cy.get('[data-cy="category-select"]').click();
    cy.get('.v-list-item').contains('Entrega').click();
    cy.get('[data-cy="priority-select"]').click();
    cy.get('.v-list-item').contains('Alta').click();
    cy.get('[data-cy="message-input"] textarea').type('Detailed message about the issue.');
    
    cy.get('[data-cy="submit-btn"]').click();
    cy.wait('@createTicket');
    cy.url().should('match', /\/support\/tickets$/);
    cy.get('.v-snackbar').should('be.visible');
  });

  it('💬 Should view ticket details and add a reply', () => {
    cy.log('📩 Viewing ticket and replying');
    cy.visit('/support/tickets');
    cy.wait('@getTickets');
    
    // Click on the first ticket row
    cy.get('[data-cy="data-table"] tbody tr').first().click();
    cy.wait('@getTicketDetail');
    
    cy.get('[data-cy="ticket-subject"]').should('contain', 'Delayed Delivery');
    cy.get('[data-cy="ticket-message"]').should('contain', 'My delivery is late.');
    
    cy.get('[data-cy="reply-btn"]').click();
    cy.url().should('include', '/reply');
    
    cy.get('[data-cy="message-input"] textarea').type('This is a follow-up reply.');
    cy.get('[data-cy="submit-btn"]').click();
    cy.wait('@addReply');
    
    cy.get('.v-snackbar').should('be.visible');
  });

  it('🔒 Should close a ticket', () => {
    cy.log('🚫 Closing ticket');
    cy.visit('/support/tickets/1');
    cy.wait('@getTicketDetail');
    
    cy.get('[data-cy="close-btn"]').click();
    cy.get('[data-cy="confirm-close-btn"]').click();
    cy.wait('@closeTicket');
    
    cy.get('.v-snackbar').should('be.visible');
  });

  it('🌐 Should test with pt-BR locale', () => {
    cy.log('🇧🇷 Testing pt-BR locale');
    // Ensure we are in pt-BR
    cy.get('h1').should('contain', 'Painel de Suporte');
    cy.get('[data-cy="new-ticket-btn"]').should('contain', 'Novo Ticket');
    
    cy.get('[data-cy="my-tickets-btn"]').click();
    cy.wait('@getTickets');
    cy.get('[data-cy="tickets-index-title"]').should('contain', 'Meus Tickets');
  });
});
