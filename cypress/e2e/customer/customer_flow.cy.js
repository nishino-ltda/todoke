describe('Customer Flow', () => {
  beforeEach(() => {
    cy.visit('/login');
    cy.get('[data-cy="email-input"] input').type('customer@todoke.test');
    cy.get('[data-cy="password-input"] input').type('password123');
    cy.get('[data-cy="login-button"]').click();
    cy.url().should('include', '/customer/dashboard');
  });

  it('should browse menu and search products', () => {
    cy.visit('/customer/menu');
    cy.get('[data-cy="product-list-container"]').should('be.visible');

    cy.get('[data-cy="product-search"]').type('Pizza');
    cy.get('[data-cy="product-card"]').should('have.length.at.least', 1);
    cy.get('[data-cy="product-name"]').first().should('contain', 'Pizza');
  });

  it('should perform cart operations', () => {
    cy.visit('/customer/menu');

    cy.get('[data-cy="product-card"]').first().click();
    cy.get('[data-cy="add-to-cart"]').click();

    cy.get('[data-cy="cart-icon"]').should('contain', '1');

    cy.get('[data-cy="cart-icon"]').click();
    cy.get('.v-dialog').should('be.visible');
    cy.get('.v-list-item-title').should('be.visible');

    cy.get('[data-cy="remove-item"]').click();
    cy.get('[data-cy="cart-icon"]').should('not.contain', '1');
  });

  it('should complete checkout flow', () => {
    cy.visit('/customer/menu');

    cy.get('[data-cy="product-card"]').first().click();
    cy.get('[data-cy="add-to-cart"]').click();
    cy.get('[data-cy="cart-icon"]').click();
    cy.get('[data-cy="checkout-button"]').click();

    cy.url().should('include', '/checkout');

    cy.get('[data-cy="submit-order"]').should('be.visible');
  });

  it('should switch language during order flow', () => {
    cy.visit('/customer/menu');

    cy.get('[data-cy="language-selector"]').click();
    cy.get('.v-list-item').contains('English').click();

    cy.get('h1').should('contain', 'Menu');

    cy.get('[data-cy="language-selector"]').click();
    cy.get('.v-list-item').contains('Português').click();
  });
});
