describe('Menu Browsing Flow', () => {
  beforeEach(() => {
    cy.intercept('GET', '/api/v1/restaurants/*', {
      fixture: 'restaurant.json'
    }).as('getRestaurant')
    
    cy.visit('/menu/test-restaurant')
    cy.wait('@getRestaurant')
  })

  it('should display restaurant products', () => {
    cy.log('🔍 Starting restaurant products display test')
    cy.get('[data-test="product-list-container"]').should('exist')
    cy.log('✅ Found product list container')
    cy.get('[data-test="product-card"]').then(($cards) => {
      cy.log(`🔍 Found ${$cards.length} product cards`)
      $cards.each((index, card) => {
        cy.log(`🔍 Product ${index + 1}: ${card.textContent}`)
      })
    }).should('have.length.at.least', 1)
    cy.log('✅ Completed restaurant products display test')
  })

  it('should allow searching products', () => {
    cy.log('🔍 Starting product search test')
    const searchTerm = 'Pizza'
    cy.log(`🔍 Searching for: ${searchTerm}`)
    cy.get('[data-test="product-search"]').type(searchTerm)
    cy.get('[data-test="product-card"]').then(($cards) => {
      cy.log(`🔍 Found ${$cards.length} matching products`)
    }).should('have.length.at.least', 1)
    cy.get('[data-test="product-name"]').first().then(($name) => {
      cy.log(`✅ First product name: ${$name.text()}`)
    }).should('contain', 'Pizza')
    cy.log('✅ Completed product search test')
  })

  it('should open product details modal', () => {
    cy.log('🔍 Starting product details modal test')
    cy.get('[data-test="product-card"]').first().then(($card) => {
      cy.log(`🚀 Clicking product: ${$card.text()}`)
    }).click()
    cy.get('.product-details-modal').then(($modal) => {
      cy.log(`🔍 Modal content: ${$modal.text()}`)
    }).should('be.visible')
    cy.get('[data-test="add-to-cart"]').should('exist')
    cy.log('✅ Completed product details modal test')
  })

  it('should add product to cart', () => {
    cy.log('🚀 Starting add to cart test')
    cy.get('[data-test="product-card"]').first().click()
    cy.log('🚀 Clicked product details')
    cy.get('[data-test="add-to-cart"]').click()
    cy.log('🚀 Clicked add to cart button')
    
    // Verify cart store was updated
    cy.getStore('cart').then(store => {
      cy.log(`✅ Cart contains ${store.items.length} items`)
      expect(store.items.length).to.eq(1)
      cy.log(`🔍 First item in cart: ${JSON.stringify(store.items[0])}`)
    })
    cy.log('✅ Completed add to cart test')
  })

  it('should persist cart between page reloads', () => {
    cy.log('🔍 Starting cart persistence test')
    cy.get('[data-test="product-card"]').first().click()
    cy.get('[data-test="add-to-cart"]').click()
    cy.getStore('cart').then(store => {
      cy.log(`🔍 Initial cart state: ${JSON.stringify(store.items)}`)
    })
    cy.log('🚀 Reloading page...')
    cy.reload()
    
    cy.getStore('cart').then(store => {
      cy.log(`✅ Cart after reload: ${JSON.stringify(store.items)}`)
      expect(store.items.length).to.eq(1)
    })
    cy.log('✅ Completed cart persistence test')
  })
})
