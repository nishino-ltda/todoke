describe('Customer Ordering Flow', () => {
  beforeEach(() => {
    cy.visit('/login');
    cy.get('[data-cy="email-input"] input').clear().type('customer@todoke.test');
    cy.get('[data-cy="password-input"] input').clear().type('password123');
    cy.get('[data-cy="login-button"]').click();
    cy.url().should('include', '/customer/dashboard');
  });

  it('should allow adding products with addons to the cart via premium modal', () => {
    cy.visit('/customer/menu');
    cy.get('[data-cy="product-card"]').first().click();
    
    // Check if modal is visible
    cy.get('.v-dialog').should('be.visible');
    
    // Select an addon if exists
    cy.get('body').then(($body) => {
        if ($body.find('[data-cy^="addon-checkbox-"]').length > 0) {
            cy.get('[data-cy^="addon-checkbox-"]').first().click();
        }
    });

    cy.get('[data-cy="add-to-cart"]').click();
    cy.get('[data-cy="cart-icon"]').should('contain', '1');
    cy.get('.v-snackbar').should('be.visible');
  });

  it('should allow viewing cart items and navigating to checkout', () => {
    cy.visit('/customer/menu');

    cy.get('[data-cy="product-card"]').first().click();
    cy.get('[data-cy="add-to-cart"]').click();

    cy.get('[data-cy="cart-icon"]').click();
    cy.get('[data-cy="cart-component"]').should('be.visible');
    
    cy.get('[data-cy="checkout-button"]').click();
    cy.url().should('include', '/customer/checkout');
  });

  it('should display order summary on checkout page', () => {
    cy.visit('/customer/menu');
    cy.get('[data-cy="product-card"]').first().click();
    cy.get('[data-cy="add-to-cart"]').click();

    cy.visit('/customer/checkout');
    cy.get('[data-cy="customer-checkout"]').should('be.visible');
    cy.get('[data-cy="checkout-order-summary"]').should('be.visible');
  });
});
