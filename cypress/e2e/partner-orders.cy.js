describe('Partner Order Management', () => {
  beforeEach(() => {
    cy.loginAsPartner()
    cy.visit('/partner/dashboard')
  })

  it('displays order list', () => {
    cy.get('[data-test="order-list"]').should('exist')
    cy.get('[data-test="order-card"]').should('have.length.at.least', 1)
  })

  it('filters orders by status', () => {
    cy.get('[data-test="filter-preparing"]').click()
    cy.get('[data-test="order-card"]').each($card => {
      expect($card).to.contain('PREPARING')
    })

    cy.get('[data-test="filter-ready"]').click()
    cy.get('[data-test="order-card"]').each($card => {
      expect($card).to.contain('READY')
    })
  })

  it('updates order status', () => {
    cy.get('[data-test="order-card"]').first().as('firstOrder')
    cy.get('@firstOrder').find('[data-test="status-badge"]').should('contain', 'PREPARING')
    cy.get('@firstOrder').find('[data-test="mark-ready-btn"]').click()
    cy.get('@firstOrder').find('[data-test="status-badge"]').should('contain', 'READY')
  })

  it('shows real-time updates', () => {
    cy.get('[data-test="order-card"]').should('have.length', 3)
    cy.waitForNewOrder()
    cy.get('[data-test="order-card"]').should('have.length', 4)
  })
})
