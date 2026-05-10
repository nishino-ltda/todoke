/**
 * Courier Flow E2E Tests — Sprint 6
 * Uses cy.intercept() to mock all delivery API calls.
 * Default locale: pt-BR
 */

const COURIER_EMAIL = 'courier@todoke.test';
const COURIER_PASSWORD = 'password123';

// ── Fixture data ────────────────────────────────────────────────────────────

const mockAvailableDeliveries = [
  {
    id: 101,
    status: 'pending',
    fee: 18.50,
    value: 18.50,
    distance: '3.2',
    restaurant_name: 'Pizza Palace',
    origin_address: 'Rua das Flores, 100',
    destination_address: 'Av. Paulista, 500',
    origin_lat: -23.548,
    origin_lng: -46.638,
    destination_lat: -23.561,
    destination_lng: -46.655,
  },
  {
    id: 102,
    status: 'pending',
    fee: 12.00,
    value: 12.00,
    distance: '1.8',
    restaurant_name: 'Sushi House',
    origin_address: 'Rua Augusta, 200',
    destination_address: 'Rua Oscar Freire, 300',
    origin_lat: -23.551,
    origin_lng: -46.651,
    destination_lat: -23.556,
    destination_lng: -46.663,
  },
];

const mockAcceptedDelivery = {
  ...mockAvailableDeliveries[0],
  id: 101,
  status: 'accepted',
};

const mockUpdatedDelivery = {
  ...mockAcceptedDelivery,
  status: 'collected',
};

// ── Helpers ──────────────────────────────────────────────────────────────────

const interceptDeliveries = (deliveries = mockAvailableDeliveries, alias = 'getDeliveries') => {
  cy.intercept('GET', '**/api/v1/deliveries*', {
    statusCode: 200,
    body: { deliveries, total: deliveries.length, per_page: 15, current_page: 1, last_page: 1 },
  }).as(alias);
};

const interceptAccept = (deliveryId = 101, response = mockAcceptedDelivery) => {
  cy.intercept('PATCH', `**/api/v1/deliveries/${deliveryId}/accept`, {
    statusCode: 200,
    body: response,
  }).as('acceptDelivery');
};

const interceptStatusUpdate = (deliveryId = 101, newStatus = 'collected') => {
  cy.intercept('PATCH', `**/api/v1/deliveries/${deliveryId}/status`, {
    statusCode: 200,
    body: { id: deliveryId, status: newStatus, message: 'Delivery status updated successfully' },
  }).as('updateStatus');
};

// ── Test Suite ───────────────────────────────────────────────────────────────

