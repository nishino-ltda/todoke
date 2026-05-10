/**
 * Admin Flow E2E Tests — Sprint 7
 * Full admin workflow with cy.intercept mocks
 */

const ADMIN_EMAIL = 'admin@todoke.test';
const ADMIN_PASSWORD = 'password123';

const mockStats = { total_users: 150, active_deliveries: 25, total_nodes: 10, reported_issues: 2 };
const mockUsers = [
  { id: 1, name: 'João Silva', email: 'joao@example.com', role: 'customer', active: true },
  { id: 2, name: 'Maria Santos', email: 'maria@example.com', role: 'courier', active: false }
];
const mockNodes = [
  { id: 1, name: 'Hub Central', type: 'hub', owner_name: 'Parceiro A', status: 'pending' }
];
const mockDeliveries = [
  {
    id: 1, status: 'in_transit', value: 25.50, type: 'ground',
    customer: { name: 'Cliente Teste' }, courier: { name: 'Entregador Teste' },
    origin_address: 'Rua A, 100', destination_address: 'Rua B, 200',
    updated_at: new Date().toISOString()
  }
];

const interceptAll = () => {
  cy.intercept('GET', '**/api/v1/admin/stats', { statusCode: 200, body: mockStats }).as('getStats');
  cy.intercept('GET', '**/api/v1/admin/users*', { statusCode: 200, body: mockUsers }).as('getUsers');
  cy.intercept('GET', '**/api/v1/admin/nodes', { statusCode: 200, body: mockNodes }).as('getNodes');
  cy.intercept('GET', '**/api/v1/admin/deliveries*', {
    statusCode: 200, body: { deliveries: mockDeliveries }
  }).as('getDeliveries');
};

describe('👔 Admin Flow', () => {
  beforeEach(() => {
    interceptAll();
    cy.visit('/login');
    cy.get('[data-cy="email-input"] input, [data-cy="email-input"]').type(ADMIN_EMAIL);
    cy.get('[data-cy="password-input"] input, [data-cy="password-input"]').type(ADMIN_PASSWORD);
    cy.get('[data-cy="login-button"], [data-cy="submit-btn"]').click();
    cy.url().should('include', '/admin');
  });

  it('📊 Should display system metrics on dashboard', () => {
    cy.wait('@getStats');
    cy.get('[data-cy="admin-metric"]').should('have.length.at.least', 4);
    cy.get('[data-cy="admin-dashboard"]').should('exist');
  });

  it('📈 Should show charts with time period filter', () => {
    cy.wait('@getStats');
    cy.get('[data-cy="chart-period-filter"]').should('exist');
    cy.get('[data-cy="filter-7days"]').should('exist');
    cy.get('[data-cy="filter-30days"]').click({ force: true });
    cy.get('[data-cy="activity-chart"]').should('exist');
  });

  it('👥 Should navigate to users and display list', () => {
    cy.visit('/admin/users');
    cy.wait('@getUsers');
    cy.get('[data-cy="users-table"]').should('be.visible');
    cy.get('[data-cy="deactivate-user-btn"]').should('exist');
  });

  it('🔄 Should activate/deactivate a user', () => {
    cy.intercept('POST', '**/api/v1/admin/users/*/activate', {
      statusCode: 200, body: { success: true }
    }).as('activateUser');
    cy.intercept('GET', '**/api/v1/admin/users*', { statusCode: 200, body: mockUsers }).as('getUsersRefresh');

    cy.visit('/admin/users');
    cy.wait('@getUsers');
    cy.get('[data-cy="activate-user-btn"]').first().click();
    cy.wait('@activateUser');
  });

  it('📍 Should navigate to nodes and approve one', () => {
    cy.intercept('PATCH', '**/api/v1/admin/nodes/*/status', {
      statusCode: 200, body: { id: 1, status: 'approved' }
    }).as('approveNode');

    cy.visit('/admin/nodes');
    cy.wait('@getNodes');
    cy.get('[data-cy="nodes-table"]').should('be.visible');
    cy.get('[data-cy="approve-node-btn"]').first().click();
    cy.wait('@approveNode');
  });

  it('⚙️ Should update platform settings', () => {
    cy.intercept('GET', '**/api/v1/admin/settings', {
      statusCode: 200,
      body: { site_name: 'TODOKE', support_email: 'support@todoke.test', maintenance_mode: false, allow_registration: true, delivery_fee_base: 5, delivery_fee_per_km: 1.5 }
    }).as('getSettings');
    cy.intercept('PATCH', '**/api/v1/admin/settings', {
      statusCode: 200, body: { success: true }
    }).as('saveSettings');

    cy.visit('/admin/settings');
    cy.wait('@getSettings');
    cy.get('[data-cy="setting-site-name"] input').clear().type('Todoke Premium');
    cy.get('[data-cy="save-settings-btn"]').click();
    cy.wait('@saveSettings');
  });

  it('📦 Should monitor deliveries', () => {
    cy.visit('/admin/deliveries');
    cy.wait('@getDeliveries');
    cy.get('[data-cy="deliveries-monitoring"]').should('be.visible');
    cy.get('[data-cy="deliveries-table"]').should('exist');
  });
});
