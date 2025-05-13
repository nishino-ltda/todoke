describe('Partner Login', () => {
  beforeEach(() => {
    cy.visit('/partner/login')
  })

  it('displays login form', () => {
    cy.log('Starting login form test')
    cy.get('[data-test="login-form"]').should('exist')
    cy.log('Found login form')
    cy.get('[data-test="email-input"]').should('exist')
    cy.log('Found email input')
    cy.get('[data-test="password-input"]').should('exist')
    cy.log('Found password input')
    cy.get('[data-test="submit-btn"]').should('exist')
    cy.log('Found submit button - test complete')
  })

  it('validates required fields', () => {
    cy.get('[data-test="submit-btn"]').click()
    cy.get('[data-test="email-error"]').should('contain', 'Required')
    cy.get('[data-test="password-error"]').should('contain', 'Required')
  })

  it('shows error for invalid credentials', () => {
    cy.get('[data-test="email-input"]').type('invalid@example.com')
    cy.get('[data-test="password-input"]').type('wrongpassword')
    cy.get('[data-test="submit-btn"]').click()
    cy.get('[data-test="login-error"]').should('contain', 'Invalid credentials')
  })

  it('redirects to dashboard on successful login', () => {
    cy.get('[data-test="email-input"]').type('partner@example.com')
    cy.get('[data-test="password-input"]').type('validpassword')
    cy.get('[data-test="submit-btn"]').click()
    cy.url().should('include', '/partner/dashboard')
  })
})
