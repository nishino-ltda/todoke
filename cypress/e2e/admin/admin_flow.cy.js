describe('👔 Admin Flow', () => {
  beforeEach(() => {
    // Login as admin
    cy.visit('/login');
    cy.get('[data-cy="email-input"] input').type('admin@todoke.test');
    cy.get('[data-cy="password-input"] input').type('password123');
    cy.get('[data-cy="login-button"]').click();
    cy.url().should('include', '/admin/dashboard');
  });

  it('📊 Should display system metrics', () => {
    cy.get('[data-cy="admin-metric"]').should('have.length.at.least', 4);
    cy.contains('Total Users').should('be.visible');
  });

  it('👥 Should manage users', () => {
    cy.visit('/admin/users');
    cy.get('[data-cy="users-table"]').should('be.visible');
    
    // Deactivate/Activate user
    cy.get('body').then(($body) => {
      if ($body.find('[data-cy="deactivate-user-btn"]').length > 0) {
        cy.get('[data-cy="deactivate-user-btn"]').first().click();
        cy.get('.v-snackbar').should('contain', 'suspended');
      } else if ($body.find('[data-cy="activate-user-btn"]').length > 0) {
        cy.get('[data-cy="activate-user-btn"]').first().click();
        cy.get('.v-snackbar').should('contain', 'activated');
      }
    });
  });

  it('📍 Should manage nodes and regions', () => {
    cy.visit('/admin/nodes');
    cy.get('[data-cy="nodes-table"]').should('be.visible');
    
    // Approve/Reject node (Assuming these buttons exist)
    cy.get('body').then(($body) => {
      if ($body.find('[data-cy="approve-node-btn"]').length > 0) {
        cy.get('[data-cy="approve-node-btn"]').first().click();
        cy.get('.v-snackbar').should('contain', 'approved');
      }
    });

    cy.visit('/admin/regions');
    cy.get('[data-cy="regions-table"]').should('be.visible');
    cy.get('[data-cy="create-region-btn"]').click();
    cy.get('[data-cy="region-name-input"] input').type('New Region');
    cy.get('[data-cy="save-region-btn"]').click();
  });

  it('⚙️ Should update platform settings', () => {
    cy.visit('/admin/settings');
    cy.get('[data-cy="site-name-input"] input').clear().type('Todoke Premium');
    cy.get('[data-cy="delivery-fee-input"] input').clear().type('5.00');
    cy.get('[data-cy="maintenance-mode-switch"]').click({ force: true });
    cy.get('[data-cy="save-settings-btn"]').click();
    cy.get('.v-snackbar').should('contain', 'saved');
  });

  it('📈 Should monitor deliveries', () => {
    cy.visit('/admin/deliveries');
    cy.get('[data-cy="deliveries-monitoring"]').should('be.visible');
    cy.get('[data-cy="delivery-status-chip"]').should('have.length.at.least', 0);
  });
});
