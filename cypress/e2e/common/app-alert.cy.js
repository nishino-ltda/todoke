describe('AppAlert Component', () => {
  beforeEach(() => {
    cy.visit('/test-log.html')
  })

  it('should display success alert with translated message', () => {
    cy.window().then((win) => {
      win.logStore.clear()
      win.logStore.log('🚀 Testing success alert')
      
      const alert = win.document.createElement('app-alert')
      alert.setAttribute('type', 'success')
      alert.setAttribute('message', 'common.alert.success')
      win.document.body.appendChild(alert)

      cy.get('app-alert')
        .should('be.visible')
        .should('contain', 'Success') // Default English text
        .should('have.class', 'alert-success')
    })
  })

  it('should display error alert with translated message', () => {
    cy.window().then((win) => {
      win.logStore.clear()
      win.logStore.log('🚨 Testing error alert')
      
      const alert = win.document.createElement('app-alert')
      alert.setAttribute('type', 'error')
      alert.setAttribute('message', 'common.alert.error')
      win.document.body.appendChild(alert)

      cy.get('app-alert')
        .should('be.visible')
        .should('contain', 'Error') // Default English text
        .should('have.class', 'alert-error')
    })
  })

  it('should auto-dismiss after timeout', () => {
    cy.window().then((win) => {
      win.logStore.clear()
      win.logStore.log('⏱️ Testing auto-dismiss')
      
      const alert = win.document.createElement('app-alert')
      alert.setAttribute('type', 'info')
      alert.setAttribute('message', 'common.alert.info')
      alert.setAttribute('timeout', '1000')
      win.document.body.appendChild(alert)

      cy.get('app-alert')
        .should('be.visible')
        .wait(1100)
        .should('not.exist')
    })
  })

  it('should persist when no timeout is set', () => {
    cy.window().then((win) => {
      win.logStore.clear()
      win.logStore.log('🕰️ Testing persistent alert')
      
      const alert = win.document.createElement('app-alert')
      alert.setAttribute('type', 'warning')
      alert.setAttribute('message', 'common.alert.warning')
      win.document.body.appendChild(alert)

      cy.get('app-alert')
        .should('be.visible')
        .wait(2000)
        .should('be.visible')
    })
  })
})
