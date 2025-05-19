describe('Log Store Test', () => {
  it('should display the logged message on the page and update the window object', () => {
    cy.visit('/test-log');
    cy.get('#logged-message').should('contain', 'Hello from TestLog.vue');
    cy.window().should('have.property', 'loggedMessage', 'Hello from TestLog.vue');
  });
});
