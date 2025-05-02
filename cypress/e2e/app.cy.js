describe('App Component Tests', () => {
  beforeEach(() => {
    cy.visit('/')
  })

  it('should load the main app view', () => {
    cy.get('[data-v-app]').should('exist')
  })
})
