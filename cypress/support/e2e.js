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
import { useLogStore } from '../../resources/js/stores/log.js'

// Initialize Pinia before each test
beforeEach(() => {
    const pinia = createPinia()
    
    cy.window().then((win) => {
        win.pinia = pinia
        // Create and register log store
        const logStore = useLogStore(pinia)
        win.logStore = {
            getLogs: () => logStore.logs,
            getLatest: () => logStore.logs[0]?.message || '',
            clear: () => logStore.clear(),
            messages: logStore.logs
        }
    })
})

// Add global before/beforeEach/after/afterEach hooks here if needed