describe('Courier Flow E2E Tests', () => {
  beforeEach(() => {
    // Mock the initial deliveries fetch on every test
    interceptDeliveries();

    // Login as courier
    cy.visit('/login');
    cy.get('[data-cy="email-input"] input, [data-cy="email-input"]').type(COURIER_EMAIL);
    cy.get('[data-cy="password-input"] input, [data-cy="password-input"]').type(COURIER_PASSWORD);
    cy.get('[data-cy="login-button"], [data-cy="submit-btn"]').click();
  });

  // ── 1. Login ───────────────────────────────────────────────────────────────
  it('should allow logging in as a courier and land on dashboard', () => {
    cy.url().should('include', '/courier');
    cy.get('[data-cy="courier-dashboard"]').should('exist');
    cy.get('[data-cy="availability-toggle"]').should('exist');
  });

  // ── 2. Availability Toggle ────────────────────────────────────────────────
  it('should allow toggling availability status', () => {
    cy.url().should('include', '/courier');

    // Initially online — available deliveries section visible
    cy.get('[data-cy="available-deliveries-section"]').should('exist');
    cy.get('[data-cy="offline-state"]').should('not.exist');

    // Toggle offline
    cy.get('[data-cy="availability-toggle"]').click({ force: true });
    cy.get('[data-cy="offline-state"]').should('be.visible');
    cy.get('[data-cy="available-deliveries-section"]').should('not.exist');

    // Toggle back online
    interceptDeliveries(mockAvailableDeliveries, 'getDeliveriesOnline');
    cy.get('[data-cy="availability-toggle"]').click({ force: true });
    cy.get('[data-cy="offline-state"]').should('not.exist');
    cy.get('[data-cy="available-deliveries-section"]').should('exist');
  });

  // ── 3. View Available Deliveries ──────────────────────────────────────────
  it('should display available delivery requests with details', () => {
    cy.url().should('include', '/courier');

    cy.wait('@getDeliveries');

    // Both delivery cards should render
    cy.get('[data-cy="available-deliveries-section"]').should('be.visible');
    cy.get('[data-cy="delivery-card-101"]').should('exist');
    cy.get('[data-cy="delivery-card-102"]').should('exist');

    // First card should show fee and restaurant
    cy.get('[data-cy="delivery-card-101"]').within(() => {
      cy.get('[data-cy="delivery-fee"]').should('contain.text', '18');
      cy.get('[data-cy="delivery-distance"]').should('contain.text', '3.2');
      cy.get('[data-cy="delivery-restaurant"]').should('contain.text', 'Pizza Palace');
      cy.get('[data-cy="accept-delivery-btn"]').should('be.visible');
      cy.get('[data-cy="reject-delivery-btn"]').should('be.visible');
    });
  });

  // ── 4. Accept / Reject Delivery ───────────────────────────────────────────
  it('should accept a delivery and display the active delivery panel', () => {
    cy.url().should('include', '/courier');
    cy.wait('@getDeliveries');

    interceptAccept(101);

    // Click accept on the first delivery
    cy.get('[data-cy="delivery-card-101"]').within(() => {
      cy.get('[data-cy="accept-delivery-btn"]').click();
    });

    cy.wait('@acceptDelivery');

    // Active delivery section should appear
    cy.get('[data-cy="active-delivery-section"]').should('be.visible');
    cy.get('[data-cy="update-status-btn"]').should('be.visible');

    // Success notification (Vuetify snackbar or app notification)
    cy.get('body').should('contain.text', 'aceita');
  });

  it('should reject a delivery and remove it from the list', () => {
    cy.url().should('include', '/courier');
    cy.wait('@getDeliveries');

    cy.get('[data-cy="delivery-card-101"]').should('exist');

    cy.get('[data-cy="delivery-card-101"]').within(() => {
      cy.get('[data-cy="reject-delivery-btn"]').click();
    });

    // Card should be removed from DOM
    cy.get('[data-cy="delivery-card-101"]').should('not.exist');
    // Second delivery still visible
    cy.get('[data-cy="delivery-card-102"]').should('exist');
  });

  // ── 5. Update Delivery Status ─────────────────────────────────────────────
  it('should update delivery status through the workflow', () => {
    cy.url().should('include', '/courier');
    cy.wait('@getDeliveries');

    // Accept a delivery first
    interceptAccept(101);
    cy.get('[data-cy="delivery-card-101"]').within(() => {
      cy.get('[data-cy="accept-delivery-btn"]').click();
    });
    cy.wait('@acceptDelivery');

    // Active delivery panel visible
    cy.get('[data-cy="active-delivery-section"]').should('be.visible');
    cy.get('[data-cy="update-status-btn"]').should('be.visible');

    // Intercept status update → collected
    interceptStatusUpdate(101, 'collected');
    cy.get('[data-cy="update-status-btn"]').click();
    cy.wait('@updateStatus');

    // Success message visible
    cy.get('body').should('contain.text', 'atualizado');

    // Status chip should reflect new status
    cy.get('[data-cy="active-delivery-status"]').should('contain.text', 'collected');
  });

  // ── 6. Error States ───────────────────────────────────────────────────────
  it('should show error state when API fails to load deliveries', () => {
    // Override the initial intercept with a failure
    cy.intercept('GET', '**/api/v1/deliveries*', {
      statusCode: 500,
      body: { message: 'Internal Server Error' },
    }).as('failedDeliveries');

    cy.visit('/courier/dashboard');
    cy.wait('@failedDeliveries');

    cy.get('[data-cy="deliveries-error"]').should('be.visible');
  });

  it('should show error notification when accept API fails', () => {
    cy.url().should('include', '/courier');
    cy.wait('@getDeliveries');

    // Intercept accept with failure
    cy.intercept('PATCH', '**/api/v1/deliveries/101/accept', {
      statusCode: 409,
      body: { message: 'Delivery already accepted by another courier' },
    }).as('acceptFailed');

    cy.get('[data-cy="delivery-card-101"]').within(() => {
      cy.get('[data-cy="accept-delivery-btn"]').click();
    });
    cy.wait('@acceptFailed');

    // Error notification
    cy.get('body').should('contain.text', 'Falha');
  });
});
