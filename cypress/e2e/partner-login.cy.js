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
    
    cy.get('[data-test="error-alert"]').then(($alert) => {
      cy.log(`📝 Alert content: ${$alert.text()}`)
      expect($alert.text()).to.equal('The email field is required. (and 1 more error)')
    })
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
    
    cy.intercept('POST', '/api/v1/auth/login').as('loginRequest')
    cy.get('[data-test="submit-button"]').click()
    cy.wait('@loginRequest').its('response.statusCode').should('eq', 401)
    
    cy.get('[data-test="error-alert"]').then(($alert) => {
      cy.log(`📝 Alert content: ${$alert.text()}`)
      expect($alert.text()).to.equal('As credenciais fornecidas estão incorretas')
    })
    cy.log('✅ Completed invalid credentials test')
  })

  it('redirects to partner dashboard on successful login', () => {
    cy.log('🔍 Starting successful login test')
    const validEmail = 'partner@example.com'
    const validPassword = 'password123'
    
    // Prevent test from failing on Ziggy route errors
    Cypress.on('uncaught:exception', (err) => {
      return !err.message.includes('Ziggy error')
    })

    // Intercept the login API call
    cy.intercept('POST', '/api/v1/auth/login').as('loginRequest')

    cy.get('[data-test="email-input"]').type(validEmail)
    cy.get('[data-test="password-input"]').type(validPassword)
    cy.get('[data-test="submit-button"]').click()
    
    // Wait for login request to complete and verify response
    cy.wait('@loginRequest').then((interception) => {
      expect(interception.response.statusCode).to.equal(200)
      expect(interception.response.body.user.type).to.equal('partner')
      
      // Debug: Log the API response
      cy.log('API Response:', interception.response.body)
      
      // Debug: Check if token was set in localStorage
      cy.window().then((win) => {
        const token = win.localStorage.getItem('token')
        cy.log('Token in localStorage:', token)
        expect(token).to.be.a('string').and.not.be.empty
      })
    })

    // Track success event emission
    cy.get('[data-test="login-form"]').then(() => {
      Cypress.on('window:success', () => {
        cy.log('Success event emitted')
      })
    })

    // Verify redirect and dashboard load with longer timeout
    cy.url().should('include', '/partner/dashboard', { timeout: 20000 })
    cy.get('[data-test="partner-dashboard"]', { timeout: 20000 }).should('be.visible')
  })
})
