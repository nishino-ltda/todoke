describe('🛒 Customer Flow', () => {
  beforeEach(() => {
    // Login as customer
    cy.visit('/login');
    cy.get('[data-cy="email-input"] input').type('customer@todoke.test');
    cy.get('[data-cy="password-input"] input').type('password123');
    cy.get('[data-cy="login-button"]').click();
    cy.url().should('include', '/customer/dashboard');
  });

  it('🍔 Should browse menu and search products', () => {
    cy.visit('/customer/menu');
    cy.get('[data-cy="product-list-container"]').should('be.visible');
    
    // Search for Pizza
    cy.get('[data-cy="product-search"]').type('Pizza');
    cy.get('[data-cy="product-card"]').should('have.length.at.least', 1);
    cy.get('[data-cy="product-name"]').first().should('contain', 'Pizza');
  });

  it('🛒 Should perform cart operations', () => {
    cy.visit('/customer/menu');
    
    // Add item to cart
    cy.get('[data-cy="product-card"]').first().click();
    cy.get('[data-cy="add-to-cart"]').click();
    
    // Verify cart icon update
    cy.get('[data-cy="cart-icon"]').should('contain', '1');
    
    // Open cart and verify item
    cy.get('[data-cy="cart-icon"]').click();
    cy.get('.v-dialog').should('be.visible');
    cy.get('.v-list-item-title').should('be.visible');
    
    // Remove item
    cy.get('[data-cy="remove-item"]').click();
    cy.get('[data-cy="cart-icon"]').should('not.contain', '1');
  });

  it('💳 Should complete checkout flow', () => {
    cy.visit('/customer/menu');
    
    // Add item and go to checkout
    cy.get('[data-cy="product-card"]').first().click();
    cy.get('[data-cy="add-to-cart"]').click();
    cy.get('[data-cy="cart-icon"]').click();
    cy.get('[data-cy="checkout-button"]').click();
    
    cy.url().should('include', '/checkout');
    
    // Fill checkout details (Assuming these fields exist or will be added)
    cy.get('[data-cy="address-input"]').type('123 Test St');
    cy.get('[data-cy="payment-method-select"]').click();
    cy.get('.v-list-item').contains('Credit Card').click();
    
    // Confirm order
    cy.get('[data-cy="place-order-button"]').click();
    
    // Success redirect
    cy.url().should('include', '/order-confirmation');
    cy.get('[data-cy="order-success-message"]').should('be.visible');
  });

  it('🌐 Should switch language during order flow', () => {
    cy.visit('/customer/menu');
    
    // Switch to English
    cy.get('[data-cy="language-selector"]').click();
    cy.get('.v-list-item').contains('English').click();
    
    cy.get('h1').should('contain', 'Menu'); // Assuming translated title
    
    // Switch back to Portuguese
    cy.get('[data-cy="language-selector"]').click();
    cy.get('.v-list-item').contains('Português').click();
    
    cy.get('h1').should('contain', 'Cardápio'); // Assuming translated title
  });
});
