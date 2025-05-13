describe('Checkout Flow', () => {
  beforeEach(() => {
    // Visit the menu page directly with test restaurant
    cy.intercept('GET', '/api/v1/restaurants/*', {
      fixture: 'restaurant.json'
    }).as('getRestaurant')
    
    cy.visit('/menu/test-restaurant')
    cy.wait('@getRestaurant')
    
    cy.get('[data-test="product-card"]').first().click()
    cy.get('[data-test="add-to-cart"]').click()
    cy.get('[data-test="cart-icon"]').click()
    cy.get('[data-test="checkout-button"]').click()
  })

  it('displays checkout page with cart items', () => {
    cy.log('🔍 Starting checkout page display test')
    cy.contains('h1', 'Checkout').should('be.visible')
    cy.log('✅ Found checkout heading')
    cy.get('[data-test="order-item"]').then(($items) => {
      cy.log(`🔍 Found ${$items.length} order items`)
      $items.each((index, item) => {
        cy.log(`🔍 Item ${index + 1}: ${item.textContent}`)
      })
    }).should('have.length.at.least', 1)
    cy.contains('Total:').then(($total) => {
      cy.log(`✅ Order total: ${$total.text()}`)
    }).should('be.visible')
    cy.log('✅ Completed checkout page display test')
  })

  it('requires delivery address', () => {
    cy.log('🔍 Starting form validation test')
    cy.get('[data-test="submit-order"]').should('be.disabled')
    cy.log('✅ Verified submit button is initially disabled')
    const testAddress = '123 Main St'
    cy.log(`🚀 Entering address: ${testAddress}`)
    cy.get('[data-test="address"]').type(testAddress)
    cy.get('[data-test="payment-method-input"]').click()
    cy.contains('Credit Card').click()
    cy.log('🚀 Selected payment method: Credit Card')
    cy.get('[data-test="submit-order"]').should('not.be.disabled')
    cy.log('✅ Verified submit button is now enabled')
    cy.log('✅ Completed form validation test')
  })

  it('submits order successfully', () => {
    cy.log('🚀 Starting order submission test')
    cy.intercept('POST', '/api/v1/orders', {
      statusCode: 201,
      body: { success: true }
    }).as('submitOrder')

    const testAddress = '123 Main St'
    cy.log(`🚀 Entering address: ${testAddress}`)
    cy.get('[data-test="address"]').type(testAddress)
    cy.get('[data-test="payment-method-input"]').click()
    cy.contains('Credit Card').click()
    cy.log('🚀 Selected payment method: Credit Card')
    cy.get('[data-test="submit-order"]').click()
    cy.log('🚀 Submitted order')

    cy.wait('@submitOrder').then((interception) => {
      cy.log('✅ Received API response')
      cy.log(`Request payload: ${JSON.stringify(interception.request.body)}`)
      expect(interception.request.body).to.have.property('address', testAddress)
      expect(interception.request.body).to.have.property('paymentMethod', 'credit_card')
      expect(interception.request.body.items).to.have.length(1)
      cy.log(`✅ Order contains ${interception.request.body.items.length} items`)
    })

    cy.contains('Order Confirmed').then(($confirmation) => {
      cy.log(`✅ Confirmation message: ${$confirmation.text()}`)
    }).should('be.visible')
    cy.log('✅ Completed order submission test')
  })
})
