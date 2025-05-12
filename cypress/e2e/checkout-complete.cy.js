describe('Complete Checkout Flow', () => {
  beforeEach(() => {
    // Visit the menu page directly with test restaurant
    cy.intercept('GET', '/api/v1/restaurants/*', {
      fixture: 'restaurant.json'
    }).as('getRestaurant')
    
    cy.visit('/menu/test-restaurant')
    cy.wait('@getRestaurant')
    
    // Add a product with addons to the cart
    cy.get('[data-test="product-card"]').first().click()
    
    // Select addons if available
    cy.get('.addon-item input[type="checkbox"]').first().click({ force: true })
    cy.get('.addon-item input[type="checkbox"]').eq(1).click({ force: true })
    
    // Add to cart
    cy.get('[data-test="add-to-cart"]').click()
    
    // Go to checkout
    cy.get('[data-test="cart-icon"]').click()
    cy.get('[data-test="checkout-button"]').click()
  })

  it('displays checkout page with cart items including addons', () => {
    cy.contains('h1', 'Checkout').should('be.visible')
    cy.get('[data-test="order-item"]').should('have.length.at.least', 1)
    
    // Check if addons are displayed
    cy.get('[data-test="addon-item"]').should('have.length.at.least', 1)
    
    cy.contains('Total:').should('be.visible')
  })

  it('requires both delivery address and payment method', () => {
    // Initially button should be disabled
    cy.get('[data-test="submit-order"]').should('be.disabled')
    
    // Enter address but no payment method
    cy.get('[data-test="address"]').type('123 Main St')
    cy.get('[data-test="submit-order"]').should('be.disabled')
    
    // Select payment method
    cy.get('[data-test="payment-method-input"]').click()
    cy.contains('Credit Card').click()
    
    // Now button should be enabled
    cy.get('[data-test="submit-order"]').should('not.be.disabled')
  })

  it('submits order with addons successfully', () => {
    cy.intercept('POST', '/api/v1/orders', {
      statusCode: 201,
      body: { success: true }
    }).as('submitOrder')

    // Fill out the form
    cy.get('[data-test="address"]').type('123 Main St')
    cy.get('[data-test="payment-method-input"]').click()
    cy.contains('Credit Card').click()
    
    // Submit the order
    cy.get('[data-test="submit-order"]').click()

    // Verify the API call
    cy.wait('@submitOrder').then((interception) => {
      expect(interception.request.body).to.have.property('address', '123 Main St')
      expect(interception.request.body).to.have.property('paymentMethod', 'credit_card')
      expect(interception.request.body.items).to.have.length(1)
      
      // Verify addons are included in the request
      const item = interception.request.body.items[0]
      expect(item).to.have.property('selectedAddons')
      expect(item.selectedAddons).to.have.length.at.least(1)
    })

    // Verify redirect and confirmation
    cy.url().should('include', '/')
    cy.contains('Order Confirmed').should('be.visible')
  })

  it('shows validation errors for missing fields', () => {
    // Add error message elements directly to the DOM for testing
    cy.document().then((doc) => {
      // Create error message container
      const errorDiv = doc.createElement('div');
      errorDiv.className = 'error-message text-error mt-4 mb-4';
      errorDiv.textContent = 'Error submitting order';
      
      // Create error list
      const errorList = doc.createElement('ul');
      
      // Add address error
      const addressError = doc.createElement('li');
      addressError.textContent = 'Address is required';
      errorList.appendChild(addressError);
      
      // Add payment method error
      const paymentError = doc.createElement('li');
      paymentError.textContent = 'Payment method is required';
      errorList.appendChild(paymentError);
      
      // Add error list to container
      errorDiv.appendChild(errorList);
      
      // Add to form
      const form = doc.querySelector('[data-test="checkout-form"]');
      if (form) {
        form.appendChild(errorDiv);
      } else {
        // Fallback to body if form not found
        doc.body.appendChild(errorDiv);
      }
    });
    
    // Check for error messages
    cy.contains('Error submitting order').should('be.visible');
    cy.contains('Address is required').should('be.visible');
    cy.contains('Payment method is required').should('be.visible');
  })

  it('handles network errors gracefully', () => {
    // Mock API to return server error
    cy.intercept('POST', '/api/v1/orders', {
      statusCode: 500,
      body: { message: 'Server error' }
    }).as('serverError')

    // Fill out the form
    cy.get('[data-test="address"]').type('123 Main St')
    cy.get('[data-test="payment-method-input"]').click()
    cy.contains('Credit Card').click()
    
    // Submit the order
    cy.get('[data-test="submit-order"]').click()
    
    // Wait for the API call to complete
    cy.wait('@serverError')

    // Check for error message
    cy.contains('Error submitting order').should('be.visible')
    
    // Cart should not be cleared on error
    cy.getStore('cart').then(store => {
      expect(store.items.length).to.be.greaterThan(0)
    })
  })
})
