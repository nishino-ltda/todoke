describe('🔄 Common Components', () => {
  // SPRINT 1: Common components testing
  it('🔤 Should render header correctly', () => {
    cy.log('🔝 Testing header component');
    cy.visit('/');
    
    // Verify basic structure
    cy.get('[data-test="app-header"]').should('exist');
    cy.contains('TODOKE').should('be.visible');
    
    // Test guest state
    cy.get('[data-test="login-link"]').should('be.visible');
    cy.get('[data-test="register-link"]').should('be.visible');
  });

  // SPRINT 1: Common components testing
  it('🦶 Should render footer correctly', () => {
    cy.log('🔚 Testing footer component');
    cy.visit('/');
    
    // Verify basic structure
    cy.get('[data-test="app-footer"]').should('exist');
    cy.contains(`© ${new Date().getFullYear()} TODOKE`).should('be.visible');
    
    // Test links
    cy.get('[data-test="terms-link"]').should('be.visible');
    cy.get('[data-test="privacy-link"]').should('be.visible');
  });

  // SPRINT 1: Log store testing
  it('📝 Should use log store for logging', () => {
    cy.log('📋 Testing log store integration');
    cy.visit('/test-log');
    
    // Initialize log store if not available
    cy.window().then((win) => {
      if (!win.logStore) {
        win.logStore = {
          getLogs: () => [],
          getLatest: () => '',
          clear: () => {}
        };
      }
    });
    
    // Click the button to trigger a log
    cy.get('#log-button').click();
    
    // Verify the log appears in the UI (wait up to 3 seconds as per requirements)
    cy.get('[data-test="logged-message"]', { timeout: 3000 }).should('contain', 'Test message from TestComponent');
    
    // Verify the log is available to Cypress
    cy.window().then((win) => {
      const latestLog = win.logStore.getLatest();
      cy.log('📢 Latest log from store:', latestLog);
      expect(latestLog).to.equal('Test message from TestComponent');
    });
  });
});
