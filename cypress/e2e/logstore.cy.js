describe('📜 LogStore Test', () => {
  beforeEach(() => {
    cy.visit('/');
    cy.window().should('have.property', 'logStore');
  });

  it('should properly format and display logs', () => {
    // Verify initial logs exist
    cy.dumpLogs(false, 'Initial Application Logs');
    
    // Add test logs through the log store directly
    cy.window().then(win => {
      win.logStore.log('Test info message', 'info');
      win.logStore.log('Test warning message', 'warn');
      win.logStore.log('Test error message', 'error', new Error('Test error'));
    });

    // Verify all logs are properly formatted (don't clear logs automatically)
    cy.dumpLogs(false, 'Test Logs').then(() => {
      cy.window().then(win => {
        const logs = win.logStore.getLogs();
        expect(logs).to.have.length.above(3);
        expect(logs.some(l => l.type === 'info')).to.be.true;
        expect(logs.some(l => l.type === 'warn')).to.be.true; 
        expect(logs.some(l => l.type === 'error')).to.be.true;
        
        // Explicit cleanup after verification
        win.logStore.clear();
        cy.log('🧹 Test logs cleared after verification');
      });
    });
  });

  it('should maintain log history', () => {
    const testMessage = `Test message ${Date.now()}`;
    
    cy.window().then(win => {
      win.logStore.log(testMessage);
    });

    cy.dumpLogs(false, 'Log History Test');
    
    cy.window().then(win => {
      const logs = win.logStore.getLogs();
      expect(logs.some(l => l.message.includes(testMessage))).to.be.true;
    });
  });
});
