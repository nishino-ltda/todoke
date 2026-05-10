describe('🍽️ Partner Dashboard', () => {
  beforeEach(() => {
    cy.visit('/login');
    cy.get('[data-cy="email-input"] input').type('partner@todoke.test');
    cy.get('[data-cy="password-input"] input').type('password123');
    cy.get('[data-cy="login-button"]').click();
    cy.url().should('include', '/partner/dashboard');
  });

  it('📊 Should display metrics and charts', () => {
    // Metrics
    cy.get('[data-cy="dashboard-metric"]').should('have.length', 4);
    
    // Charts
    cy.get('[data-cy="order-volume-chart"]').should('be.visible');
    cy.get('[data-cy="revenue-chart"]').should('be.visible');
  });

  it('📅 Should filter charts by period', () => {
    cy.get('[data-cy="chart-period-filter"]').should('be.visible');
    
    // Today
    cy.get('[data-cy="filter-today"]').click();
    cy.get('[data-cy="order-volume-chart"]').should('be.visible');

    // 30 days
    cy.get('[data-cy="filter-30days"]').click();
    cy.get('[data-cy="order-volume-chart"]').should('be.visible');
  });

  it('📦 Should show recent orders and link to details', () => {
    cy.get('[data-cy="recent-orders-table"]').should('be.visible');
    cy.get('[data-cy="view-order-btn"]').first().click();
    cy.url().should('match', /\/partner\/orders\/\d+/);
  });
});
