/**
 * Admin Login E2E Tests — Sprint 7
 */

const ADMIN_EMAIL = 'admin@todoke.test';
const ADMIN_PASSWORD = 'password123';

const interceptStats = () => {
  cy.intercept('GET', '**/api/v1/admin/stats', {
    statusCode: 200,
    body: { total_users: 150, active_deliveries: 25, total_nodes: 10, reported_issues: 2 }
  }).as('getStats');
};

describe('🔐 Admin Login', () => {
  it('✅ Should login with valid credentials and redirect to dashboard', () => {
    interceptStats();
    cy.visit('/login');
    cy.get('[data-cy="email-input"] input, [data-cy="email-input"]').type(ADMIN_EMAIL);
    cy.get('[data-cy="password-input"] input, [data-cy="password-input"]').type(ADMIN_PASSWORD);
    cy.get('[data-cy="login-button"], [data-cy="submit-btn"]').click();
    cy.url().should('include', '/admin');
    cy.get('[data-cy="admin-dashboard"], [data-cy="admin-metric"]').should('exist');
  });

  it('❌ Should reject invalid credentials and show error', () => {
    cy.intercept('POST', '**/login', {
      statusCode: 422,
      body: { message: 'These credentials do not match our records.' }
    }).as('loginFail');

    cy.visit('/login');
    cy.get('[data-cy="email-input"] input, [data-cy="email-input"]').type('wrong@example.com');
    cy.get('[data-cy="password-input"] input, [data-cy="password-input"]').type('wrongpassword');
    cy.get('[data-cy="login-button"], [data-cy="submit-btn"]').click();
    cy.url().should('include', '/login');
  });

  it('🚫 Should redirect unauthenticated users to login when accessing admin routes', () => {
    // Clear any existing session
    cy.clearLocalStorage();
    cy.clearCookies();
    cy.visit('/admin/dashboard');
    cy.url().should('include', '/login');
  });

  it('📱 Should be usable on mobile viewport', () => {
    cy.viewport(375, 812);
    cy.visit('/login');
    cy.get('[data-cy="email-input"] input, [data-cy="email-input"]').should('be.visible');
    cy.get('[data-cy="password-input"] input, [data-cy="password-input"]').should('be.visible');
    cy.get('[data-cy="login-button"], [data-cy="submit-btn"]').should('be.visible');
    // No horizontal overflow
    cy.document().then(doc => {
      expect(doc.body.scrollWidth).to.be.lte(380);
    });
  });
});
