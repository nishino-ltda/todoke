/**
 * Support Ticket System E2E Tests
 * Focus on ticket lifecycle, validation, and edge cases
 */

const CUSTOMER_EMAIL = 'customer@todoke.test';
const CUSTOMER_PASSWORD = 'password123';

describe('🛠️ Support Ticket System', () => {
  beforeEach(() => {
    localStorage.setItem('locale', 'pt-BR');
    cy.visit('/login');
    cy.get('[data-cy="email-input"] input').type(CUSTOMER_EMAIL);
    cy.get('[data-cy="password-input"] input').type(CUSTOMER_PASSWORD);
    cy.get('[data-cy="login-button"]').click();
    
    cy.url().should('not.include', '/login');
    cy.visit('/support/tickets/create');
  });

  it('📝 Should validate required fields in ticket creation', () => {
    cy.log('🧪 Testing validation errors');
    
    // Try to submit empty form
    cy.get('[data-cy="submit-btn"]').should('be.disabled');
    
    // Fill some fields and check if button enables
    cy.get('[data-cy="subject-input"] input').type('Validation Test');
    cy.get('[data-cy="submit-btn"]').should('be.disabled');
    
    cy.get('[data-cy="category-select"]').click();
    cy.get('.v-list-item').contains('Conta').click();
    cy.get('[data-cy="submit-btn"]').should('be.disabled');
    
    cy.get('[data-cy="message-input"] textarea').type('This is a test message.');
    // Priority has a default value 'medium' in the component
    cy.get('[data-cy="submit-btn"]').should('not.be.disabled');
  });

  it('✅ Should submit valid form and show success notification', () => {
    cy.log('🧪 Testing successful submission');
    cy.intercept('POST', '**/api/v1/support/tickets', { 
      statusCode: 201, 
      body: { data: { id: 10, subject: 'Valid Ticket' } } 
    }).as('createTicket');
    
    cy.get('[data-cy="subject-input"] input').type('Valid Ticket');
    cy.get('[data-cy="category-select"]').click();
    cy.get('.v-list-item').contains('Conta').click();
    cy.get('[data-cy="message-input"] textarea').type('Everything is correct.');
    
    cy.get('[data-cy="submit-btn"]').click();
    cy.wait('@createTicket');
    
    cy.get('.v-snackbar').should('be.visible').and('contain', 'sucesso');
    cy.url().should('match', /\/support\/tickets$/);
  });

  it('🔍 Should display ticket details correctly', () => {
    cy.log('🧪 Testing ticket detail display');
    const mockTicket = {
      id: 123,
      subject: 'Display Test',
      category: 'payment',
      priority: 'high',
      status: 'open',
      created_at: '2026-05-10T10:00:00Z',
      messages: [
        { user_name: 'John Doe', body: 'Original message body.', created_at: '2026-05-10T10:00:00Z' }
      ]
    };
    
    cy.intercept('GET', '**/api/v1/support/tickets/123', { body: { data: mockTicket } }).as('getTicket');
    cy.visit('/support/tickets/123');
    cy.wait('@getTicket');
    
    cy.get('[data-cy="ticket-subject"]').should('contain', 'Display Test');
    cy.get('[data-cy="ticket-status-chip"]').should('contain', 'Aberto');
    cy.contains('Pagamento').should('be.visible');
    cy.contains('Alta').should('be.visible');
    cy.get('[data-cy="ticket-message"]').should('contain', 'Original message body.');
  });

  it('⚠️ Should handle API errors (500) gracefully', () => {
    cy.log('🧪 Testing API error handling');
    cy.intercept('POST', '**/api/v1/support/tickets', { 
      statusCode: 500, 
      body: { message: 'Server Error' } 
    }).as('createTicketError');
    
    cy.get('[data-cy="subject-input"] input').type('Error Test');
    cy.get('[data-cy="category-select"]').click();
    cy.get('.v-list-item').contains('Outro').click();
    cy.get('[data-cy="message-input"] textarea').type('This will fail.');
    
    cy.get('[data-cy="submit-btn"]').click();
    cy.wait('@createTicketError');
    
    cy.get('.v-snackbar').should('be.visible').and('class', 'v-snackbar--variant-flat');
    cy.get('.v-snackbar').should('contain', 'Falha');
  });
});
