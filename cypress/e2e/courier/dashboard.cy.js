describe('🛵 Courier Dashboard', () => {
  beforeEach(() => {
    cy.log('🔑 Logging in as courier');
    cy.loginAsCourier();
  });

  it('🔄 Should toggle availability status', () => {
    cy.visit('/courier/dashboard');
    cy.get('[data-cy="status-card"]').should('contain', 'Online');
    
    cy.get('[data-cy="availability-toggle"]').click();
    cy.get('[data-cy="status-card"]').should('contain', 'Offline');
    
    // Toggle back
    cy.get('[data-cy="availability-toggle"]').click();
    cy.get('[data-cy="status-card"]').should('contain', 'Online');
  });

  it('📦 Should display available delivery requests', () => {
    // Intercept available deliveries API
    cy.intercept('GET', '/api/v1/deliveries/available*', {
      statusCode: 200,
      body: [
        {
          id: 101,
          value: 15.50,
          estimated_time: 20,
          type: 'standard',
          status: 'pending',
          logistics_partner: { name: 'Pizza Palace' },
          origin: { address: 'Origin Address' },
          destination: { address: 'Dest Address' }
        }
      ]
    }).as('getAvailable');

    cy.visit('/courier/dashboard');
    cy.wait('@getAvailable');
    
    cy.get('[data-cy="delivery-card"]').should('have.length', 1);
    cy.get('[data-cy="delivery-card"]').should('contain', 'R$ 15,50');
    cy.get('[data-cy="delivery-card"]').should('contain', 'Pizza Palace');
  });

  it('✅ Should be able to accept/reject deliveries', () => {
    cy.intercept('GET', '/api/v1/deliveries/available*', {
      statusCode: 200,
      body: [{ id: 101, value: 15.50, status: 'pending' }]
    }).as('getAvailable');

    cy.intercept('PATCH', '/api/v1/deliveries/101/accept', {
      statusCode: 200,
      body: { id: 101, status: 'accepted' }
    }).as('acceptDelivery');

    cy.visit('/courier/dashboard');
    cy.wait('@getAvailable');
    
    cy.get('[data-cy="accept-delivery-btn"]').click();
    cy.wait('@acceptDelivery');
    
    cy.get('[data-cy="active-delivery-status"]').should('contain', 'Accepted');
  });

  it('📍 Should update delivery status', () => {
    // Mock active delivery
    cy.intercept('GET', '/api/v1/deliveries/available*', {
      statusCode: 200,
      body: [
        {
          id: 101,
          status: 'accepted',
          value: 15.50,
          origin: { address: 'A' },
          destination: { address: 'B' }
        }
      ]
    }).as('getAvailable');

    cy.intercept('PATCH', '/api/v1/deliveries/101/status', {
      statusCode: 200,
      body: { id: 101, status: 'collected' }
    }).as('updateStatus');

    cy.visit('/courier/dashboard');
    cy.wait('@getAvailable');
    
    cy.get('[data-cy="update-status-btn"]').click();
    cy.wait('@updateStatus');
    
    cy.get('[data-cy="active-delivery-status"]').should('contain', 'Arrived'); // Label is Arrived but status is collected
  });
});
