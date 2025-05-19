describe('Log Store Test', () => {
  it('should capture and display logs from components', () => {
    cy.visit('/test-log');
    
    // Click the button to trigger a log
    cy.get('#log-button').click();
    
    // Verify the log appears in the UI
    cy.get('#logged-message').should('contain', 'Test message from TestComponent');
    
    // Verify the log is available to Cypress
    cy.window().then((win) => {
      const latestLog = win.logStore.getLatest();
      cy.log('📢 Latest log from store:', latestLog);
      expect(latestLog).to.equal('Test message from TestComponent');
    });
  });
});
