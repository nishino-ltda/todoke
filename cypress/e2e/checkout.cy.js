describe('Checkout Flow', () => {
  beforeEach(() => {
    cy.visit('/')
    cy.get('[data-test="product-card"]').first().click()
    cy.get('[data-test="add-to-cart"]').click()
    cy.get('[data-test="cart-icon"]').click()
    cy.get('[data-test="checkout-button"]').click()
  })

  it('displays checkout page with cart items', () => {
    cy.contains('h1', 'Checkout').should('be.visible')
    cy.get('[data-test="order-item"]').should('have.length.at.least', 1)
    cy.contains('Total:').should('be.visible')
  })

  it('requires delivery address', () => {
    cy.get('[data-test="submit-order"]').should('be.disabled')
    cy.get('[data-test="address"]').type('123 Main St')
    cy.get('[data-test="submit-order"]').should('not.be.disabled')
  })

  it('submits order successfully', () => {
    cy.intercept('POST', '/api/orders', {
      statusCode: 201,
      body: { success: true }
    }).as('submitOrder')

    cy.get('[data-test="address"]').type('123 Main St')
    cy.get('[data-test="submit-order"]').click()

    cy.wait('@submitOrder').then((interception) => {
      expect(interception.request.body).to.have.property('address', '123 Main St')
      expect(interception.request.body.items).to.have.length(1)
    })

    cy.url().should('include', '/')
    cy.contains('Order submitted successfully').should('be.visible')
  })
})
