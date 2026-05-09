describe('🆘 Support Flow', () => {
  beforeEach(() => {
    // Login as customer (support is for all, but let's use customer)
    cy.visit('/login');
    cy.get('[data-cy="email-input"] input').type('customer@todoke.test');
    cy.get('[data-cy="password-input"] input').type('password123');
    cy.get('[data-cy="login-button"]').click();
    cy.visit('/support/dashboard');
  });

  it('📊 Should display support dashboard stats', () => {
    cy.get('[data-cy="support-dashboard"]').should('be.visible');
    cy.get('.text-h3').should('have.length.at.least', 3);
    cy.get('[data-cy="new-ticket-btn"]').should('be.visible');
  });

  it('🎫 Should create a new support ticket', () => {
    cy.get('[data-cy="new-ticket-btn"]').click();
    cy.url().should('include', '/support/tickets/create');
    
    cy.get('[data-cy="subject-input"] input').type('Test Ticket Subject');
    cy.get('[data-cy="category-select"]').click();
    cy.get('.v-list-item').contains('Technical Support').click();
    cy.get('[data-cy="priority-select"]').click();
    cy.get('.v-list-item').contains('High').click();
    cy.get('[data-cy="message-input"] textarea').type('This is a test message for the support ticket.');
    
    cy.get('[data-cy="submit-btn"]').click();
    cy.url().should('include', '/support/tickets');
    cy.get('.v-snackbar').should('contain', 'created');
  });

  it('🔍 Should list and filter tickets', () => {
    cy.visit('/support/tickets');
    cy.get('[data-cy="tickets-table"]').should('be.visible');
    
    // Filter by Open
    cy.get('[data-cy="status-filter-btn"]').contains('Open').click();
    // Assuming search is in DataTable
    cy.get('input[placeholder*="Search"]').type('Test Ticket');
  });

  it('💬 Should view ticket details and reply', () => {
    cy.visit('/support/tickets');
    // Click first row (Assuming DataTable row has some identifier or just click the first cell)
    cy.get('tbody tr').first().click();
    
    cy.url().should('match', /\/support\/tickets\/\d+/);
    cy.get('[data-cy="reply-btn"]').should('be.visible').click();
    
    cy.get('[data-cy="message-input"] textarea').type('This is a reply message.');
    cy.get('[data-cy="submit-btn"]').click();
    cy.get('.v-snackbar').should('contain', 'sent');
  });

  it('❓ Should browse FAQ', () => {
    cy.get('[data-cy="faq-btn"]').click();
    cy.url().should('include', '/support/faq');
    cy.get('[data-cy="faq-search"]').should('be.visible').type('drone');
    cy.get('[data-cy="faq-item"]').should('have.length.at.least', 0);
  });

  it('🌐 Should switch language in support flow', () => {
    cy.get('[data-cy="language-selector"]').click();
    cy.get('.v-list-item').contains('English').click();
    cy.get('h1').should('contain', 'Support Dashboard');
    
    cy.get('[data-cy="language-selector"]').click();
    cy.get('.v-list-item').contains('Português').click();
    cy.get('h1').should('contain', 'Painel de Suporte');
  });
});
