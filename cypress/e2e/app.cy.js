describe('🚀 App Component', () => {
  // SPRINT 1: Core app initialization testing
  it('🔍 Should initialize the application', () => {
    cy.log('🔄 Testing app initialization');
    cy.visit('/');
    
    // Verify Vue app is mounted
    cy.get('[data-v-app]').should('exist');
    
    // Verify core components are present
    cy.get('[data-test="app-header"]').should('exist');
    cy.get('[data-test="app-footer"]').should('exist');
    cy.get('[data-test="main-content"]').should('exist');
  });

  // SPRINT 1: Core app initialization testing
  it('🔒 Should handle authentication state', () => {
    cy.log('🔑 Testing auth state management');
    
    // Test guest state
    cy.visit('/');
    cy.get('[data-test="login-link"]').should('be.visible');
    
    // Mock authenticated state
    cy.intercept('GET', '/api/user', {
      statusCode: 200,
      body: {
        user: { name: 'Test User', type: 'customer' }
      }
    }).as('userCheck');
    
    cy.setCookie('auth_token', 'fake-token');
    cy.visit('/');
    cy.wait('@userCheck');
    
    // Verify authenticated state
    cy.contains('Welcome, Test User').should('be.visible');
    cy.get('[data-test="logout-button"]').should('be.visible');
  });

  // SPRINT 1: Core app initialization testing
  it('📱 Should be responsive', () => {
    cy.log('📲 Testing responsive behavior');
    
    // Test desktop view
    cy.viewport(1280, 800);
    cy.visit('/');
    cy.get('[data-test="app-header"]').should('have.css', 'height', '64px');
    
    // Test mobile view
    cy.viewport('iphone-x');
    cy.get('[data-test="app-header"]').should('have.css', 'height', '56px');
    cy.get('.v-app-bar-nav-icon').should('be.visible');
  });

  // SPRINT 1: Core app initialization testing
  it('🌐 Should handle routing', () => {
    cy.log('🛣️ Testing client-side routing');
    
    cy.visit('/');
    cy.get('[data-test="login-link"]').click();
    cy.url().should('include', '/login');
    
    cy.go('back');
    cy.url().should('eq', Cypress.config().baseUrl + '/');
  });

  // Log store testing
  it('📝 Should log messages to terminal', () => {
    cy.log('📋 Testing log store functionality');
    cy.visit('/');
    
    // Verify log message appears in terminal
    cy.window().then((win) => {
      const latestLog = win.logStore?.getLatest();
      expect(latestLog).to.equal('Home page loaded');
      cy.log(`✅ Verified log message: "${latestLog}"`);
    });
  });
});
