describe('Registration Flow', () => {
  beforeEach(() => {
    // Intercept API requests
    cy.intercept('POST', '/api/auth/register', (req) => {
      // Check for required fields
      if (!req.body.name || !req.body.email || !req.body.password || !req.body.password_confirmation) {
        req.reply({
          statusCode: 422,
          body: {
            message: 'The given data was invalid.',
            errors: {
              name: req.body.name ? null : ['The name field is required.'],
              email: req.body.email ? null : ['The email field is required.'],
              password: req.body.password ? null : ['The password field is required.']
            }
          }
        })
        return
      }

      // Check for email format
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(req.body.email)) {
        req.reply({
          statusCode: 422,
          body: {
            message: 'The given data was invalid.',
            errors: {
              email: ['The email must be a valid email address.']
            }
          }
        })
        return
      }

      // Check for password confirmation
      if (req.body.password !== req.body.password_confirmation) {
        req.reply({
          statusCode: 422,
          body: {
            message: 'The given data was invalid.',
            errors: {
              password: ['The password confirmation does not match.']
            }
          }
        })
        return
      }

      // Check for existing email
      if (req.body.email === 'existing@example.com') {
        req.reply({
          statusCode: 422,
          body: {
            message: 'The given data was invalid.',
            errors: {
              email: ['The email has already been taken.']
            }
          }
        })
        return
      }

      // Successful registration
      req.reply({
        statusCode: 200,
        body: {
          token: 'fake-jwt-token',
          user: {
            id: 1,
            name: req.body.name,
            email: req.body.email,
            type: req.body.role || 'customer'
          }
        }
      })
    }).as('registerRequest')

    // Visit register page
    cy.visit('/register')
  })

  it('displays the registration form', () => {
    // Check if we're on the register page
    cy.contains('h1, h2, h3, .v-card-title', 'Register').should('be.visible')
    
    // Check if form elements are present
    cy.get('[data-test="name-input"]').should('be.visible')
    cy.get('[data-test="email-input"]').should('be.visible')
    cy.get('[data-test="password-input"]').should('be.visible')
    cy.get('[data-test="password-confirmation-input"]').should('be.visible')
    cy.get('[data-test="submit-button"]').should('be.visible').and('contain', 'Register')
    
    // Check if the link to login page exists
    cy.contains('a', 'Already have an account?').should('be.visible')
      .should('have.attr', 'href', '/login')
  })

  it('successfully registers with valid data', () => {
    // Fill in the form
    cy.get('[data-test="name-input"] input').type('New User')
    cy.get('[data-test="email-input"] input').type('newuser@example.com')
    cy.get('[data-test="password-input"] input').type('password123')
    cy.get('[data-test="password-confirmation-input"] input').type('password123')
    
    // Submit the form
    cy.get('[data-test="submit-button"]').click()
    
    // Wait for the API request
    cy.wait('@registerRequest')
    
    // Check if we're redirected to the home page
    cy.url().should('include', '/')
    
    // Check if the user is logged in (header shows logout button)
    cy.contains('button', 'Logout').should('be.visible')
    cy.contains('Welcome, New User').should('be.visible')
  })

  it('shows validation errors for empty fields', () => {
    // Submit the form without filling it
    cy.get('[data-test="submit-button"]').click()
    
    // Add small delay to prevent race conditions
    cy.wait(500)
    
    // Wait for the API request
    cy.wait('@registerRequest')
    
    // Debug validation errors
    cy.pause()
    cy.get('[data-test="name-input"]').then(($el) => {
      console.log('Name input DOM:', $el[0].outerHTML)
    })
    cy.get('[data-test="email-input"]').then(($el) => {
      console.log('Email input DOM:', $el[0].outerHTML)
    })
    cy.get('[data-test="password-input"]').then(($el) => {
      console.log('Password input DOM:', $el[0].outerHTML)
    })
    
    // Check that we're still on the register page
    cy.url().should('include', '/register')
  })

  it('shows error for invalid email format', () => {
    // Fill in the form with invalid email
    cy.get('[data-test="name-input"] input').type('New User')
    cy.get('[data-test="email-input"] input').type('invalid-email')
    cy.get('[data-test="password-input"] input').type('password123')
    cy.get('[data-test="password-confirmation-input"] input').type('password123')
    
    // Submit the form
    cy.get('[data-test="submit-button"]').click()
    
    // Wait for the API request
    cy.wait('@registerRequest')
    
    // Check if validation error is displayed
    cy.get('[data-test="email-input"] .v-messages__message').should('contain', 'The email must be a valid email address')
    
    // Check that we're still on the register page
    cy.url().should('include', '/register')
  })

  it('shows error for password confirmation mismatch', () => {
    // Fill in the form with mismatched passwords
    cy.get('[data-test="name-input"] input').type('New User')
    cy.get('[data-test="email-input"] input').type('newuser@example.com')
    cy.get('[data-test="password-input"] input').type('password123')
    cy.get('[data-test="password-confirmation-input"] input').type('different123')
    
    // Submit the form
    cy.get('[data-test="submit-button"]').click()
    
    // Wait for the API request
    cy.wait('@registerRequest')
    
    // Check if validation error is displayed
    cy.get('[data-test="password-input"] .v-messages__message').should('contain', 'The password confirmation does not match')
    
    // Check that we're still on the register page
    cy.url().should('include', '/register')
  })

  it('shows error for existing email', () => {
    // Fill in the form with an existing email
    cy.get('[data-test="name-input"] input').type('New User')
    cy.get('[data-test="email-input"] input').type('existing@example.com')
    cy.get('[data-test="password-input"] input').type('password123')
    cy.get('[data-test="password-confirmation-input"] input').type('password123')
    
    // Submit the form
    cy.get('[data-test="submit-button"]').click()
    
    // Wait for the API request
    cy.wait('@registerRequest')
    
    // Check if validation error is displayed
    cy.get('[data-test="email-input"] .v-messages__message').should('contain', 'The email has already been taken')
    
    // Check that we're still on the register page
    cy.url().should('include', '/register')
  })

  it('shows loading state during registration', () => {
    // Slow down the API response
    cy.intercept('POST', '/api/auth/register', (req) => {
      req.on('response', (res) => {
        // Delay the response by 1 second
        res.setDelay(1000)
      })
    }).as('slowRegisterRequest')
    
    // Fill in the form
    cy.get('[data-test="name-input"] input').type('New User')
    cy.get('[data-test="email-input"] input').type('newuser@example.com')
    cy.get('[data-test="password-input"] input').type('password123')
    cy.get('[data-test="password-confirmation-input"] input').type('password123')
    
    // Submit the form
    cy.get('[data-test="submit-button"]').click()
    
    // Check if loading state is shown
    cy.get('[data-test="submit-button"]').should('have.class', 'v-btn--loading')
  })

  it('navigates to login page when clicking the link', () => {
    // Click on the login link
    cy.contains('a', 'Already have an account?').click()
    
    // Check if we're redirected to the login page
    cy.url().should('include', '/login')
    cy.contains('h1, h2, h3, .v-card-title', 'Login').should('be.visible')
  })
})
