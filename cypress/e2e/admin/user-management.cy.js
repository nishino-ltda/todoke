/**
 * Admin User Management E2E Tests — Sprint 7
 */

const ADMIN_EMAIL = 'admin@todoke.test';
const ADMIN_PASSWORD = 'password123';

const mockUsers = [
  { id: 1, name: 'João Silva', email: 'joao@example.com', role: 'customer', active: true },
  { id: 2, name: 'Ana Ferreira', email: 'ana@example.com', role: 'courier', active: false },
  { id: 3, name: 'Carlos Lima', email: 'carlos@example.com', role: 'partner', active: true },
];

const loginAsAdmin = () => {
  cy.intercept('GET', '**/api/v1/admin/users*', { statusCode: 200, body: mockUsers }).as('getUsers');
  cy.visit('/login');
  cy.get('[data-cy="email-input"] input, [data-cy="email-input"]').type(ADMIN_EMAIL);
  cy.get('[data-cy="password-input"] input, [data-cy="password-input"]').type(ADMIN_PASSWORD);
  cy.get('[data-cy="login-button"], [data-cy="submit-btn"]').click();
  cy.url().should('include', '/admin');
};

describe('👥 Admin User Management', () => {
  beforeEach(() => {
    loginAsAdmin();
    cy.visit('/admin/users');
    cy.wait('@getUsers');
  });

  it('📋 Should display user list with roles and status badges', () => {
    cy.get('[data-cy="users-table"]').should('be.visible');
    cy.get('[data-cy="deactivate-user-btn"]').should('exist');
    cy.get('[data-cy="activate-user-btn"]').should('exist');
  });

  it('🔴 Should deactivate an active user', () => {
    cy.intercept('POST', '**/api/v1/admin/users/1/deactivate', {
      statusCode: 200, body: { success: true }
    }).as('deactivate');
    cy.intercept('GET', '**/api/v1/admin/users*', {
      statusCode: 200,
      body: mockUsers.map(u => u.id === 1 ? { ...u, active: false } : u)
    }).as('getUsersRefresh');

    cy.get('[data-cy="deactivate-user-btn"]').first().click();
    cy.wait('@deactivate');
    cy.wait('@getUsersRefresh');
  });

  it('🟢 Should activate a suspended user', () => {
    cy.intercept('POST', '**/api/v1/admin/users/2/activate', {
      statusCode: 200, body: { success: true }
    }).as('activate');
    cy.intercept('GET', '**/api/v1/admin/users*', {
      statusCode: 200,
      body: mockUsers.map(u => u.id === 2 ? { ...u, active: true } : u)
    }).as('getUsersRefresh');

    cy.get('[data-cy="activate-user-btn"]').first().click();
    cy.wait('@activate');
    cy.wait('@getUsersRefresh');
  });

  it('⚠️ Should show error notification when user management API fails', () => {
    cy.intercept('POST', '**/api/v1/admin/users/1/deactivate', {
      statusCode: 500, body: { message: 'Server Error' }
    }).as('deactivateFail');

    cy.get('[data-cy="deactivate-user-btn"]').first().click();
    cy.wait('@deactivateFail');
    cy.get('body').should('contain.text', 'Falha');
  });

  it('📱 Should be usable on mobile viewport with scrollable table', () => {
    cy.viewport(375, 812);
    cy.get('[data-cy="users-table"]').should('be.visible');
  });
});
