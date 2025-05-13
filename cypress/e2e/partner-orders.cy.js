describe('Partner Order Management', () => {
  beforeEach(() => {
    // Login as partner first
    cy.visit('/login')
    cy.get('[data-test="email-input"]').type('partner@example.com')
    cy.get('[data-test="password-input"]').type('password123')
    cy.get('[data-test="submit-button"]').click()
    cy.url().should('include', '/partner')
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
