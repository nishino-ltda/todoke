/**
 * Admin Delivery Monitoring E2E Tests — Sprint 7
 */

const ADMIN_EMAIL = 'admin@todoke.test';
const ADMIN_PASSWORD = 'password123';

const today = new Date().toISOString();
const mockDeliveries = [
  {
    id: 101, status: 'pending', value: 18.50, type: 'ground',
    customer: { name: 'Cliente A' }, courier: null,
    origin_address: 'Rua das Flores, 10', destination_address: 'Av. Paulista, 500',
    origin_lat: -23.55, origin_lng: -46.63, destination_lat: -23.56, destination_lng: -46.65,
    updated_at: today
  },
  {
    id: 102, status: 'in_transit', value: 25.00, type: 'ground',
    customer: { name: 'Cliente B' }, courier: { name: 'Entregador X' },
    origin_address: 'Rua Augusta, 100', destination_address: 'Rua Oscar Freire, 200',
    updated_at: today
  },
  {
    id: 103, status: 'delivered', value: 32.00, type: 'drone',
    customer: { name: 'Cliente C' }, courier: { name: 'Drone-001' },
    origin_address: 'Rua D, 50', destination_address: 'Av. E, 300',
    updated_at: today
  }
];

const loginAsAdmin = () => {
  cy.intercept('GET', '**/api/v1/admin/deliveries*', {
    statusCode: 200, body: { deliveries: mockDeliveries }
  }).as('getDeliveries');
  cy.visit('/login');
  cy.get('[data-cy="email-input"] input, [data-cy="email-input"]').type(ADMIN_EMAIL);
  cy.get('[data-cy="password-input"] input, [data-cy="password-input"]').type(ADMIN_PASSWORD);
  cy.get('[data-cy="login-button"], [data-cy="submit-btn"]').click();
  cy.url().should('include', '/admin');
};

describe('📦 Admin Delivery Monitoring', () => {
  beforeEach(() => {
    loginAsAdmin();
    cy.visit('/admin/deliveries');
    cy.wait('@getDeliveries');
  });

  it('📊 Should display metric cards with correct counts', () => {
    cy.get('[data-cy="deliveries-monitoring"]').should('be.visible');
    cy.get('[data-cy="metric-active"]').should('exist');
    cy.get('[data-cy="metric-pending"]').should('exist');
    cy.get('[data-cy="metric-in-transit"]').should('exist');
    cy.get('[data-cy="metric-delivered-today"]').should('exist');
    // 1 pending delivery
    cy.get('[data-cy="metric-pending"]').should('contain.text', '1');
    // 1 in_transit
    cy.get('[data-cy="metric-in-transit"]').should('contain.text', '1');
  });

  it('🏷️ Should display status chips for each delivery', () => {
    cy.get('[data-cy="deliveries-table"]').should('exist');
    cy.get('[data-cy="delivery-status-chip"]').should('have.length.at.least', 1);
  });

  it('👁️ Should open delivery detail modal on row action click', () => {
    cy.get('[data-cy="view-delivery-btn"]').first().click();
    cy.get('[data-cy="delivery-detail-modal"]').should('exist');
    cy.get('[data-cy="detail-origin"]').should('contain.text', 'Rua das Flores');
    cy.get('[data-cy="detail-destination"]').should('contain.text', 'Av. Paulista');
  });

  it('🔄 Should refresh deliveries on button click', () => {
    cy.intercept('GET', '**/api/v1/admin/deliveries*', {
      statusCode: 200, body: { deliveries: mockDeliveries }
    }).as('getDeliveriesRefresh');

    cy.get('[data-cy="refresh-deliveries-btn"]').click();
    cy.wait('@getDeliveriesRefresh');
    cy.get('[data-cy="deliveries-table"]').should('be.visible');
  });

  it('⚠️ Should show error state when API fails', () => {
    cy.intercept('GET', '**/api/v1/admin/deliveries*', {
      statusCode: 500, body: { message: 'Server Error' }
    }).as('deliveriesFail');

    cy.visit('/admin/deliveries');
    cy.wait('@deliveriesFail');
    cy.get('body').should('contain.text', 'Falha');
  });
});
