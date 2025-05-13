describe('Partner Order Management', () => {
  beforeEach(() => {
    cy.loginAsPartner()
    cy.visit('/partner/dashboard')
  })

  it('displays order list', () => {
    cy.log('🔍 Starting order list display test')
    cy.get('[data-test="order-list"]').should('exist')
    cy.log('✅ Found order list container')
    cy.get('[data-test="order-card"]').then(($cards) => {
      cy.log(`🔍 Found ${$cards.length} order cards`)
      $cards.each((index, card) => {
        cy.log(`🔍 Order ${index + 1} status: ${card.textContent}`)
      })
    }).should('have.length.at.least', 1)
    cy.log('✅ Completed order list display test')
  })

  it('filters orders by status', () => {
    cy.log('🔍 Starting order filtering test')
    cy.log('🚀 Filtering for PREPARING orders')
    cy.get('[data-test="filter-preparing"]').click()
    cy.get('[data-test="order-card"]').each($card => {
      cy.log(`🔍 Order card content: ${$card.text()}`)
      expect($card).to.contain('PREPARING')
    })

    cy.log('🚀 Filtering for READY orders')
    cy.get('[data-test="filter-ready"]').click()
    cy.get('[data-test="order-card"]').each($card => {
      cy.log(`Order card content: ${$card.text()}`)
      expect($card).to.contain('READY')
    })
    cy.log('✅ Completed order filtering test')
  })

  it('updates order status', () => {
    cy.log('🔍 Starting order status update test')
    cy.get('[data-test="order-card"]').first().as('firstOrder')
    cy.get('@firstOrder').find('[data-test="status-badge"]').then(($badge) => {
      cy.log(`🔍 Initial status: ${$badge.text()}`)
    }).should('contain', 'PREPARING')
    cy.log('🚀 Marking order as READY')
    cy.get('@firstOrder').find('[data-test="mark-ready-btn"]').click()
    cy.get('@firstOrder').find('[data-test="status-badge"]').then(($badge) => {
      cy.log(`✅ Updated status: ${$badge.text()}`)
    }).should('contain', 'READY')
    cy.log('✅ Completed order status update test')
  })

  it('shows real-time updates', () => {
    cy.log('🔍 Starting real-time updates test')
    cy.get('[data-test="order-card"]').then(($cards) => {
      cy.log(`🔍 Initial order count: ${$cards.length}`)
    }).should('have.length', 3)
    cy.log('🚀 Waiting for new order...')
    cy.waitForNewOrder()
    cy.get('[data-test="order-card"]').then(($cards) => {
      cy.log(`✅ Updated order count: ${$cards.length}`)
    }).should('have.length', 4)
    cy.log('✅ Completed real-time updates test')
  })
})
