describe('🔄 Common Components', () => {
  // SPRINT 1: Common components testing
  it('🔤 Should render header correctly', () => {
    cy.log('🔝 Testing header component');
    cy.visit('/');
    
    // Verify logo and basic structure
    cy.get('[data-test="app-header"]').should('exist');
    cy.contains('TODOKE').should('be.visible');
    
    // Test guest state
    cy.get('[data-test="login-link"]').should('be.visible');
    cy.get('[data-test="register-link"]').should('be.visible');
    
    // Mock login and test authenticated state
    cy.intercept('POST', '/api/login', {
      statusCode: 200,
      body: {
        user: { name: 'Test User', type: 'customer' },
        token: 'fake-token'
      }
    }).as('login');
    
    cy.get('[data-test="login-link"]').click();
    cy.get('[data-test="email-input"]').type('test@todoke.test');
    cy.get('[data-test="password-input"]').type('password123');
    cy.get('[data-test="login-button"]').click();
    cy.wait('@login');
    
    cy.contains('Welcome, Test User').should('be.visible');
    cy.get('[data-test="menu-link"]').should('be.visible');
    cy.get('[data-test="logout-button"]').should('be.visible');
  });

  // SPRINT 1: Common components testing
  it('🦶 Should render footer correctly', () => {
    cy.log('🔚 Testing footer component');
    cy.visit('/');
    
    // Verify basic structure
    cy.get('.app-footer').should('exist');
    cy.contains(`© ${new Date().getFullYear()} TODOKE`).should('be.visible');
    
    // Test links
    cy.get('[data-test="terms-link"]').should('be.visible');
    cy.get('[data-test="privacy-link"]').should('be.visible');
    cy.get('[data-test="facebook-link"]').should('be.visible');
    cy.get('[data-test="twitter-link"]').should('be.visible');
    cy.get('[data-test="instagram-link"]').should('be.visible');
  });

  // SPRINT 1: Common components testing
  it('🔄 Should handle loading states', () => {
    cy.log('⏳ Testing loading components');
    cy.visit('/');
    
    // Mock API call with delay
    cy.intercept('GET', '/api/some-data', {
      delay: 1000,
      body: { data: 'test' }
    }).as('apiCall');
    
    // Trigger loading
    cy.window().then((win) => {
      win.$loading.start('Loading data...');
    });
    
    // Verify loading indicator
    cy.get('.loading-indicator').should('be.visible');
    cy.contains('Loading data...').should('be.visible');
    
    // Complete loading
    cy.window().then((win) => {
      win.$loading.stop();
    });
    cy.get('.loading-indicator').should('not.exist');
  });

  // SPRINT 1: Common components testing
  it('⏳ Should show LoadingIndicator component', () => {
    cy.log('🌀 Testing LoadingIndicator component');
    cy.visit('/test-loading');
    
    // Test full-page loader
    cy.get('[data-test="full-loader"]').should('not.exist');
    cy.get('[data-test="show-full-loader"]').click();
    cy.get('[data-test="full-loader"]').should('be.visible');
    cy.contains('Loading...').should('be.visible');
    cy.get('[data-test="hide-full-loader"]').click();
    cy.get('[data-test="full-loader"]').should('not.exist');

    // Test inline loader
    cy.get('[data-test="inline-loader"]').should('not.exist');
    cy.get('[data-test="show-inline-loader"]').click();
    cy.get('[data-test="inline-loader"]').should('be.visible');
    cy.get('[data-test="hide-inline-loader"]').click();
    cy.get('[data-test="inline-loader"]').should('not.exist');
  });

  // SPRINT 1: Common components testing
  it('💬 Should display notifications', () => {
    cy.log('🔔 Testing notification system');
    cy.visit('/');
    
    // Test success notification
    cy.window().then((win) => {
      win.$notify.success('Operation successful!');
    });
    cy.contains('Operation successful!').should('be.visible');
    cy.get('.v-alert--success').should('be.visible');
    
    // Test error notification
    cy.window().then((win) => {
      win.$notify.error('Something went wrong');
    });
    cy.contains('Something went wrong').should('be.visible');
    cy.get('.v-alert--error').should('be.visible');
    
    // Test auto-dismiss
    cy.contains('Operation successful!').should('not.exist', { timeout: 5000 });
  });

  // SPRINT 1: Common components testing
  it('📱 Should work on mobile', () => {
    cy.log('📲 Testing mobile components');
    cy.viewport('iphone-x');
    cy.visit('/');
    
    // Verify header is responsive
    cy.get('[data-test="app-header"]').should('be.visible');
    cy.get('.v-app-bar-nav-icon').should('be.visible');
    
    // Test menu toggle
    cy.get('.v-app-bar-nav-icon').click();
    cy.get('.v-navigation-drawer').should('be.visible');
    
    // Verify footer layout
    cy.get('.app-footer').within(() => {
      cy.get('.v-row').should('have.css', 'flex-direction', 'column');
    });
  });

  // SPRINT 1: Log store testing
  it('📝 Should use log store for logging', () => {
    cy.log('📋 Testing log store integration');
    cy.visit('/');
    
    // Verify log store is available
    cy.window().should('have.property', '__appStores');
    cy.window().its('__appStores.logStore').should('exist');
    
    // Test logging through the store
    cy.window().then(win => {
      const logStore = win.__appStores.logStore;
      logStore.clear();
      logStore.log('Test message', 'info');
      
      expect(logStore.messages).to.have.length(1);
      expect(logStore.messages[0].message).to.equal('Test message');
      expect(logStore.messages[0].type).to.equal('info');
    });
  });
});
