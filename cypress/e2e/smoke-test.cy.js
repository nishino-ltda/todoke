describe('Smoke Test', () => {
  it('Visits the app root url', () => {
    cy.visit('/')
    cy.contains('TODOKE') // Check for app name in page
  })
})
