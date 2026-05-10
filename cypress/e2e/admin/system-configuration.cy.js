/**
 * Admin System Configuration E2E Tests — Sprint 7
 */

const ADMIN_EMAIL = 'admin@todoke.test';
const ADMIN_PASSWORD = 'password123';

const mockSettings = {
  site_name: 'TODOKE',
  support_email: 'suporte@todoke.test',
  contact_phone: '+55 11 99999-0000',
  maintenance_mode: false,
  allow_registration: true,
  delivery_fee_base: 5.00,
  delivery_fee_per_km: 1.50
};

const loginAsAdmin = () => {
  cy.intercept('GET', '**/api/v1/admin/settings', {
    statusCode: 200, body: mockSettings
  }).as('getSettings');
  cy.visit('/login');
  cy.get('[data-cy="email-input"] input, [data-cy="email-input"]').type(ADMIN_EMAIL);
  cy.get('[data-cy="password-input"] input, [data-cy="password-input"]').type(ADMIN_PASSWORD);
  cy.get('[data-cy="login-button"], [data-cy="submit-btn"]').click();
  cy.url().should('include', '/admin');
};

describe('⚙️ Admin System Configuration', () => {
  beforeEach(() => {
    loginAsAdmin();
    cy.visit('/admin/settings');
    cy.wait('@getSettings');
  });

  it('📋 Should display all configuration settings loaded from API', () => {
    cy.get('[data-cy="setting-site-name"] input').should('have.value', 'TODOKE');
    cy.get('[data-cy="setting-support-email"] input').should('have.value', 'suporte@todoke.test');
    cy.get('[data-cy="setting-base-fee"] input').should('have.value', '5');
    cy.get('[data-cy="setting-km-fee"] input').should('have.value', '1.5');
    cy.get('[data-cy="save-settings-btn"]').should('be.visible');
  });

  it('💾 Should save updated settings successfully', () => {
    cy.intercept('PATCH', '**/api/v1/admin/settings', {
      statusCode: 200, body: { success: true, ...mockSettings, site_name: 'Todoke Premium' }
    }).as('saveSettings');

    cy.get('[data-cy="setting-site-name"] input').clear().type('Todoke Premium');
    cy.get('[data-cy="save-settings-btn"]').click();
    cy.wait('@saveSettings');
    cy.get('body').should('contain.text', 'atualiz');
  });

  it('🔴 Should toggle maintenance mode', () => {
    cy.intercept('PATCH', '**/api/v1/admin/settings', {
      statusCode: 200, body: { success: true, ...mockSettings, maintenance_mode: true }
    }).as('saveSettings');

    cy.get('[data-cy="setting-maintenance-mode"]').click({ force: true });
    cy.get('[data-cy="save-settings-btn"]').click();
    cy.wait('@saveSettings');
  });

  it('💲 Should update delivery fees', () => {
    cy.intercept('PATCH', '**/api/v1/admin/settings', {
      statusCode: 200, body: { success: true }
    }).as('saveFees');

    cy.get('[data-cy="setting-base-fee"] input').clear().type('7.50');
    cy.get('[data-cy="setting-km-fee"] input').clear().type('2.00');
    cy.get('[data-cy="save-settings-btn"]').click();
    cy.wait('@saveFees');
  });

  it('⚠️ Should show error notification when save fails', () => {
    cy.intercept('PATCH', '**/api/v1/admin/settings', {
      statusCode: 500, body: { message: 'Server error' }
    }).as('saveFail');

    cy.get('[data-cy="save-settings-btn"]').click();
    cy.wait('@saveFail');
    cy.get('body').should('contain.text', 'Falha');
  });
});
