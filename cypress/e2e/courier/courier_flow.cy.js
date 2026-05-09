describe('🏍️ Courier Flow', () => {
  beforeEach(() => {
    // Login as courier
    cy.visit('/login');
    cy.get('[data-cy="email-input"] input').type('courier@todoke.test');
    cy.get('[data-cy="password-input"] input').type('password123');
    cy.get('[data-cy="login-button"]').click();
    cy.url().should('include', '/courier/dashboard');
  });

  it('🔘 Should toggle availability', () => {
    cy.get('[data-cy="availability-toggle"]').should('be.checked');
    cy.get('[data-cy="availability-toggle"]').click({ force: true });
    cy.get('[data-cy="availability-toggle"]').should('not.be.checked');
    cy.contains('Go Online').should('be.visible');
  });

  it('📦 Should manage available deliveries', () => {
    // Ensure online
    cy.get('body').then(($body) => {
      if (!$body.find('[data-cy="availability-toggle"]').is(':checked')) {
        cy.get('[data-cy="availability-toggle"]').click({ force: true });
      }
    });

    // Accept delivery
    cy.get('[data-cy="accept-delivery-btn"]').first().click();
    cy.get('.v-snackbar').should('contain', 'accepted');
    
    // Verify active delivery view
    cy.get('.active-delivery').should('be.visible');
    cy.get('.delivery-map').should('be.visible');
  });

  it('🔄 Should update delivery status', () => {
    // Assuming there's an active delivery from previous test or seeded
    // If not, we accept one first
    cy.get('body').then(($body) => {
      if ($body.find('[data-cy="accept-delivery-btn"]').length > 0) {
        cy.get('[data-cy="accept-delivery-btn"]').first().click();
      }
    });

    cy.get('[data-cy="update-status-btn"]').should('be.visible').click();
    cy.get('.v-snackbar').should('contain', 'updated');
  });

  it('❌ Should reject available delivery', () => {
    cy.get('[data-cy="reject-delivery-btn"]').first().click();
    cy.get('.v-snackbar').should('contain', 'rejected');
  });
});
