describe('🛒 Checkout Flow', () => {
  const mockPartner = {
    id: 1,
    name: 'Test Restaurant',
    slug: 'test-restaurant',
    type: 'Restaurant'
  };

  const mockProducts = [
    {
      id: 1,
      name: 'Pizza Margherita',
      price: 10.0,
      description: 'Fresh mozzarella, tomatoes, basil',
      addons: [
        { id: 101, name: 'Extra Cheese', price: 2.0 },
        { id: 102, name: 'Mushrooms', price: 1.5 }
      ]
    }
  ];

  beforeEach(() => {
    // Intercept essential APIs
    cy.intercept('GET', '**/api/v1/partners/test-restaurant', {
      statusCode: 200,
      body: {
        partner: mockPartner,
        products: mockProducts
      }
    }).as('getPartner');

    cy.intercept('POST', '**/api/v1/orders', {
      statusCode: 201,
      body: { id: 123, status: 'pending' }
    }).as('submitOrder');

    cy.intercept('GET', '**/api/v1/map/geocode*', {
      statusCode: 200,
      body: [{ address: 'Test Address 123', lat: -23.5505, lng: -46.6333 }]
    }).as('geocode');

    // Mock auth check
    cy.intercept('GET', '**/api/v1/users/me', {
      statusCode: 200,
      body: { id: 1, name: 'Test Customer', email: 'customer@todoke.test', role: 'customer' }
    }).as('getMe');

    // Clear state
    cy.window().then((win) => {
      win.localStorage.clear();
    });
  });

  it('📦 Should add items to cart and update quantities', () => {
    cy.visit('/menu/test-restaurant');
    cy.wait('@getPartner');

    // Click on product card to open modal
    cy.get('[data-cy="product-card"]').first().click();

    // Verify modal is open
    cy.get('[data-cy="add-to-cart"]').should('be.visible');

    // Increase quantity
    cy.get('[data-cy="increase-quantity"]').click();
    cy.get('[data-cy="quantity-display"]').should('contain', '2');

    // Add to cart
    cy.get('[data-cy="add-to-cart"]').click();

    // Verify cart count in badge
    cy.get('[data-cy="cart-icon"]').should('contain', '2');
  });

  it('🏠 Should handle address selection with geocoding', () => {
    cy.login('customer@todoke.test', 'password123');
    
    // Add item to cart
    cy.visit('/menu/test-restaurant');
    cy.get('[data-cy="product-card"]').first().click();
    cy.get('[data-cy="add-to-cart"]').click();

    // Go to checkout
    cy.visit('/customer/checkout');

    // Type address and select from combobox
    cy.get('[data-cy="address-combobox"]').find('input').clear().type('Test Address', { delay: 100 });
    cy.wait('@geocode');
    
    // Wait for the menu to appear and select the first item
    cy.get('.v-overlay-container').should('be.visible');
    cy.get('.v-list-item').first().click();

    // Verify value was set
    cy.get('[data-cy="address-combobox"]').find('input').should('have.value', 'Test Address 123');
  });

  it('💳 Should handle payment method selection', () => {
    cy.login('customer@todoke.test', 'password123');
    
    // Add item and go to checkout
    cy.visit('/menu/test-restaurant');
    cy.get('[data-cy="product-card"]').first().click();
    cy.get('[data-cy="add-to-cart"]').click();
    cy.visit('/customer/checkout');

    // Default should be cash (based on component logic)
    cy.get('[data-cy="payment-method-select"]').should('contain', 'Cash');

    // Change to Credit Card
    cy.get('[data-cy="payment-method-select"]').click();
    cy.get('.v-list-item').contains('Credit Card').click();

    // Verify CC fields appear
    cy.get('[data-cy="card-number-input"]').should('be.visible');
    cy.get('[data-cy="card-expiry-input"]').should('be.visible');
    cy.get('[data-cy="card-cvv-input"]').should('be.visible');
    cy.get('[data-cy="card-holder-input"]').should('be.visible');
  });

  it('➕ Should calculate totals correctly with addons', () => {
    cy.visit('/menu/test-restaurant');
    cy.wait('@getPartner');

    // Open modal
    cy.get('[data-cy="product-card"]').first().click();

    // Base price $10.00. Add "Extra Cheese" (+$2.00)
    cy.get('[data-cy="addon-checkbox-101"]').click();
    
    // Total in button should be $12.00
    cy.get('[data-cy="add-to-cart"]').should('contain', '$12.00');

    // Add to cart
    cy.get('[data-cy="add-to-cart"]').click();

    // Check cart summary in checkout
    cy.login('customer@todoke.test', 'password123');
    cy.visit('/customer/checkout');
    
    // Subtotal $12.00 + Delivery Fee $5.00 = $17.00
    cy.get('[data-cy="checkout-order-summary"]').within(() => {
      cy.contains('R$ 12.00'); // Subtotal (formatted as R$)
      cy.contains('R$ 17.00'); // Total
    });
  });

  it('✅ Should successfully place an order', () => {
    cy.login('customer@todoke.test', 'password123');
    
    // Add item
    cy.visit('/menu/test-restaurant');
    cy.get('[data-cy="product-card"]').first().click();
    cy.get('[data-cy="add-to-cart"]').click();

    // Checkout
    cy.visit('/customer/checkout');

    // Fill details
    cy.get('[data-cy="address-combobox"]').find('input').type('Test Address 123');
    cy.get('[data-cy="payment-method-select"]').click();
    cy.get('.v-list-item').contains('Cash').click();

    // Submit
    cy.get('[data-cy="submit-order"]').click();

    // Verify API call
    cy.wait('@submitOrder').then((interception) => {
      expect(interception.request.body.partner_id).to.equal(1);
      expect(interception.request.body.items[0].product_id).to.equal(1);
    });

    // Verify redirect to confirmation
    cy.url().should('include', '/customer/orders/123');
    
    // Verify cart is cleared
    cy.get('[data-cy="cart-icon"]').should('not.contain', '1');
  });

  it('❌ Should handle checkout error cases', () => {
    cy.login('customer@todoke.test', 'password123');
    
    // Mock validation error
    cy.intercept('POST', '/api/v1/orders', {
      statusCode: 422,
      body: { message: 'The selected delivery address is invalid.' }
    }).as('submitOrderError');

    // Add item and go to checkout
    cy.visit('/menu/test-restaurant');
    cy.get('[data-cy="product-card"]').first().click();
    cy.get('[data-cy="add-to-cart"]').click();
    cy.visit('/customer/checkout');

    // Fill and submit
    cy.get('[data-cy="address-combobox"]').find('input').type('Invalid Address');
    cy.get('[data-cy="submit-order"]').click();

    // Verify error message
    cy.wait('@submitOrderError');
    cy.get('[data-cy="checkout-form-error"]').should('contain', 'The selected delivery address is invalid.');
  });
});
