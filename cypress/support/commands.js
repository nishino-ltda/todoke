import 'cypress-file-upload';

// Pinia store access command with test support
Cypress.Commands.add('getStore', (storeName) => {
  return cy.window().then(win => {
    try {
      // First try to use the pre-initialized store
      if (storeName === 'log' && win.logStore) {
        return win.logStore;
      }

      // Then try to use the test Pinia instance
      if (win.pinia) {
        const store = win.pinia._s.get(storeName);
        if (store) return store;
      }

      // Fallback to app Pinia instance
      const appElement = win.document.querySelector('#app');
      if (appElement?.__vue_app__) {
        const pinia = appElement.__vue_app__.config.globalProperties.$pinia;
        if (pinia) {
          const store = pinia._s.get(storeName);
          if (store) return store;
        }
      }

      // Final error with available stores
      const pinia = win.pinia || appElement?.__vue_app__?.config.globalProperties.$pinia;
      const availableStores = pinia ? Array.from(pinia._s.keys()).join(', ') : 'none';
      throw new Error(`Store "${storeName}" not found. Available stores: ${availableStores}`);
    } catch (err) {
      console.error('Error accessing store:', err);
      throw err;
    }
  });
});

Cypress.Commands.add('login', (email, password) => {
  cy.visit('/login')
  cy.get('[data-cy="email-input"]').type(email)
  cy.get('[data-cy="password-input"]').type(password)
  cy.get('[data-cy*="submit-btn"]').click()
})

Cypress.Commands.add('loginAsPartner', () => {
  cy.visit('/partner/login')
  cy.get('[data-cy="email-input"]').type('partner@example.com')
  cy.get('[data-cy="password-input"]').type('password123')
  cy.get('[data-cy*="submit-btn"]').click()
})

Cypress.Commands.add('loginAsCourier', () => {
  cy.visit('/login')
  cy.get('[data-cy="email-input"]').type('courier@example.com')
  cy.get('[data-cy="password-input"]').type('password123')
  cy.get('[data-cy*="submit-btn"]').click()
})

Cypress.Commands.add('setupTestOrders', () => {
  cy.log('🔄 Setting up test orders via API')
  cy.request({
    method: 'POST',
    url: '/api/v1/test/setup-orders',
    body: {
      partnerId: 1,
      orders: [
        { status: 'preparing', items: ['Margherita Pizza'] },
        { status: 'preparing', items: ['Caesar Salad'] },
        { status: 'ready', items: ['Pepperoni Pizza'] }
      ]
    },
    failOnStatusCode: false
  }).then(response => {
    if (response.status !== 200) {
      cy.log('❌ Error setting up test orders:', response.body)
    } else {
      cy.log('✅ Test orders created successfully:', response.body.count)
    }
  })
})

Cypress.Commands.add('waitForNewOrder', () => {
  cy.log('⏳ Waiting for orders API response')
  cy.intercept('GET', '**/partner/orders').as('getOrdersRefresh')
  cy.wait('@getOrdersRefresh', { timeout: 10000 }).then(interception => {
    cy.log('📋 Orders refreshed:', interception.response?.body)
  })
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

/**
 * Dumps application logs to Cypress output with enhanced formatting
 * @param {boolean} clearAfter - Whether to clear logs after dumping (default: true)
 * @param {string} title - Optional title for the log section
 */
Cypress.Commands.add('dumpLogs', (clearAfter = true, title = 'Application logs') => {
  cy.window().then(win => {
    if (win.logStore) {
      const logs = win.logStore.getLogs();
      if (logs && logs.length > 0) {
        // Start with a separator and title
        cy.log(`\n📜📜📜 ${title.toUpperCase()} 📜📜📜`);
        
        // Group logs by type with emoji prefixes
        const typeEmojis = {
          error: '❌',
          warn: '⚠️', 
          info: 'ℹ️',
          debug: '🐛',
          default: '📝'
        };

        logs.forEach(log => {
          const emoji = typeEmojis[log.type] || typeEmojis.default;
          const timestamp = new Date(log.timestamp).toLocaleTimeString();
          cy.log(`${emoji} [${timestamp}] ${log.message}`);
          
          // Include stack trace if available
          if (log.stack) {
            cy.log(`🔍 Stack: ${log.stack}`);
          }
        });

        // End with summary
        cy.log(`📊 Total logs: ${logs.length}`);
        
        if (clearAfter) {
          win.logStore.clear();
          cy.log('🧹 Logs cleared');
        }
      } else {
        cy.log('🕳️ No application logs found');
      }
    } else {
      cy.log('🚨 Log store not available in window');
    }
  });
});
