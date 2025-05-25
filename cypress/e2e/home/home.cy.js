describe('Home Page', () => {
  beforeEach(() => {
    cy.visit('/')
    cy.log('🏠 Starting home page tests')
  })

  it('displays hero section with correct content', () => {
    cy.get('[data-test="home-hero"]').should('exist')
    cy.get('[data-test="hero-title"]').should('contain', 'Hybrid Delivery for Everyone')
    cy.get('[data-test="hero-subtitle"]').should('contain', 'Combining traditional couriers with drone technology')
  })

  it('has working register and login buttons', () => {
    cy.get('[data-test="hero-register-btn"]')
      .should('contain', 'Get Started')
      .click()
    cy.url().should('include', '/register')
    cy.go('back')

    cy.get('[data-test="hero-login-btn"]')
      .should('contain', 'Sign In')
      .click()
    cy.url().should('include', '/login')
  })

  it('displays hero image', () => {
    cy.get('[data-test="hero-image"]')
      .should('be.visible')
      .and('have.attr', 'alt', 'Hybrid delivery illustration')
  })
})
