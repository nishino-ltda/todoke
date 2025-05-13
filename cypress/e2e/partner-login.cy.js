describe('Partner Login', () => {
  beforeEach(() => {
    cy.visit('/login')
  })

  it('displays login form', () => {
    cy.log('🔍 Starting login form test')
    cy.get('[data-test="login-form"]').should('exist')
    cy.log('✅ Found login form')
    cy.get('[data-test="email-input"]').should('exist')
    cy.log('✅ Found email input')
    cy.get('[data-test="password-input"]').should('exist')
    cy.log('✅ Found password input')
    cy.get('[data-test="submit-button"]').should('exist')
    cy.log('✅ Found submit button - test complete')
  })

  it('validates required fields', () => {
    cy.log('🔍 Starting required fields validation test')
    cy.get('[data-test="submit-button"]').click()
    cy.get('[data-test="error-alert"]').should('contain', 'As credenciais fornecidas estão incorretas')
    cy.log('✅ Completed required fields validation test')
  })

  it('shows error for invalid credentials', () => {
    cy.log('⚠️ Starting invalid credentials test')
    const invalidEmail = 'invalid@example.com'
    const invalidPassword = 'wrongpassword'
    cy.log(`🚀 Using test email: ${invalidEmail}`)
    cy.get('[data-test="email-input"]').type(invalidEmail)
    cy.log(`🚀 Using test password: ${invalidPassword}`)
    cy.get('[data-test="password-input"]').type(invalidPassword)
    cy.get('[data-test="submit-button"]').click()
    cy.get('[data-test="error-alert"]').should('contain', 'As credenciais fornecidas estão incorretas')
    cy.log('✅ Completed invalid credentials test')
  })

  it('redirects to partner dashboard on successful login', () => {
    cy.log('🔍 Starting successful login test')
    const validEmail = 'partner@example.com'
    const validPassword = 'password123'
    cy.log(`🚀 Using valid email: ${validEmail}`)
    cy.get('[data-test="email-input"]').type(validEmail)
    cy.log(`🚀 Using valid password: ${validPassword}`)
    cy.get('[data-test="password-input"]').type(validPassword)
    cy.get('[data-test="submit-button"]').click()
    cy.url().should('include', '/partner')
    cy.log('✅ Completed successful login test')
  })
})
