describe('🍽️ Partner Order Management', () => {
  const mockOrders = [
    {
      id: 1,
      customer_name: 'John Doe',
      customer_phone: '(11) 98888-7777',
      delivery_address: 'Rua Teste, 123',
      total: 50.00,
      subtotal: 45.00,
      delivery_fee: 5.00,
      status: 'pending',
      payment_method: 'Credit Card',
      items: [
        { id: 1, product_name: 'Pizza Margherita', quantity: 2, price: 22.50, addons: [] }
      ]
    },
    {
      id: 2,
      customer_name: 'Jane Smith',
      customer_phone: '(11) 91111-2222',
      delivery_address: 'Av. Paulista, 1000',
      total: 35.00,
      subtotal: 30.00,
      delivery_fee: 5.00,
      status: 'preparing',
      payment_method: 'Cash',
      items: [
        { id: 2, product_name: 'Burger Deluxe', quantity: 1, price: 30.00, addons: [] }
      ]
    }
  ];

  beforeEach(() => {
    cy.intercept('GET', '**/api/v1/partner/orders', {
      statusCode: 200,
      body: mockOrders
    }).as('getOrders');
    
    cy.intercept('GET', '**/api/v1/partner/orders/1', {
      statusCode: 200,
      body: mockOrders[0]
    }).as('getOrder');
    
    cy.intercept('PATCH', '**/api/v1/partner/orders/*/status', {
      statusCode: 200,
      body: { success: true }
    }).as('updateStatus');
    
    cy.intercept('POST', '**/api/v1/deliveries', {
      statusCode: 201,
      body: { success: true }
    }).as('requestCourier');

    cy.visit('/login');
    cy.get('[data-cy="email-input"] input').type('partner@todoke.test');
    cy.get('[data-cy="password-input"] input').type('password123');
    cy.get('[data-cy="login-button"]').click();
    cy.url().should('include', '/partner/dashboard');
  });

  it('🌱 Should display a list of current orders', () => {
    cy.visit('/partner/orders');
    cy.wait('@getOrders', { timeout: 10000 });
    cy.get('[data-cy="partner-orders-table"]').should('be.visible');
    cy.get('[data-cy="partner-orders-table"] tbody tr').should('have.length', 2);
  });

  it('✏️ Should update order status', () => {
    cy.log('Step: Visiting orders page');
    cy.visit('/partner/orders');
    cy.wait('@getOrders', { timeout: 10000 });
    
    cy.log('Step: Clicking accept button');
    cy.get('[data-cy="accept-order-btn"]').first().should('be.visible').click();
    
    cy.log('Step: Waiting for API response');
    cy.wait('@updateStatus', { timeout: 10000 }).its('response.statusCode').should('equal', 200);
    
    cy.log('Step: Verifying notification');
    cy.get('[data-cy="app-alert"]').should('be.visible');
  });

  it('👁️ Should view order details', () => {
    cy.log('Step: Visiting orders page');
    cy.visit('/partner/orders');
    cy.wait('@getOrders', { timeout: 10000 });
    
    cy.log('Step: Clicking view details button');
    cy.get('[data-cy="view-order-btn"]').first().should('be.visible').click();
    
    cy.log('Step: Waiting for single order API');
    cy.wait('@getOrder', { timeout: 10000 });
    cy.url().should('include', '/partner/orders/1');
    cy.get('[data-cy="partner-order-show"]').should('be.visible');
    cy.get('[data-cy="order-items-table"]').should('be.visible');
  });

  it('🖨️ Should print customer address labels', () => {
    cy.visit('/partner/orders');
    cy.wait('@getOrders', { timeout: 10000 });
    cy.get('[data-cy="view-order-btn"]').first().should('be.visible').click();
    cy.wait('@getOrder', { timeout: 10000 });
    
    // Mock window.print
    cy.window().then((win) => {
      cy.stub(win, 'print').as('printStub');
    });

    cy.log('Step: Clicking print button');
    cy.get('[data-cy="print-label-btn"]').should('be.visible').click();
    cy.get('@printStub').should('be.calledOnce');
  });

  it('🛵 Should request a courier or drone for pickup', () => {
    cy.visit('/partner/orders');
    cy.wait('@getOrders', { timeout: 10000 });
    cy.get('[data-cy="view-order-btn"]').first().should('be.visible').click();
    cy.wait('@getOrder', { timeout: 10000 });

    cy.log('Step: Clicking request courier button');
    cy.get('[data-cy="request-courier-btn"]').should('be.visible').click();
    cy.wait('@requestCourier', { timeout: 10000 }).its('response.statusCode').should('equal', 201);
    
    cy.log('Step: Verifying notification');
    cy.get('[data-cy="app-alert"]').should('be.visible').and('contain', 'solicitado');
  });
});
