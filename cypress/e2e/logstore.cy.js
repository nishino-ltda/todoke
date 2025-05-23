describe('LogStore Test', () => {
  it('should log messages to terminal', () => {
    cy.visit('/');
    
    // Wait for logStore to be initialized
    cy.window().should('have.property', 'logStore');
    
    // Get the latest log message
    cy.window().then((win) => {
      const latestLog = win.logStore.getLatest();
      expect(latestLog).to.equal('Home page loaded');
      cy.log(`Latest log message: ${latestLog}`);
    });
  });
});
