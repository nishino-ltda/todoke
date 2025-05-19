// ***********************************************
// This file is where you can add global E2E test
// configurations and behavior that modifies Cypress.
//
// You can change the location of this file or turn
// off automatically serving support files with the
// 'supportFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/configuration
// ***********************************************

import './commands'
import { createPinia } from 'pinia'

// Initialize Pinia before each test
beforeEach(() => {
    const pinia = createPinia()
    
    cy.window().then((win) => {
        win.pinia = pinia
        if (win.__logStore) {
            win.__logStore.$pinia = pinia
        }
  })
})

// Add global before/beforeEach/after/afterEach hooks here if needed
