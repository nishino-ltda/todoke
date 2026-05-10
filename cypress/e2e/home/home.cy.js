describe('🏠 Home Page', () => {
  beforeEach(() => {
    cy.visit('/', {
      onBeforeLoad(win) {
        win.localStorage.clear();
      },
    });
  });

  it('✨ Should load the home page correctly', () => {
    cy.get('[data-cy="home-page"]').should('be.visible');
    cy.get('[data-cy="app-header"]').should('be.visible');
    cy.get('[data-cy="app-footer"]').should('be.visible');
  });

  it('🌐 Should switch language correctly', () => {
    // Get initial title
    cy.get('[data-cy="hero-title"]').invoke('text').then((initialText) => {
      const isEnglish = initialText.includes('Hybrid Delivery');
      const targetLang = isEnglish ? 'Português' : 'English';
      const expectedSnippet = isEnglish ? 'Entrega Híbrida' : 'Hybrid Delivery';

      cy.log(`Initial text: "${initialText}" (isEnglish: ${isEnglish})`);
      cy.log(`Switching to: ${targetLang}`);

      // Switch language
      cy.get('[data-cy="language-selector"]').click();
      cy.get('.v-list-item').contains(targetLang).click();
      
      // Verify text changed
      cy.get('[data-cy="hero-title"]').should('contain', expectedSnippet);
      cy.get('[data-cy="hero-title"]').should('not.contain', initialText);
    });
  });

  it('📱 Should be responsive', () => {
    // Desktop
    cy.viewport(1280, 720);
    cy.get('[data-cy="home-page"]').should('be.visible');

    // Mobile
    cy.viewport('iphone-xr');
    cy.get('[data-cy="home-page"]').should('be.visible');
    // Header nav icon should be visible on mobile
    cy.get('.v-app-bar-nav-icon').should('be.visible');
  });

  it('🔗 Should navigate to auth pages', () => {
    cy.get('[data-cy="login-link"]').click();
    cy.url().should('include', '/login');
    cy.get('[data-cy="auth-login"]').should('be.visible');

    cy.visit('/');
    cy.get('[data-cy="register-link"]').click();
    cy.url().should('include', '/register');
    cy.get('[data-cy="auth-register"]').should('be.visible');
  });
});
