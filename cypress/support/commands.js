// Pinia store access command
Cypress.Commands.add('getStore', (storeName) => {
  return cy.window().then(win => {
    const app = win.document.querySelector('#app').__vue_app__
    return app.config.globalProperties.$pinia._s.get(storeName)
  })
})

Cypress.Commands.add('login', (email, password) => {
  // TODO: Implement login command
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
