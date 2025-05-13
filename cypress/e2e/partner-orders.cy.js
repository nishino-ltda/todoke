describe('Partner Order Management', () => {
  beforeEach(() => {
    cy.intercept('GET', '**/partner/orders').as('getOrders')
    
    cy.log('🔐 Starting partner login flow')
    cy.visit('/login')
    
    cy.log('📝 Filling login form with test credentials')
    cy.get('[data-test="email-input"]').type('partner@example.com')
    cy.get('[data-test="password-input"]').type('password123')
    
    cy.log('🔄 Submitting login form')
    cy.get('[data-test="submit-button"]').click()
    
    cy.log('⏳ Waiting for login to complete')
    
    // First get CSRF token from meta tag
    cy.document().then((doc) => {
      const csrfToken = doc.querySelector('meta[name="csrf-token"]')?.content
      
      cy.request({
        method: 'POST',
        url: '/api/v1/auth/login',
        failOnStatusCode: false,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken || '',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: {
          email: 'partner@example.com',
          password: 'password123'
        }
      }).then((response) => {
        cy.log('🔍 Login API Response:', {
          status: response.status,
          statusText: response.statusText,
          headers: response.headers,
          body: response.body
        })
        
        if (response.status === 200 && response.body.token) {
          cy.window().then((win) => {
            win.localStorage.setItem('token', response.body.token)
            cy.log('✅ Login successful - Token:', response.body.token.slice(0, 10) + '...')
          })
        } else {
          throw new Error(`Login failed with status ${response.status}: ${JSON.stringify(response.body)}`)
        }
      })
    })
    
    cy.url().should('include', '/partner')
    
    cy.log('🧪 Setting up test orders')
    cy.window().then((win) => {
      const token = win.localStorage.getItem('token')
      cy.request({
        method: 'POST',
        url: '/api/v1/test/setup-orders',
        headers: {
          'Authorization': `Bearer ${token}`
        },
        body: {
          partnerId: 1,
          orders: [
            { status: 'preparing', items: ['Margherita Pizza'] },
            { status: 'preparing', items: ['Caesar Salad'] },
            { status: 'ready', items: ['Pepperoni Pizza'] }
          ]
        }
      }).then(response => {
        cy.log('✅ Test orders created:', response.body.count)
      })
    })
    
    cy.log('🔄 Reloading page to refresh orders')
    cy.reload()
    
    cy.log('⏳ Waiting for orders to load')
    cy.wait('@getOrders', { timeout: 10000 }).then(interception => {
      cy.log('📋 Orders loaded - status:', interception.response?.statusCode)
    })
  })

  it('displays order list', () => {
    cy.log('🔍 Checking for order list')
    cy.get('[data-test="order-list"]', { timeout: 10000 }).should('exist')
    cy.log('📋 Verifying order cards exist')
    cy.get('[data-test="order-card"]', { timeout: 10000 }).should('have.length.at.least', 1)
    cy.log('✅ Order list displayed correctly')
  })

  it('allows filtering orders', () => {
    cy.log('🔍 Testing order filtering')
    cy.get('[data-test="filter-new"]', { timeout: 10000 }).should('exist').then($btn => {
      cy.log('🖱️ Clicking filter button')
      cy.wrap($btn).click()
      
      cy.log('⏳ Waiting for filtered results')
      cy.waitForNewOrder()
      
      cy.log('🔍 Verifying filtered orders')
      cy.get('[data-test="order-card"]').each(($el) => {
        cy.wrap($el).find('[data-test="order-status"]').should('contain', 'new')
      })
      
      cy.log('✅ Order filtering works correctly')
    })
  })

  it('allows updating order status', () => {
    cy.log('🔄 Testing order status update')
    cy.get('[data-test="order-card"]', { timeout: 10000 }).first().then($card => {
      cy.log('🔍 Finding update status button')
      cy.wrap($card).find('[data-test="update-status"]').should('exist').click()
      
      cy.log('📝 Selecting new status')
      cy.get('[data-test="status-select"]').should('exist').select('preparing')
      
      cy.log('✅ Confirming status update')
      cy.get('[data-test="confirm-update"]').should('exist').click()
      
      // Intercept the status update request
      cy.intercept('PATCH', '/api/v1/orders/*/status').as('statusUpdate')
      cy.wait('@statusUpdate').then(() => {
        cy.log('🔍 Verifying status was updated')
        cy.get('[data-test="order-status"]').should('contain', 'preparing')
        cy.log('✅ Order status updated successfully')
      })
    })
  })

  it('shows order details', () => {
    cy.log('🔍 Testing order details view')
    cy.get('[data-test="order-card"]', { timeout: 10000 }).first().then($card => {
      cy.log('🖱️ Clicking on order card')
      cy.wrap($card).click()
      
      cy.log('⏳ Waiting for details to load')
      cy.get('[data-test="order-details"]', { timeout: 10000 }).should('exist')
      
      cy.log('🔍 Verifying customer information')
      cy.get('[data-test="customer-name"]').should('exist')
      
      cy.log('🔍 Verifying order items')
      cy.get('[data-test="order-items"]').should('have.length.at.least', 1)
      
      cy.log('✅ Order details displayed correctly')
    })
  })
})
