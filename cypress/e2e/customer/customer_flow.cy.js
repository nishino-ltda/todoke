describe('Customer Flow', () => {
  beforeEach(() => {
    cy.visit('/login');
    cy.get('[data-cy="email-input"] input').clear().type('customer@todoke.test');
    cy.get('[data-cy="password-input"] input').clear().type('password123');
    cy.get('[data-cy="login-button"]').click();
    cy.url().should('include', '/customer/dashboard');
  });

  it('should see discovery hub on dashboard', () => {
    cy.get('[data-cy="customer-discovery-map"]').should('be.visible');
    cy.get('[data-cy="category-quick-nav"]').should('be.visible');
    cy.get('[data-testid="user-welcome"]').should('contain', 'Olá');
  });

  it('should browse menu and filter by category', () => {
    cy.visit('/customer/menu');
    cy.get('[data-cy="customer-menu"]').should('be.visible');

    // Test search
    cy.get('[data-cy="product-search"]').type('Pizza');
    cy.get('[data-cy="product-card"]').should('have.length.at.least', 1);
    cy.get('[data-cy="product-name"]').first().should('contain', 'Pizza');
  });

  it('should perform cart operations with premium modal', () => {
    cy.visit('/customer/menu');

    // Open premium modal
    cy.get('[data-cy="product-card"]').first().click();
    cy.get('.v-dialog').should('be.visible');
    
    // Add to cart
    cy.get('[data-cy="add-to-cart"]').click();
    
    // Verify success snackbar
    cy.get('.v-snackbar').should('be.visible').and('contain', 'Item adicionado');

    // Check cart icon in header
    cy.get('[data-cy="cart-icon"]').should('contain', '1');
  });

  it('should verify orders page and reorder functionality', () => {
    cy.visit('/customer/orders');
    cy.get('[data-cy="customer-orders"]').should('be.visible');
    
    // Switch to history
    cy.get('.v-tab').contains('Histórico').click();
    
    // Click reorder
    cy.get('button').contains('Repetir').first().click();
    
    // Verify snackbar and cart update
    cy.get('.v-snackbar').should('be.visible').and('contain', 'adicionados ao carrinho');
    cy.get('[data-cy="cart-icon"]').should('not.contain', '0');
  });

  it('should switch language and see translated titles', () => {
    cy.visit('/customer/menu');

    cy.get('[data-cy="language-selector"]').click();
    cy.get('.v-list-item').contains('English').click();
    cy.get('h1').should('contain', 'Explore Menu');

    cy.get('[data-cy="language-selector"]').click();
    cy.get('.v-list-item').contains('Português').click();
    cy.get('h1').should('contain', 'Explorar Cardápio');
  });
});
