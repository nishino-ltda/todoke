describe('🔑 Login Flow', () => {
  it('👤 Should login as customer', () => {
    cy.log('🛒 Testing customer login');
    // Test will verify:
    // - Form validation works
    // - Success flow
    // - Redirects to customer dashboard
    // - Session is established
    
    // Setup test data
    const customer = {
      email: 'customer@todoke.test',
      password: 'password123'
    };
    
    // Mock API response
    cy.intercept('POST', '/api/login', {
      statusCode: 200,
      body: {
        user: {
          id: 1,
          name: 'Test Customer',
          email: customer.email,
          type: 'customer'
        },
        token: 'fake-jwt-token'
      }
    }).as('loginRequest');

    // Visit login page
    cy.visit('/login');
    
    // Test form validation
    cy.get('[data-test="login-button"]').click();
    cy.log('🔍 Verifying required field validation');
    cy.contains('Email is required').should('be.visible');
    cy.contains('Password is required').should('be.visible');
    
    // Fill and submit form
    cy.log('📝 Filling login form');
    cy.get('[data-test="email-input"]').type(customer.email);
    cy.get('[data-test="password-input"]').type(customer.password);
    cy.get('[data-test="login-button"]').click();
    
    // Verify API call
    cy.log('📡 Verifying API request');
    cy.wait('@loginRequest').its('request.body').should('deep.equal', {
      email: customer.email,
      password: customer.password
    });
    
    // Verify redirect and session
    cy.log('🔄 Verifying redirect and session');
    cy.url().should('include', '/customer/dashboard');
    cy.window().its('localStorage.token').should('exist');
  });

  it('🛵 Should login as courier', () => {
    cy.log('🏍️ Testing courier login');
    // Test will verify:
    // - Redirects to courier dashboard
    // - Shows availability toggle
    // - Shows delivery requests

    // Setup test data
    const courier = {
      email: 'courier@todoke.test',
      password: 'password123'
    };
    
    // Mock API response
    cy.intercept('POST', '/api/login', {
      statusCode: 200,
      body: {
        user: {
          id: 2,
          name: 'Test Courier',
          email: courier.email,
          type: 'courier',
          availability: false
        },
        token: 'fake-jwt-token'
      }
    }).as('loginRequest');

    // Visit login page
    cy.visit('/login');
    
    // Fill and submit form
    cy.log('📝 Filling login form');
    cy.get('[data-test="email-input"]').type(courier.email);
    cy.get('[data-test="password-input"]').type(courier.password);
    cy.get('[data-test="login-button"]').click();
    
    // Verify API call
    cy.log('📡 Verifying API request');
    cy.wait('@loginRequest').its('request.body').should('deep.equal', {
      email: courier.email,
      password: courier.password
    });
    
    // Verify redirect and dashboard elements
    cy.log('🔄 Verifying courier dashboard');
    cy.url().should('include', '/courier/dashboard');
    cy.get('[data-test="availability-toggle"]').should('exist');
    cy.get('[data-test="delivery-requests"]').should('exist');
    cy.window().its('localStorage.token').should('exist');
  });

  it('🍽️ Should login as partner', () => {
    cy.log('🏢 Testing partner login');
    // Test will verify:
    // - Redirects to partner dashboard
    // - Shows order management
    // - Shows business metrics

    // Setup test data
    const partner = {
      email: 'partner@todoke.test',
      password: 'password123'
    };
    
    // Mock API response
    cy.intercept('POST', '/api/login', {
      statusCode: 200,
      body: {
        user: {
          id: 3,
          name: 'Test Partner',
          email: partner.email,
          type: 'partner',
          node_id: 1
        },
        token: 'fake-jwt-token'
      }
    }).as('loginRequest');

    // Visit login page
    cy.visit('/login');
    
    // Fill and submit form
    cy.log('📝 Filling login form');
    cy.get('[data-test="email-input"]').type(partner.email);
    cy.get('[data-test="password-input"]').type(partner.password);
    cy.get('[data-test="login-button"]').click();
    
    // Verify API call
    cy.log('📡 Verifying API request');
    cy.wait('@loginRequest').its('request.body').should('deep.equal', {
      email: partner.email,
      password: partner.password
    });
    
    // Verify redirect and dashboard elements
    cy.log('🔄 Verifying partner dashboard');
    cy.url().should('include', '/partner/dashboard');
    cy.get('[data-test="order-management"]').should('exist');
    cy.get('[data-test="business-metrics"]').should('exist');
    cy.window().its('localStorage.token').should('exist');
  });

  it('👔 Should login as admin', () => {
    cy.log('💼 Testing admin login');
    // Test will verify:
    // - Redirects to admin dashboard
    // - Shows system controls
    // - Shows user management

    // Setup test data
    const admin = {
      email: 'admin@todoke.test',
      password: 'password123'
    };
    
    // Mock API response
    cy.intercept('POST', '/api/login', {
      statusCode: 200,
      body: {
        user: {
          id: 4,
          name: 'Test Admin',
          email: admin.email,
          type: 'admin',
          is_super_admin: true
        },
        token: 'fake-jwt-token'
      }
    }).as('loginRequest');

    // Visit login page
    cy.visit('/login');
    
    // Fill and submit form
    cy.log('📝 Filling login form');
    cy.get('[data-test="email-input"]').type(admin.email);
    cy.get('[data-test="password-input"]').type(admin.password);
    cy.get('[data-test="login-button"]').click();
    
    // Verify API call
    cy.log('📡 Verifying API request');
    cy.wait('@loginRequest').its('request.body').should('deep.equal', {
      email: admin.email,
      password: admin.password
    });
    
    // Verify redirect and dashboard elements
    cy.log('🔄 Verifying admin dashboard');
    cy.url().should('include', '/admin/dashboard');
    cy.get('[data-test="system-controls"]').should('exist');
    cy.get('[data-test="user-management"]').should('exist');
    cy.window().its('localStorage.token').should('exist');
  });

  it('⚠️ Should handle failed logins', () => {
    cy.log('❌ Testing login failures');
    // Test will verify:
    // - Wrong password
    // - Nonexistent account
    // - Locked account
    // - Rate limiting

    // Setup test data
    const validUser = {
      email: 'user@todoke.test',
      password: 'password123'
    };
    const invalidUser = {
      email: 'nonexistent@todoke.test',
      password: 'wrongpassword'
    };

    // Mock API responses
    cy.intercept('POST', '/api/login', (req) => {
      if (req.body.email === validUser.email && req.body.password !== validUser.password) {
        req.reply({
          statusCode: 401,
          body: { message: 'Invalid credentials' }
        });
      } else if (req.body.email === 'locked@todoke.test') {
        req.reply({
          statusCode: 403,
          body: { message: 'Account locked' }
        });
      } else if (req.body.email === invalidUser.email) {
        req.reply({
          statusCode: 404,
          body: { message: 'User not found' }
        });
      }
    }).as('loginRequest');

    // Test wrong password
    cy.log('🔑 Testing wrong password');
    cy.visit('/login');
    cy.get('[data-test="email-input"]').type(validUser.email);
    cy.get('[data-test="password-input"]').type('wrongpassword');
    cy.get('[data-test="login-button"]').click();
    cy.wait('@loginRequest');
    cy.contains('Invalid credentials').should('be.visible');

    // Test nonexistent account
    cy.log('👤 Testing nonexistent account');
    cy.get('[data-test="email-input"]').clear().type(invalidUser.email);
    cy.get('[data-test="password-input"]').clear().type(invalidUser.password);
    cy.get('[data-test="login-button"]').click();
    cy.wait('@loginRequest');
    cy.contains('User not found').should('be.visible');

    // Test locked account
    cy.log('🔒 Testing locked account');
    cy.get('[data-test="email-input"]').clear().type('locked@todoke.test');
    cy.get('[data-test="password-input"]').clear().type(validUser.password);
    cy.get('[data-test="login-button"]').click();
    cy.wait('@loginRequest');
    cy.contains('Account locked').should('be.visible');

    // Test rate limiting (5 failed attempts)
    cy.log('⏱️ Testing rate limiting');
    for (let i = 0; i < 5; i++) {
      cy.get('[data-test="email-input"]').clear().type(`user${i}@todoke.test`);
      cy.get('[data-test="password-input"]').clear().type('wrongpassword');
      cy.get('[data-test="login-button"]').click();
      cy.wait('@loginRequest');
    }
    cy.contains('Too many attempts').should('be.visible');
  });

  it('📱 Should work on mobile', () => {
    cy.log('📲 Testing mobile login');
    // Test will verify:
    // - Form is usable
    // - Keyboard works properly
    // - No horizontal scrolling

    // Set mobile viewport
    cy.viewport('iphone-x');

    // Setup test data
    const user = {
      email: 'mobile@todoke.test',
      password: 'password123'
    };

    // Mock API response
    cy.intercept('POST', '/api/login', {
      statusCode: 200,
      body: {
        user: {
          id: 5,
          name: 'Mobile User',
          email: user.email,
          type: 'customer'
        },
        token: 'fake-jwt-token'
      }
    }).as('loginRequest');

    // Visit login page
    cy.visit('/login');

    // Test form usability
    cy.log('📱 Testing form usability');
    cy.get('[data-test="email-input"]').should('be.visible').type(user.email);
    cy.get('[data-test="password-input"]').should('be.visible').type(user.password);
    
    // Test keyboard behavior
    cy.log('⌨️ Testing keyboard behavior');
    cy.get('[data-test="email-input"]').clear().type(user.email).blur();
    cy.get('[data-test="password-input"]').click();
    cy.get('[data-test="password-input"]').should('be.focused');
    
    // Test no horizontal scrolling
    cy.log('↔️ Testing no horizontal scroll');
    cy.document().its('documentElement').should('have.prop', 'scrollWidth')
      .then((scrollWidth) => {
        cy.viewport('iphone-x').then(() => {
          cy.window().its('innerWidth').should('equal', scrollWidth);
        });
      });

    // Submit form
    cy.get('[data-test="login-button"]').click();
    cy.wait('@loginRequest');
    cy.url().should('include', '/customer/dashboard');
  });
});
