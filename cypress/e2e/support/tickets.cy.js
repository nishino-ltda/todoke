/**
 * Support Tickets Management E2E Tests
 * Focus on listing, filtering, and searching
 */

const CUSTOMER_EMAIL = 'customer@todoke.test';
const CUSTOMER_PASSWORD = 'password123';

describe('🛠️ Support Tickets Management', () => {
  beforeEach(() => {
    localStorage.setItem('locale', 'pt-BR');
    cy.visit('/login');
    cy.get('[data-cy="email-input"] input').type(CUSTOMER_EMAIL);
    cy.get('[data-cy="password-input"] input').type(CUSTOMER_PASSWORD);
    cy.get('[data-cy="login-button"]').click();
    
    cy.url().should('not.include', '/login');
    cy.visit('/support/tickets');
  });

  it('📋 Should render tickets table with correct columns', () => {
    cy.log('🧪 Testing table rendering');
    const mockTickets = [
      { id: 1, subject: 'Issue A', category: 'account', status: 'open', created_at: new Date().toISOString(), updated_at: new Date().toISOString() }
    ];
    cy.intercept('GET', '**/api/v1/support/tickets', { body: { data: mockTickets } }).as('getTickets');
    cy.reload();
    cy.wait('@getTickets');
    
    cy.get('[data-cy="tickets-table"]').should('be.visible');
    cy.get('thead th').should('contain', 'ID');
    cy.get('thead th').should('contain', 'Assunto');
    cy.get('thead th').should('contain', 'Categoria');
    cy.get('thead th').should('contain', 'Status');
  });

  it('🔍 Should filter tickets by status', () => {
    cy.log('🧪 Testing status filters');
    cy.intercept('GET', '**/api/v1/support/tickets', { 
      body: { 
        data: [
          { id: 1, subject: 'Open Ticket', status: 'open', category: 'account' },
          { id: 2, subject: 'Closed Ticket', status: 'closed', category: 'payment' }
        ] 
      } 
    }).as('getTickets');
    
    cy.reload();
    cy.wait('@getTickets');
    
    // Check all visible
    cy.get('tbody tr').should('have.length', 2);
    
    // Filter by Open
    cy.get('[data-cy="status-filter-btn"]').contains('Aberto').click();
    cy.get('tbody tr').should('have.length', 1);
    cy.get('tbody tr').first().should('contain', 'Open Ticket');
    
    // Filter by Closed
    cy.get('[data-cy="status-filter-btn"]').contains('Fechado').click();
    cy.get('tbody tr').should('have.length', 1);
    cy.get('tbody tr').first().should('contain', 'Closed Ticket');
  });

  it('🔎 Should search for tickets by subject', () => {
    cy.log('🧪 Testing search functionality');
    cy.intercept('GET', '**/api/v1/support/tickets', { 
      body: { 
        data: [
          { id: 1, subject: 'Problem with drone', status: 'open', category: 'delivery_issue' },
          { id: 2, subject: 'Login issue', status: 'open', category: 'account' }
        ] 
      } 
    }).as('getTickets');
    
    cy.reload();
    cy.wait('@getTickets');
    
    cy.get('[data-cy="table-search"]').type('drone');
    cy.get('tbody tr').should('have.length', 1);
    cy.get('tbody tr').first().should('contain', 'Problem with drone');
  });

  it('📭 Should display empty state when no tickets exist', () => {
    cy.log('🧪 Testing empty state');
    cy.intercept('GET', '**/api/v1/support/tickets', { body: { data: [] } }).as('getEmptyTickets');
    
    cy.reload();
    cy.wait('@getEmptyTickets');
    
    cy.get('[data-cy="empty-state"]').should('be.visible');
    cy.get('[data-cy="empty-state"]').should('contain', 'Nenhum ticket encontrado');
  });

  it('📄 Should display pagination (if many items)', () => {
    cy.log('🧪 Testing pagination display');
    const manyTickets = Array.from({ length: 15 }, (_, i) => ({
      id: i + 1,
      subject: `Ticket ${i + 1}`,
      category: 'other',
      status: 'open',
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString()
    }));
    
    cy.intercept('GET', '**/api/v1/support/tickets', { body: { data: manyTickets } }).as('getManyTickets');
    
    cy.reload();
    cy.wait('@getManyTickets');
    
    cy.get('.v-pagination').should('exist');
  });
});
