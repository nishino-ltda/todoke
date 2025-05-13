describe('Login Flow', () => {
  beforeEach(() => {
    // Clear auth state
    cy.window().then((win) => {
      win.localStorage.removeItem('token')
    })

    // Intercept API requests
    cy.intercept('POST', '/api/auth/login', (req) => {
      // Successful login response
      if (req.body.email === 'test@example.com' && req.body.password === 'password123') {
        req.reply({
          statusCode: 200,
          body: {
            token: 'fake-jwt-token',
            user: {
              id: 1,
              name: 'Test User',
              email: 'test@example.com',
              type: 'customer'
            }
          }
        })
      } else {
        // Failed login response
        req.reply({
          statusCode: 401,
          body: {
            message: 'Invalid email or password'
          }
        })
      }
    }).as('loginRequest')

    // Visit login page and verify logged out state
    cy.visit('/login')
    cy.window().its('localStorage.token').should('be.undefined')
  })

  it('displays the login form', () => {
    cy.log('🔍 Starting login form test')
    // Check if we're on the login page
    cy.contains('h1, h2, h3, .v-card-title', 'Login').should('be.visible')
    
    // Check if form elements are present
    cy.get('[data-test="email-input"]').should('be.visible')
    cy.log('✅ Found email input')
    cy.get('[data-test="password-input"]').should('be.visible')
    cy.log('✅ Found password input')
    cy.get('[data-test="submit-button"]').should('be.visible').and('contain', 'Login')
    cy.log('✅ Found submit button')
    
    // Check if the link to register page exists
    cy.contains('a', 'Create an account').should('be.visible')
      .should('have.attr', 'href', '/register')
    cy.log('✅ Found register link')
  })

  it('successfully logs in with valid credentials', () => {
    cy.log('🚀 Starting login with valid credentials')
    // Fill in the form
    cy.get('[data-test="email-input"] input').type('test@example.com')
    cy.log('🔍 Entered test email')
    cy.get('[data-test="password-input"] input').type('password123')
    cy.log('🔍 Entered test password')
    
    // Submit the form
    cy.get('[data-test="submit-button"]').click()
    cy.log('🚀 Submitted login form')
    
    // Wait for the API request
    cy.wait('@loginRequest')
    
    // Check if we're redirected to the home page
    cy.url().should('include', '/')
    cy.log('✅ Redirected to home page')
    
    // Check if the user is logged in (header shows logout button)
    cy.contains('button', 'Logout').should('be.visible')
    cy.contains('Welcome, Test User').should('be.visible')
    cy.log('✅ Login successful - user authenticated')
  })

  it('shows error message with invalid credentials', () => {
    cy.log('⚠️ Testing invalid credentials')
    // Fill in the form with invalid credentials
    cy.get('[data-test="email-input"] input').type('wrong@example.com')
    cy.get('[data-test="password-input"] input').type('wrongpassword')
    
    // Submit the form
    cy.get('[data-test="submit-button"]').click()
    
    // Wait for the API request
    cy.wait('@loginRequest')
    
    // Check if error message is displayed
    cy.get('[data-test="error-alert"]').should('be.visible')
      .and('contain', 'Invalid email or password')
    cy.log('⚠️ Error message displayed')
    
    // Check that we're still on the login page
    cy.url().should('include', '/login')
  })

  it('shows loading state during login', () => {
    cy.log('🔁 Resetting auth state')
    // Ensure we're logged out
    cy.logout()
    
    cy.log('🔍 Verifying logged out state')
    cy.clearCookies()
    cy.window().then((win) => {
      win.localStorage.removeItem('token')
    })
    
    // Mock a delayed API response (500ms) that fails
    cy.intercept('POST', '/api/auth/login', {
      delay: 500,
      statusCode: 401,
      body: {
        message: 'Invalid credentials'
      }
    }).as('loginRequest')
    
    // Fill in the form
    cy.get('[data-test="email-input"] input').type('test@example.com')
    cy.get('[data-test="password-input"] input').type('password123')
    
    cy.log('🚀 Submitting form with loading state')
    // Submit the form and verify loading state appears
    cy.get('[data-test="submit-button"]').as('submitBtn')
    cy.get('@submitBtn').should('be.visible').click()
    cy.get('@submitBtn').should('have.class', 'v-btn--loading')
    cy.log('✅ Loading state verified')
  })

  it('navigates to register page when clicking the link', () => {
    cy.log('🔍 Testing register page navigation')
    // Click on the register link
    cy.contains('a', 'Create an account').click()
    
    // Check if we're redirected to the register page
    cy.url().should('include', '/register')
    cy.contains('h1, h2, h3, .v-card-title', 'Register').should('be.visible')
    cy.log('✅ Successfully navigated to register page')
  })
})
