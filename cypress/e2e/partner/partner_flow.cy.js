describe('🏢 Partner Flow', () => {
  beforeEach(() => {
    // Login as partner
    cy.visit('/login');
    cy.get('[data-cy="email-input"] input').type('partner@todoke.test');
    cy.get('[data-cy="password-input"] input').type('password123');
    cy.get('[data-cy="login-button"]').click();
    cy.url().should('include', '/partner/dashboard');
  });

  it('📊 Should display dashboard metrics', () => {
    cy.get('[data-cy="dashboard-metric"]').should('have.length.at.least', 4);
    cy.get('[data-cy="dashboard-metric"]').first().should('be.visible');
  });

  it('📋 Should manage orders', () => {
    cy.get('[data-cy="recent-orders-table"]').should('be.visible');
    
    // View an order
    cy.get('[data-cy="view-order-btn"]').first().click();
    cy.get('.v-dialog').should('be.visible');
    cy.get('.v-btn').contains('Close').click();
    
    // Accept an order (if any pending)
    cy.get('body').then(($body) => {
      if ($body.find('[data-cy="accept-order-btn"]').length > 0) {
        cy.get('[data-cy="accept-order-btn"]').first().click();
        cy.get('.v-snackbar').should('contain', 'accepted');
      }
    });
  });

  it('🍎 Should perform product CRUD', () => {
    cy.visit('/partner/products');
    
    // Create product
    cy.get('[data-cy="create-product-btn"]').click();
    cy.get('[data-cy="product-name-input"] input').type('Test Product');
    cy.get('[data-cy="product-price-input"] input').type('10.00');
    cy.get('[data-cy="product-category-select"]').click();
    cy.get('.v-list-item').contains('Pizza').click();
    cy.get('[data-cy="save-product-btn"]').click();
    cy.get('.v-snackbar').should('contain', 'created');
    
    // Edit product
    cy.get('[data-cy="edit-product-btn"]').last().click();
    cy.get('[data-cy="product-name-input"] input').clear().type('Updated Product');
    cy.get('[data-cy="save-product-btn"]').click();
    cy.get('.v-snackbar').should('contain', 'updated');
    
    // Delete product
    cy.get('[data-cy="delete-product-btn"]').last().click();
    cy.get('[data-cy="confirm-delete-btn"]').click();
    cy.get('.v-snackbar').should('contain', 'deleted');
  });

  it('➕ Should perform addon CRUD', () => {
    cy.visit('/partner/addons');
    cy.get('[data-cy="create-addon-btn"]').click();
    cy.get('[data-cy="addon-name-input"] input').type('Test Addon');
    cy.get('[data-cy="addon-price-input"] input').type('2.00');
    cy.get('[data-cy="save-addon-btn"]').click();
    cy.get('.v-snackbar').should('contain', 'created');
  });

  it('📍 Should manage regions and nodes', () => {
    cy.visit('/partner/regions');
    cy.get('[data-cy="regions-table"]').should('be.visible');
    
    cy.visit('/partner/nodes');
    cy.get('[data-cy="nodes-table"]').should('be.visible');
    cy.get('[data-cy="create-node-btn"]').click();
    cy.get('[data-cy="node-name-input"] input').type('Test Node');
    cy.get('[data-cy="save-node-btn"]').click();
  });
});
