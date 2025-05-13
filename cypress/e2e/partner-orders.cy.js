describe('Partner Order Management', () => {
  beforeEach(() => {
    cy.log('🔐 Starting partner login')
    cy.visit('/login')
    cy.log('📝 Filling login form')
    cy.get('[data-test="email-input"]').type('partner@example.com')
    cy.get('[data-test="password-input"]').type('password123')
    cy.log('🔄 Submitting form')
    cy.get('[data-test="submit-button"]').click()
    
    // Check for error alert first
    cy.get('[data-test="error-alert"]').then(($alert) => {
      if ($alert.length) {
        cy.log('❌ Error alert found:', $alert.text())
      }
    })

    // Debug the API response
    cy.intercept('POST', '/api/v1/auth/login').as('loginRequest')
    cy.wait('@loginRequest').then((interception) => {
      cy.log('🔍 Login API response:', interception.response?.body)
      if (interception.response?.body?.user) {
        cy.log('👤 User type:', interception.response.body.user.type)
      }
    })

    cy.url().should('include', '/partner')
    cy.log('✅ Login completed')
  })

  it('displays order list', () => {
    cy.get('[data-test="order-list"]').should('exist')
    cy.get('[data-test="order-card"]').should('have.length.at.least', 1)
  })

  it('allows filtering orders', () => {
    cy.get('[data-test="filter-new"]').click()
    cy.get('[data-test="order-card"]').each(($el) => {
      cy.wrap($el).find('[data-test="order-status"]').should('contain', 'new')
    })
  })

  it('allows updating order status', () => {
    cy.get('[data-test="order-card"]').first().find('[data-test="update-status"]').click()
    cy.get('[data-test="status-select"]').select('preparing')
    cy.get('[data-test="confirm-update"]').click()
    cy.get('[data-test="order-status"]').should('contain', 'preparing')
  })

  it('shows order details', () => {
    cy.get('[data-test="order-card"]').first().click()
    cy.get('[data-test="order-details"]').should('exist')
    cy.get('[data-test="customer-name"]').should('exist')
    cy.get('[data-test="order-items"]').should('have.length.at.least', 1)
  })
})
