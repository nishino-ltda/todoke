describe('Login Flow', () => {
  beforeEach(() => {
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

    // Visit login page
    cy.visit('/login')
  })

  it('displays the login form', () => {
    // Check if we're on the login page
    cy.contains('h1, h2, h3, .v-card-title', 'Login').should('be.visible')
    
    // Check if form elements are present
    cy.get('[data-test="email-input"]').should('be.visible')
    cy.get('[data-test="password-input"]').should('be.visible')
    cy.get('[data-test="submit-button"]').should('be.visible').and('contain', 'Login')
    
    // Check if the link to register page exists
    cy.contains('a', 'Create an account').should('be.visible')
      .should('have.attr', 'href', '/register')
  })

  it('successfully logs in with valid credentials', () => {
    // Fill in the form
    cy.get('[data-test="email-input"] input').type('test@example.com')
    cy.get('[data-test="password-input"] input').type('password123')
    
    // Submit the form
    cy.get('[data-test="submit-button"]').click()
    
    // Wait for the API request
    cy.wait('@loginRequest')
    
    // Check if we're redirected to the home page
    cy.url().should('include', '/')
    
    // Check if the user is logged in (header shows logout button)
    cy.contains('button', 'Logout').should('be.visible')
    cy.contains('Welcome, Test User').should('be.visible')
  })

  it('shows error message with invalid credentials', () => {
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
    
    // Check that we're still on the login page
    cy.url().should('include', '/login')
  })

  it('shows loading state during login', () => {
    // Slow down the API response
    cy.intercept('POST', '/api/auth/login', (req) => {
      req.on('response', (res) => {
        // Delay the response by 1 second
        res.setDelay(1000)
      })
    }).as('slowLoginRequest')
    
    // Fill in the form
    cy.get('[data-test="email-input"] input').type('test@example.com')
    cy.get('[data-test="password-input"] input').type('password123')
    
    // Submit the form
    cy.get('[data-test="submit-button"]').click()
    
    // Check if loading state is shown
    cy.get('[data-test="submit-button"]').should('be.disabled')
      .and('contain', 'Logging in...')
  })

  it('navigates to register page when clicking the link', () => {
    // Click on the register link
    cy.contains('a', 'Create an account').click()
    
    // Check if we're redirected to the register page
    cy.url().should('include', '/register')
    cy.contains('h1, h2, h3, .v-card-title', 'Register').should('be.visible')
  })
})
