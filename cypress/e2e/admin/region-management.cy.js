/**
 * Admin Region Management E2E Tests — Sprint 7
 */

const ADMIN_EMAIL = 'admin@todoke.test';
const ADMIN_PASSWORD = 'password123';

const validPolygon = JSON.stringify({
  type: 'Polygon',
  coordinates: [[
    [-46.64, -23.55],
    [-46.60, -23.55],
    [-46.60, -23.50],
    [-46.64, -23.50],
    [-46.64, -23.55]
  ]]
});

const mockRegions = [
  {
    id: 1, name: 'Zona Sul SP', status: 'active', partner_id: 1,
    partner: { name: 'Parceiro SP', email: 'sp@todoke.test' },
    polygon: { type: 'Polygon', coordinates: [[[-46.64, -23.55], [-46.60, -23.55], [-46.60, -23.50], [-46.64, -23.55]]] }
  }
];
const mockPartners = [{ id: 1, name: 'Parceiro SP', email: 'sp@todoke.test' }];

const loginAsAdmin = () => {
  cy.intercept('GET', '**/api/v1/admin/regions', { statusCode: 200, body: { regions: mockRegions } }).as('getRegions');
  cy.intercept('GET', '**/api/v1/admin/users*', { statusCode: 200, body: { users: mockPartners } }).as('getPartners');
  cy.visit('/login');
  cy.get('[data-cy="email-input"] input, [data-cy="email-input"]').type(ADMIN_EMAIL);
  cy.get('[data-cy="password-input"] input, [data-cy="password-input"]').type(ADMIN_PASSWORD);
  cy.get('[data-cy="login-button"], [data-cy="submit-btn"]').click();
  cy.url().should('include', '/admin');
};

describe('🗺️ Admin Region Management', () => {
  beforeEach(() => {
    loginAsAdmin();
    cy.visit('/admin/regions');
    cy.wait('@getRegions');
  });

  it('📋 Should display region list and map', () => {
    cy.get('[data-cy="regions-table"]').should('be.visible');
    cy.get('[data-cy="regions-map"]').should('exist');
    cy.get('[data-cy="create-region-btn"]').should('be.visible');
  });

  it('➕ Should create a new region', () => {
    cy.intercept('POST', '**/api/v1/admin/regions', {
      statusCode: 201, body: { id: 2, name: 'Nova Região', status: 'active' }
    }).as('createRegion');
    cy.intercept('GET', '**/api/v1/admin/regions', {
      statusCode: 200,
      body: { regions: [...mockRegions, { id: 2, name: 'Nova Região', status: 'active', partner_id: 1, partner: mockPartners[0], polygon: JSON.parse(validPolygon) }] }
    }).as('getRegionsRefresh');

    cy.get('[data-cy="create-region-btn"]').click();
    cy.get('[data-cy="region-name-input"] input').type('Nova Região');
    cy.get('[data-cy="region-coordinates-input"] textarea').clear().type(validPolygon, { delay: 0 });
    cy.get('[data-cy="save-region-btn"]').click();
    cy.wait('@createRegion');
    cy.wait('@getRegionsRefresh');
  });

  it('✏️ Should edit an existing region', () => {
    cy.intercept('PUT', '**/api/v1/admin/regions/1', {
      statusCode: 200, body: { id: 1, name: 'Zona Sul SP (Editada)', status: 'active' }
    }).as('updateRegion');
    cy.intercept('GET', '**/api/v1/admin/regions', {
      statusCode: 200, body: { regions: mockRegions }
    }).as('getRegionsRefresh');

    cy.get('[data-cy="edit-region-btn"]').first().click();
    cy.get('[data-cy="region-name-input"] input').clear().type('Zona Sul SP (Editada)');
    cy.get('[data-cy="save-region-btn"]').click();
    cy.wait('@updateRegion');
  });

  it('🗑️ Should delete a region after confirmation', () => {
    cy.intercept('DELETE', '**/api/v1/admin/regions/1', {
      statusCode: 200, body: { success: true }
    }).as('deleteRegion');
    cy.intercept('GET', '**/api/v1/admin/regions', {
      statusCode: 200, body: { regions: [] }
    }).as('getRegionsEmpty');

    cy.get('[data-cy="delete-region-btn"]').first().click();
    cy.get('[data-cy="confirm-delete-btn"]').click();
    cy.wait('@deleteRegion');
    cy.wait('@getRegionsEmpty');
  });

  it('⚠️ Should show validation error when region name is empty', () => {
    cy.get('[data-cy="create-region-btn"]').click();
    cy.get('[data-cy="save-region-btn"]').click();
    // Vuetify form validation shows inline error — form should not submit
    cy.get('@createRegion').should('not.exist');
  });
});
