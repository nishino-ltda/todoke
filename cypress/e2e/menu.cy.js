describe('Menu Browsing Flow', () => {
  beforeEach(() => {
    cy.intercept('GET', '/api/v1/restaurants/*', {
      fixture: 'restaurant.json'
    }).as('getRestaurant')
    
    cy.visit('/menu/test-restaurant')
    cy.wait('@getRestaurant')
  })

  it('should display restaurant products', () => {
    cy.get('[data-test="product-list-container"]').should('exist')
    cy.get('[data-test="product-card"]').should('have.length.at.least', 1)
  })

  it('should allow searching products', () => {
    cy.get('[data-test="product-search"]').type('Pizza')
    cy.get('[data-test="product-card"]').should('have.length.at.least', 1)
    cy.get('[data-test="product-name"]').first().should('contain', 'Pizza')
  })

  it('should open product details modal', () => {
    cy.get('[data-test="product-card"]').first().click()
    cy.get('.product-details-modal').should('be.visible')
    cy.get('[data-test="add-to-cart"]').should('exist')
  })

  it('should add product to cart', () => {
    cy.get('[data-test="product-card"]').first().click()
    cy.get('[data-test="add-to-cart"]').click()
    
    // Verify cart store was updated
    cy.getStore('cart').then(store => {
      expect(store.items.length).to.eq(1)
    })
  })

  it('should persist cart between page reloads', () => {
    cy.get('[data-test="product-card"]').first().click()
    cy.get('[data-test="add-to-cart"]').click()
    cy.reload()
    
    cy.getStore('cart').then(store => {
      expect(store.items.length).to.eq(1)
    })
  })
})
