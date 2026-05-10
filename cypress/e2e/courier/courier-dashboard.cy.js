/**
 * Courier Dashboard E2E Tests — Sprint 6
 * Covers: performance display, availability management,
 * offline handling, edge cases, and mobile responsiveness.
 */

const COURIER_EMAIL = 'courier@todoke.test';
const COURIER_PASSWORD = 'password123';

const mockDeliveries = [
  {
    id: 201,
    status: 'pending',
    fee: 22.00,
    value: 22.00,
    distance: '4.1',
    restaurant_name: 'Burger Bros',
    origin_address: 'Rua das Orquídeas, 10',
    destination_address: 'Av. Brasil, 1000',
    origin_lat: -23.55,
    origin_lng: -46.64,
    destination_lat: -23.57,
    destination_lng: -46.66,
  },
];

const loginAsCourier = () => {
  cy.visit('/login');
  cy.get('[data-cy="email-input"] input, [data-cy="email-input"]').type(COURIER_EMAIL);
  cy.get('[data-cy="password-input"] input, [data-cy="password-input"]').type(COURIER_PASSWORD);
  cy.get('[data-cy="login-button"], [data-cy="submit-btn"]').click();
};

describe('🛵 Courier Dashboard', () => {
  beforeEach(() => {
    cy.intercept('GET', '**/api/v1/deliveries*', {
      statusCode: 200,
      body: {
        deliveries: mockDeliveries,
        total: mockDeliveries.length,
        per_page: 15,
        current_page: 1,
        last_page: 1,
      },
    }).as('getDeliveries');

    loginAsCourier();
    cy.url().should('include', '/courier');
  });

  // ── 1. Status Card & Dashboard Render ─────────────────────────────────────
  it('📊 Should display the status card and courier dashboard', () => {
    cy.get('[data-cy="status-card"]').should('be.visible');
    cy.get('[data-cy="availability-toggle"]').should('exist');
    cy.get('[data-cy="courier-dashboard"]').should('exist');
  });

  // ── 2. Availability Management ─────────────────────────────────────────────
  it('📅 Should manage availability and affect delivery list visibility', () => {
    cy.wait('@getDeliveries');

    // Online: delivery requests visible
    cy.get('[data-cy="available-deliveries-section"]').should('be.visible');

    // Go offline
    cy.get('[data-cy="availability-toggle"]').click({ force: true });
    cy.get('[data-cy="offline-state"]').should('be.visible');
    cy.get('[data-cy="available-deliveries-section"]').should('not.exist');

    // Re-intercept for when toggling back online
    cy.intercept('GET', '**/api/v1/deliveries*', {
      statusCode: 200,
      body: {
        deliveries: mockDeliveries,
        total: 1,
        per_page: 15,
        current_page: 1,
        last_page: 1,
      },
    }).as('getDeliveriesAgain');

    // Come back online
    cy.get('[data-cy="availability-toggle"]').click({ force: true });
    cy.wait('@getDeliveriesAgain');
    cy.get('[data-cy="available-deliveries-section"]').should('be.visible');
    cy.get('[data-cy="offline-state"]').should('not.exist');
  });

  // ── 3. Offline / Empty State ───────────────────────────────────────────────
  it('📱 Should show offline state and empty delivery message', () => {
    cy.wait('@getDeliveries');

    // Override with empty list
    cy.intercept('GET', '**/api/v1/deliveries*', {
      statusCode: 200,
      body: { deliveries: [], total: 0, per_page: 15, current_page: 1, last_page: 1 },
    }).as('emptyDeliveries');

    // Toggle offline then back to trigger re-fetch
    cy.get('[data-cy="availability-toggle"]').click({ force: true });
    cy.get('[data-cy="availability-toggle"]').click({ force: true });
    cy.wait('@emptyDeliveries');

    // Empty state message
    cy.get('[data-cy="no-deliveries-empty"]').should('be.visible');
  });

  // ── 4. Edge Cases — API Error ─────────────────────────────────────────────
  it('⚠️ Should show error alert when deliveries fail to load', () => {
    cy.intercept('GET', '**/api/v1/deliveries*', {
      statusCode: 503,
      body: { message: 'Service Unavailable' },
    }).as('errorDeliveries');

    // Navigate directly to dashboard to trigger the error
    cy.visit('/courier/dashboard');
    cy.wait('@errorDeliveries');

    cy.get('[data-cy="deliveries-error"]').should('be.visible');
  });

  it('⚠️ Should show error notification when status update fails', () => {
    cy.wait('@getDeliveries');

    // Accept a delivery first
    cy.intercept('PATCH', '**/api/v1/deliveries/201/accept', {
      statusCode: 200,
      body: { id: 201, status: 'accepted' },
    }).as('accept');

    cy.get('[data-cy="delivery-card-201"]').within(() => {
      cy.get('[data-cy="accept-delivery-btn"]').click();
    });
    cy.wait('@accept');

    // Status update fails
    cy.intercept('PATCH', '**/api/v1/deliveries/201/status', {
      statusCode: 400,
      body: { message: 'Invalid status transition' },
    }).as('failedStatus');

    cy.get('[data-cy="update-status-btn"]').click();
    cy.wait('@failedStatus');

    // Error notification visible
    cy.get('body').should('contain.text', 'Falha');
  });

  // ── 5. Mobile Responsiveness ───────────────────────────────────────────────
  it('📱 Should be usable on mobile viewport', () => {
    cy.viewport(375, 812); // iPhone X
    cy.wait('@getDeliveries');

    // Dashboard renders without horizontal scroll
    cy.get('[data-cy="courier-dashboard"]').should('be.visible');
    cy.get('[data-cy="status-card"]').should('be.visible');
    cy.get('[data-cy="availability-toggle"]').should('exist');

    // Accept/reject buttons should be present and not overflow
    cy.get('[data-cy="accept-delivery-btn"]').should('be.visible');
    cy.get('[data-cy="reject-delivery-btn"]').should('be.visible');

    // Check no horizontal overflow
    cy.document().then((doc) => {
      const body = doc.body;
      expect(body.scrollWidth).to.be.lte(375 + 5); // allow 5px tolerance
    });
  });
});
