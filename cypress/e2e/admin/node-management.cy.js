/**
 * Admin Node Management E2E Tests — Sprint 7
 */

const ADMIN_EMAIL = 'admin@todoke.test';
const ADMIN_PASSWORD = 'password123';

const mockNodes = [
  { id: 1, name: 'Hub São Paulo', type: 'hub', owner_name: 'Parceiro SP', status: 'pending' },
  { id: 2, name: 'Drone Station Rio', type: 'drone', owner_name: 'Parceiro Rio', status: 'approved' },
  { id: 3, name: 'Hub Curitiba', type: 'hub', owner_name: 'Parceiro PR', status: 'pending' },
];

const loginAsAdmin = () => {
  cy.intercept('GET', '**/api/v1/admin/nodes', { statusCode: 200, body: mockNodes }).as('getNodes');
  cy.visit('/login');
  cy.get('[data-cy="email-input"] input, [data-cy="email-input"]').type(ADMIN_EMAIL);
  cy.get('[data-cy="password-input"] input, [data-cy="password-input"]').type(ADMIN_PASSWORD);
  cy.get('[data-cy="login-button"], [data-cy="submit-btn"]').click();
  cy.url().should('include', '/admin');
};

describe('📡 Admin Node Management', () => {
  beforeEach(() => {
    loginAsAdmin();
    cy.visit('/admin/nodes');
    cy.wait('@getNodes');
  });

  it('📋 Should display node list with status badges and action buttons', () => {
    cy.get('[data-cy="nodes-table"]').should('be.visible');
    cy.get('[data-cy="approve-node-btn"]').should('have.length.at.least', 1);
    cy.get('[data-cy="reject-node-btn"]').should('have.length.at.least', 1);
  });

  it('✅ Should approve a pending node', () => {
    cy.intercept('PATCH', '**/api/v1/admin/nodes/1/status', {
      statusCode: 200, body: { id: 1, status: 'approved', name: 'Hub São Paulo' }
    }).as('approveNode');
    cy.intercept('GET', '**/api/v1/admin/nodes', {
      statusCode: 200,
      body: mockNodes.map(n => n.id === 1 ? { ...n, status: 'approved' } : n)
    }).as('getNodesRefresh');

    cy.get('[data-cy="approve-node-btn"]').first().click();
    cy.wait('@approveNode');
    cy.wait('@getNodesRefresh');
    cy.get('body').should('contain.text', 'Hub São Paulo');
  });

  it('❌ Should reject a pending node', () => {
    cy.intercept('PATCH', '**/api/v1/admin/nodes/1/status', {
      statusCode: 200, body: { id: 1, status: 'rejected', name: 'Hub São Paulo' }
    }).as('rejectNode');
    cy.intercept('GET', '**/api/v1/admin/nodes', {
      statusCode: 200,
      body: mockNodes.map(n => n.id === 1 ? { ...n, status: 'rejected' } : n)
    }).as('getNodesRefresh');

    cy.get('[data-cy="reject-node-btn"]').first().click();
    cy.wait('@rejectNode');
  });

  it('⚠️ Should show error when node approval fails', () => {
    cy.intercept('PATCH', '**/api/v1/admin/nodes/1/status', {
      statusCode: 500, body: { message: 'Server error' }
    }).as('approveFail');

    cy.get('[data-cy="approve-node-btn"]').first().click();
    cy.wait('@approveFail');
    cy.get('body').should('contain.text', 'Falha');
  });

  it('👁️ Should show edit button for approved nodes', () => {
    cy.get('[data-cy="edit-node-btn"]').should('exist');
  });
});
