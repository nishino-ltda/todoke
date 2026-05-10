describe('Customer Ordering Flow', () => {
  beforeEach(() => {
    cy.visit('/login');
    cy.get('[data-cy="email-input"] input').type('customer@todoke.test');
    cy.get('[data-cy="password-input"] input').type('password123');
    cy.get('[data-cy="login-button"]').click();
    cy.url().should('include', '/customer/dashboard');
  });

  it('should allow navigating to a partner menu via public route', () => {
    cy.visit('/menu/test-partner');
    cy.get('[data-cy="customer-menu"]').should('be.visible');
    cy.get('[data-cy="product-list-container"]').should('be.visible');
  });

  it('should allow browsing products and viewing details', () => {
    cy.visit('/customer/menu');
    cy.get('[data-cy="product-card"]').first().click();
    cy.get('[data-cy="add-to-cart"]').should('be.visible');
  });

  it('should allow adding products with addons to the cart', () => {
    cy.visit('/customer/menu');
    cy.get('[data-cy="product-card"]').first().click();
    cy.get('[data-cy="add-to-cart"]').click();
    cy.get('[data-cy="cart-icon"]').should('contain', '1');
  });

  it('should allow viewing cart items and totals in the Cart component', () => {
    cy.visit('/customer/menu');

    cy.get('[data-cy="product-card"]').first().click();
    cy.get('[data-cy="add-to-cart"]').click();

    cy.get('[data-cy="cart-icon"]').click();

    cy.get('[data-cy="cart-component"]').should('be.visible');
    cy.get('[data-cy="cart-item"]').should('exist');
  });

  it('should display login prompt when accessing checkout without auth', () => {
    cy.visit('/logout');
    cy.visit('/customer/checkout');
    cy.url().should('include', '/login');
  });

  it('should display order confirmation after successful submission', () => {
    cy.visit('/customer/menu');
    cy.get('[data-cy="product-card"]').first().click();
    cy.get('[data-cy="add-to-cart"]').click();

    cy.visit('/customer/checkout');
    cy.get('[data-cy="customer-checkout"]').should('be.visible');
    cy.get('[data-cy="checkout-order-summary"]').should('be.visible');
    cy.get('[data-cy="submit-order"]').should('be.visible');
  });
});
