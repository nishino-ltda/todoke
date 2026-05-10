describe('Real-time Notification System', () => {
    beforeEach(() => {
        cy.intercept('GET', '/api/user').as('getUser');
        cy.intercept('POST', '/login').as('login');
    });

    it('displays notifications on the courier dashboard when a new delivery is available', () => {
        // Mock courier login
        cy.loginByRole('courier');
        cy.visit('/courier/dashboard');
        cy.wait('@getUser');

        // Verify notification area exists (AppAlert is usually in the layout)
        cy.get('[data-cy="notifications-container"]').should('exist');

        // Simulate a real-time event
        // In a real E2E test with Reverb running, we would trigger an action that broadcasts.
        // For this test, we'll manually trigger the event on the window.Echo if available, 
        // or mock the store call if Echo is hard to trigger.
        
        cy.window().then((win) => {
            if (win.Echo) {
                // We can't easily trigger a real WebSocket event from here without the server,
                // but we can mock the store action to verify the UI.
                const notificationStore = win.__appStores.notificationStore;
                if (notificationStore) {
                    notificationStore.info('New delivery available in your area', 'New Delivery');
                }
            }
        });

        // Verify the alert appears
        cy.get('[data-cy="app-alert"]').should('be.visible')
            .and('contain', 'New delivery available in your area');
        
        // Test dismissal
        cy.get('[data-cy="dismiss-alert"]').click();
        cy.get('[data-cy="app-alert"]').should('not.exist');
    });

    it('works with pt-BR locale', () => {
        cy.loginByRole('courier');
        cy.visit('/courier/dashboard');
        
        // Switch to pt-BR if not already
        cy.get('[data-cy="language-switcher"]').click();
        cy.get('[data-cy="lang-pt-BR"]').click();

        cy.window().then((win) => {
            const notificationStore = win.__appStores.notificationStore;
            if (notificationStore) {
                notificationStore.success('Pedido #123 aceito com sucesso', 'Sucesso');
            }
        });

        cy.get('[data-cy="app-alert"]').should('be.visible')
            .and('contain', 'Pedido #123 aceito com sucesso');
    });
});
