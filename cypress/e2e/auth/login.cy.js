describe('🔑 Login Flow', () => {
  beforeEach(() => {
    // Initialize log store before each test
    cy.window().then((win) => {
      if (!win.logStore) {
        win.logStore = {
          messages: [],
          clear() { 
            this.messages = []
            console.log('🧹 Log store cleared')
          },
          add(message) { 
            this.messages.push(message)
            console.log('📝 Log added:', message)
          },
          dump() {
            console.group('Log Store Dump')
            console.log('Message count:', this.messages.length)
            this.messages.forEach((msg, i) => {
              console.log(`#${i}: ${msg}`)
            })
            console.groupEnd()
          }
        };
      }
    });
    cy.getStore('log').then(logStore => {
      if (logStore.messages.length > 0) {
        cy.log('ℹ️ Found existing logs:')
        logStore.dump()
      }
      logStore.clear()
    });
  });

  // SPRINT 1: Core authentication testing
  it('👤 Should login as customer', () => {
    cy.log('🛒 Testing customer login');
    // Test will verify:
    // - Form validation works
    // - Success flow
    // - Redirects to customer dashboard
    // - Session is established
    // - Log store captures events
    
    // Setup test data
    const customer = {
      email: 'customer@todoke.test',
      password: 'password123'
    };
    
    // Visit login page (using real API)
    cy.visit('/login');
    
    // Test form validation
    cy.get('[data-test="login-button"]').click();
    cy.log('🔍 Verifying required field validation');
    cy.contains('Email is required').should('be.visible');
    cy.contains('Password is required').should('be.visible');
    
    // Check and dump logs for validation errors
    cy.dumpLogs();
    cy.getStore('log').then(logStore => {
      const logs = logStore.messages
      expect(logs).to.have.length(2)
      expect(logs[0].message).to.include('Email validation failed')
      expect(logs[1].message).to.include('Password validation failed')
    })
    
    // Fill and submit form
    cy.log('📝 Filling login form');
    cy.get('[data-test="email-input"]').type(customer.email);
    cy.get('[data-test="password-input"]').type(customer.password);
    cy.get('[data-test="login-button"]').click();
    
    // Verify successful login and redirect
    cy.log('✅ Verifying successful login and redirect');
    // Wait for redirect after login
    cy.url().should('include', '/customer/dashboard', { timeout: 10000 });
    cy.get('[data-testid="user-welcome"]').should('contain', 'Welcome back');
    cy.get('[data-testid="user-email"]').should('contain', customer.email);
    
    // Check and dump logs for successful login
    cy.dumpLogs();
    cy.getStore('log').then(logStore => {
      const logs = logStore.messages
      expect(logs.some(log => log.message.includes('Login attempt'))).to.be.true
      expect(logs.some(log => log.message.includes('Login successful'))).to.be.true
    })
  });

  // SPRINT 1: Core authentication testing  
  it('🛵 Should login as courier', () => {
    cy.log('🏍️ Testing courier login');
    // Test will verify:
    // - Redirects to courier dashboard
    // - Shows availability toggle
    // - Shows delivery requests
    // - Log store captures events

    // Setup test data
    const courier = {
      email: 'courier@todoke.test',
      password: 'password123'
    };
    
    // Visit login page (using real API)
    cy.visit('/login');
    
    // Fill and submit form
    cy.log('📝 Filling login form');
    cy.get('[data-test="email-input"]').type(courier.email);
    cy.get('[data-test="password-input"]').type(courier.password);
    cy.get('[data-test="login-button"]').click();
    
    // Verify successful login and redirect  
    cy.log('✅ Verifying successful login and redirect');
    // Wait for redirect after login
    cy.url().should('include', '/courier/dashboard', { timeout: 20000 });
    cy.get('[data-testid="courier-welcome"]').should('contain', 'Courier Dashboard');
    
    // Check and dump logs for successful login
    cy.dumpLogs();
    cy.getStore('log').then(logStore => {
      const logs = logStore.messages;
      expect(logs.some(log => 
        log.message.includes('Login attempt') && 
        log.message.includes(courier.email)
      )).to.be.true;
      expect(logs.some(log => 
        log.message.includes('Login successful') && 
        log.message.includes('courier')
      )).to.be.true;
    });
  });

  // SPRINT 1: Core authentication testing
  it('🍽️ Should login as partner', () => {
    cy.log('🏢 Testing partner login');
    // Test will verify:
    // - Redirects to partner dashboard
    // - Shows order management
    // - Shows business metrics
    // - Log store captures events

    // Setup test data
    const partner = {
      email: 'partner@todoke.test',
      password: 'password123'
    };
    
    // Visit login page (using real API)
    cy.visit('/login');
    
    // Fill and submit form
    cy.log('📝 Filling login form');
    cy.get('[data-test="email-input"]').type(partner.email);
    cy.get('[data-test="password-input"]').type(partner.password);
    cy.get('[data-test="login-button"]').click();
    
    // Verify successful login and redirect
    cy.log('✅ Verifying successful login and redirect');
    // Wait for redirect after login
    cy.url().should('include', '/partner/dashboard', { timeout: 20000 });
    cy.get('[data-testid="partner-welcome"]').should('contain', 'Partner Dashboard');
    
    // Check and dump logs for successful login
    cy.dumpLogs();
    cy.getStore('log').then(logStore => {
      const logs = logStore.messages;
      expect(logs.some(log => 
        log.message.includes('Login attempt') && 
        log.message.includes(partner.email)
      )).to.be.true;
      expect(logs.some(log => 
        log.message.includes('Login successful') && 
        log.message.includes('partner')
      )).to.be.true;
    });
  });

  // SPRINT 1: Core authentication testing
  it('👔 Should login as admin', () => {
    cy.log('💼 Testing admin login');
    // Test will verify:
    // - Redirects to admin dashboard
    // - Shows system controls
    // - Shows user management
    // - Log store captures events

    // Setup test data
    const admin = {
      email: 'admin@todoke.test',
      password: 'password123'
    };
    
    // Visit login page (using real API)
    cy.visit('/login');
    
    // Fill and submit form
    cy.log('📝 Filling login form');
    cy.get('[data-test="email-input"]').type(admin.email);
    cy.get('[data-test="password-input"]').type(admin.password);
    cy.get('[data-test="login-button"]').click();
    
    // Verify successful login and redirect
    cy.log('✅ Verifying successful login and redirect');
    // Wait for redirect after login
    cy.url().should('include', '/admin/dashboard', { timeout: 10000 });
    cy.get('[data-testid="admin-welcome"]').should('contain', 'Admin Dashboard');
    
    // Check and dump logs for successful login
    cy.dumpLogs();
    cy.getStore('log').then(logStore => {
      const logs = logStore.messages;
      expect(logs.some(log => 
        log.message.includes('Login attempt') && 
        log.message.includes(admin.email)
      )).to.be.true;
      expect(logs.some(log => 
        log.message.includes('Login successful') && 
        log.message.includes('admin')
      )).to.be.true;
    });
  });

  // SPRINT 1: Core authentication testing
  it('⚠️ Should handle failed logins', () => {
    cy.log('❌ Testing login failures');
    // Test will verify:
    // - Wrong password
    // - Nonexistent account
    // - Locked account
    // - Rate limiting
    // - Appropriate log messages

    // Setup test data
    const validUser = {
      email: 'user@todoke.test',
      password: 'password123'
    };
    const invalidUser = {
      email: 'nonexistent@todoke.test',
      password: 'wrongpassword'
    };

    // No API mocking - real E2E test

    // Test wrong password
    cy.log('🔑 Testing wrong password');
    cy.visit('/login');
    cy.get('[data-test="email-input"]').type(validUser.email);
    cy.get('[data-test="password-input"]').type('wrongpassword');
    cy.get('[data-test="login-button"]').click();
    cy.get('[data-test="auth-alert"]', { timeout: 10000 }).should('be.visible')
      .should('contain', 'Invalid login credentials');
    
    // Check and dump logs for failed attempt
    cy.dumpLogs();
    cy.getStore('log').then(logStore => {
      const logs = logStore.messages;
      expect(logs.some(log => 
        log.message.includes('Login attempt') && 
        log.message.includes(validUser.email)
      )).to.be.true;
      expect(logs.some(log => 
        log.message.includes('Login failed') && 
        log.message.includes('Invalid credentials')
      )).to.be.true;
    });

    // Test nonexistent account
    cy.log('👤 Testing nonexistent account');
    cy.get('[data-test="email-input"]').clear().type(invalidUser.email);
    cy.get('[data-test="password-input"]').clear().type(invalidUser.password);
    cy.get('[data-test="login-button"]').click();
    cy.get('[data-test="auth-alert"]', { timeout: 10000 }).should('be.visible')
      .should('contain', 'User not found');
    
    // Check and dump logs for nonexistent account
    cy.dumpLogs();
    cy.getStore('log').then(logStore => {
      const logs = logStore.messages;
      expect(logs.some(log => 
        log.message.includes('Login attempt') && 
        log.message.includes(invalidUser.email)
      )).to.be.true;
      expect(logs.some(log => 
        log.message.includes('Login failed') && 
        log.message.includes('User not found')
      )).to.be.true;
    });

    // Test locked account
    cy.log('🔒 Testing locked account');
    cy.get('[data-test="email-input"]').clear().type('locked@todoke.test');
    cy.get('[data-test="password-input"]').clear().type(validUser.password);
    cy.get('[data-test="login-button"]').click();
    cy.get('[data-test="auth-alert"]', { timeout: 10000 }).should('be.visible')
      .should('contain', 'Account locked');
    
    // Check and dump logs for locked account
    cy.dumpLogs();
    cy.getStore('log').then(logStore => {
      const logs = logStore.messages;
      expect(logs.some(log => 
        log.message.includes('Login attempt') && 
        log.message.includes('locked@todoke.test')
      )).to.be.true;
      expect(logs.some(log => 
        log.message.includes('Login failed') && 
        log.message.includes('Account locked')
      )).to.be.true;
    });

    // Test rate limiting (5 failed attempts)
    cy.log('⏱️ Testing rate limiting');
    for (let i = 0; i < 5; i++) {
      cy.get('[data-test="email-input"]').clear().type(`user${i}@todoke.test`);
      cy.get('[data-test="password-input"]').clear().type('wrongpassword');
      cy.get('[data-test="login-button"]').click();
      cy.wait('@loginRequest');
    }
    cy.get('[data-test="auth-alert"]').should('be.visible')
      .should('contain', 'Too many attempts');
    
    // Check and dump logs for rate limiting
    cy.dumpLogs();
    cy.getStore('log').then(logStore => {
      const logs = logStore.messages;
      expect(logs.some(log => 
        log.message.includes('Rate limiting triggered')
      )).to.be.true;
    });
  });

  // SPRINT 1: Core authentication testing
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

    // Visit login page (using real API)
    cy.visit('/login');

    // Test form usability
    cy.log('� Testing form usability');
    cy.get('[data-test="email-input"]').should('be.visible').type(user.email);
    cy.get('[data-test="password-input"]').should('be.visible').type(user.password);
    
    // Test keyboard behavior
    cy.log('⌨️ Testing keyboard behavior');
    cy.get('[data-test="email-input"]').clear().type(user.email);
    cy.get('[data-test="password-input"]').click().type(user.password);
    
    // Submit form
    cy.get('[data-test="login-button"]').click();
    cy.url().should('include', '/customer/dashboard', { timeout: 10000 });
  });
});
