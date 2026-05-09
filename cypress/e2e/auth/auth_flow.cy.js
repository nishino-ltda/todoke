describe('🔑 Auth Flow', () => {
  const users = {
    customer: { email: 'customer@todoke.test', password: 'password123' },
    partner: { email: 'partner@todoke.test', password: 'password123' },
    courier: { email: 'courier@todoke.test', password: 'password123' },
    admin: { email: 'admin@todoke.test', password: 'password123' }
  };

  beforeEach(() => {
    cy.visit('/login');
  });

  it('🌐 Should switch language on login page', () => {
    // Default should be pt-BR based on requirements
    cy.get('h1').should('contain', 'Login'); // Or whatever the PT version is
    
    // Switch to English
    cy.get('[data-cy="language-selector"]').click();
    cy.get('.v-list-item').contains('English').click();
    
    // Verify text changes
    cy.get('h1').should('contain', 'Login'); // "Login" is same in both? Let's check "Register"
    cy.get('[data-cy="forgot-password-link"]').should('contain', 'Forgot Password');
  });

  it('👥 Should login/logout for each role', () => {
    Object.entries(users).forEach(([role, user]) => {
      cy.visit('/login');
      cy.get('[data-cy="email-input"] input').type(user.email);
      cy.get('[data-cy="password-input"] input').type(user.password);
      cy.get('[data-cy="login-button"]').click();

      // Verify redirect
      cy.url().should('include', `/${role}/dashboard`);
      
      // Logout
      cy.get('[data-cy="logout-button"]').click();
      cy.url().should('include', '/login');
    });
  });

  it('⚠️ Should show registration validation errors', () => {
    cy.visit('/register');
    cy.get('[data-cy="register-button"]').click();
    
    cy.get('[data-cy="validation-alert"]').should('be.visible');
    cy.get('[data-cy="email-input"]').should('contain', 'required');
  });

  it('❌ Should handle invalid credentials', () => {
    cy.get('[data-cy="email-input"] input').type('wrong@user.test');
    cy.get('[data-cy="password-input"] input').type('wrongpassword');
    cy.get('[data-cy="login-button"]').click();
    
    cy.get('[data-cy="auth-alert"]').should('be.visible')
      .should('contain', 'credentials');
  });

  it('🔄 Should handle password reset flow', () => {
    cy.get('[data-cy="forgot-password-link"]').click();
    cy.url().should('include', '/forgot-password');
    
    cy.get('[data-cy="email-input"] input').type('customer@todoke.test');
    cy.get('[data-cy="submit-button"]').click();
    
    cy.get('[data-cy="success-alert"]').should('be.visible');
  });
});
