describe('App Component Tests', () => {
  beforeEach(() => {
    cy.visit('/')
  })

  it('should display the app header', () => {
    cy.get('[data-test="app-header"]').should('be.visible')
  })

  it('should display the app footer', () => {
    cy.get('[data-test="app-footer"]').should('be.visible')
  })
})
