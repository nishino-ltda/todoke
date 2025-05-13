// Pinia store access command
Cypress.Commands.add('getStore', (storeName) => {
  return cy.window().then(win => {
    const app = win.document.querySelector('#app').__vue_app__
    return app.config.globalProperties.$pinia._s.get(storeName)
  })
})

Cypress.Commands.add('login', (email, password) => {
  cy.visit('/login')
  cy.get('[data-test="email-input"]').type(email)
  cy.get('[data-test="password-input"]').type(password)
  cy.get('[data-test="submit-btn"]').click()
})

Cypress.Commands.add('loginAsPartner', () => {
  cy.visit('/partner/login')
  cy.get('[data-test="email-input"]').type('partner@example.com')
  cy.get('[data-test="password-input"]').type('password123')
  cy.get('[data-test="submit-btn"]').click()
})

Cypress.Commands.add('setupTestOrders', () => {
  cy.request('POST', '/api/v1/test/setup-orders', {
    partnerId: 1,
    orders: [
      { status: 'PREPARING', items: ['Margherita Pizza'] },
      { status: 'PREPARING', items: ['Caesar Salad'] },
      { status: 'READY', items: ['Pepperoni Pizza'] }
    ]
  })
})

Cypress.Commands.add('waitForNewOrder', () => {
  cy.intercept('GET', '/api/v1/partner/orders').as('getOrders')
  cy.wait('@getOrders')
})

Cypress.Commands.add('logout', () => {
  // Use the auth store's logout method if available
  cy.getStore('auth').then((store) => {
    if (store && store.logout) {
      store.logout()
    } else {
      // Fallback to manual logout
      cy.window().then((win) => {
        win.localStorage.removeItem('token')
        win.localStorage.removeItem('user')
      })
      cy.clearCookies()
    }
  })
  
  // Ensure we're on login page
  cy.visit('/login')
})

Cypress.Commands.overwrite("log", function(log, ...args) {
  const indent = "\t";
  const formattedArgs = args.map((arg) =>
        typeof arg === "string" ? indent + arg : indent + JSON.stringify(arg)
  );
  if (Cypress.browser.isHeadless) {
    return cy.task("log", formattedArgs, { log: false }).then(() => {
      return log(...args);
    });
  } else {
    console.log(...formattedArgs);
    return log(...args);
  }
});
