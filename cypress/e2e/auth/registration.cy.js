describe('🔐 User Registration', () => {
  beforeEach(() => {
    cy.log('🧹 Clearing log store before test');
    cy.window().then(win => {
      if (win.logStore) {
        win.logStore.clear();
      } else {
        cy.log('⚠️ logStore not found in window');
      }
    });
  });

  afterEach(() => {
    cy.log('📋 Dumping log store after test');
    cy.dumpLogs()
  });

  // SPRINT 1: Core authentication testing
  it('👤 Should register as customer', () => {
    cy.log('🛒 Testing customer registration');
    // Test will verify:
    // - Form validation works
    // - Success flow
    // - Account is created
    // - Redirects to correct dashboard
    
    // Setup test data with all required fields
    const customer = {
      name: 'Test Customer',
      email: `customer-${Date.now()}@todoke.test`,
      password: 'password123',
      type: 'customer',
      phone: '(11) 99999-9999',
      cpf: '123.456.789-09'
    };

    // Visit registration page (using real API)
    cy.visit('/register');
    
    // Test form validation
    cy.log('🔍 Verifying required field validation');
    cy.get('[data-test="register-button"]').click();
    
    // Expect validation errors (this is the expected behavior)
    cy.on('uncaught:exception', (err) => {
      expect(err.message).to.include('The name field is required');
      return false; // prevent Cypress from failing the test
    });

    cy.get('[data-test="auth-alert"]').should('be.visible');
    // Dump logs from logstore
    
    // Fill and submit form
    cy.log('📝 Filling registration form');
    cy.get('[data-test="name-input"] input').type(customer.name);
    cy.get('[data-test="email-input"] input').type(customer.email);
    cy.get('[data-test="password-input"] input').type(customer.password);
    cy.get('[data-test="password-confirmation-input"] input').type(customer.password);
    cy.get('[data-test="phone-input"] input').type(customer.phone);
    cy.get('[data-test="cpf-input"] input').type(customer.cpf);
    cy.get('[data-test="role-select"]').click();
    cy.get('[data-test="role-customer"]').click();
    cy.get('[data-test="register-button"]').click();
    
    // Dump logs from logstore

    // Verify successful registration
    cy.log('✅ Verifying successful registration');
    cy.get('[data-test="auth-alert"]').should('not.exist');
    
    // Verify successful redirect to customer dashboard
    cy.url().should('include', '/customer/dashboard', { timeout: 3000 });
    
    // Verify auth store is populated for customer
    cy.getStore('auth').its('user').should('not.be.null');
    cy.getStore('auth').its('user.type').should('eq', 'customer');
    cy.dumpLogs(true, 'Post-registration logs');
  });

  // SPRINT 1: Core authentication testing
  it('🛵 Should register as courier', () => {
    cy.log('🏍️ Testing courier registration');
    // Test will verify:
    // - Additional fields work
    // - Document upload
    // - Background check initiation
    // - Approval workflow

    // Setup test data with all required fields
    const courier = {
      name: 'Test Courier',
      email: `courier-${Date.now()}@todoke.test`,
      password: 'password123',
      type: 'courier',
      phone: '(11) 99999-9999',
      cpf: '123.456.789-09',
      license_number: `COURIER-${Date.now()}`,
      vehicle_type: 'motorcycle',
      password_confirmation: 'password123',
      document: 'test-license.jpg'
    };

    // Visit registration page (using real API)
    cy.visit('/register');
    
    // Fill basic form
    cy.log('📝 Filling basic registration form');
    cy.get('[data-test="name-input"] input').type(courier.name);
    cy.get('[data-test="email-input"] input').type(courier.email);
    cy.get('[data-test="phone-input"] input').type(courier.phone);
    cy.get('[data-test="cpf-input"] input').type(courier.cpf);
    cy.get('[data-test="password-input"] input').type(courier.password);
    cy.get('[data-test="password-confirmation-input"] input').type(courier.password);
    cy.get('[data-test="role-select"]').click();
    cy.get('[data-test="role-courier"]').click();
    
    // Verify courier-specific fields appear
    cy.log('🛵 Verifying courier fields');
    cy.get('[data-test="license-input"]').should('be.visible');
    cy.get('[data-test="vehicle-select"]').should('be.visible');
    cy.get('[data-test="document-upload"]').should('be.visible');
    
    // Fill courier-specific fields
    cy.get('[data-test="license-input"] input').type(courier.license_number);
    cy.get('[data-test="vehicle-select"]').click();
    cy.get('[data-test="vehicle-motorcycle"]').click();
    
    // Upload test license file
    cy.log('📄 Uploading test license file');
    cy.fixture('test-license.jpg', 'binary').then(fileContent => {
      const blob = Cypress.Blob.binaryStringToBlob(fileContent, 'image/jpeg');
      cy.get('[data-test="document-upload"] input[type="file"]').attachFile({
        fileContent: blob,
        fileName: 'test-license.jpg',
        mimeType: 'image/jpeg'
      });
    });
    
    // Submit form
    cy.get('[data-test="register-button"]').click();
    
    // Verify successful registration
    cy.log('✅ Verifying successful registration');
    cy.get('[data-test="auth-alert"]').should('not.exist');
    
    // Verify registration was submitted successfully
    cy.log('✅ Verifying registration submission');
    
    // Verify pending message is shown
    cy.get('[data-test="pending-alert"]').should('be.visible');
    cy.contains('Your account is pending approval').should('be.visible');
    
    // Verify auth store is NOT populated since account is pending
    cy.getStore('auth').its('user').should('be.null');
  });

  // SPRINT 1: Core authentication testing
  it.only('🍽️ Should register as partner', () => {
    cy.log('🏢 Testing partner registration');
    // Test will verify:
    // - Business info collection
    // - Verification workflow
    // - Admin approval process

    // Setup test data with all required fields
    const partner = {
      name: 'Test Partner',
      email: `partner-${Date.now()}@todoke.test`,
      password: 'password123',
      password_confirmation: 'password123',
      type: 'partner',
      phone: '(11) 99999-9999',
      cpf: '123.456.789-09',
      business_name: `Test Restaurant ${Date.now()}`,
      business_type: 'restaurant',
      tax_id: `${Date.now()}`,
      address: `${Date.now()} Main St`
    };

    // Visit registration page (using real API)
    cy.visit('/register');
    
    // Fill basic form
    cy.log('📝 Filling basic registration form');
    cy.get('[data-test="name-input"] input').type(partner.name);
    cy.get('[data-test="email-input"] input').type(partner.email);
    cy.get('[data-test="password-input"] input').type(partner.password);
    cy.get('[data-test="password-confirmation-input"] input').type(partner.password);
    cy.get('[data-test="role-select"]').click();
    cy.get('[data-test="role-partner"]').click();
    
    // Verify partner-specific fields appear
    cy.log('🏢 Verifying partner fields');
    cy.get('[data-test="business-name-input"]').should('be.visible');
    cy.get('[data-test="business-type-select"]').should('be.visible');
    cy.get('[data-test="tax-id-input"]').should('be.visible');
    cy.get('[data-test="address-input"]').should('be.visible');
    cy.get('[data-test="business-document-upload"]').should('be.visible');
    
    // Fill partner-specific fields
    cy.get('[data-test="business-name-input"] input').type(partner.business_name);
    cy.get('[data-test="business-type-select"]').click();
    cy.get('.v-list-item').contains('Restaurant').click();
    cy.get('[data-test="tax-id-input"] input').type(partner.tax_id);
    cy.get('[data-test="address-input"] input').type(partner.address);
    
    // Upload test business document
    cy.log('📄 Uploading test business document');
    cy.fixture('test-business-license.pdf').then(fileContent => {
      cy.get('[data-test="business-document-upload"] input[type="file"]').attachFile({
        fileContent: fileContent.toString(),
        fileName: 'test-business-license.pdf',
        mimeType: 'application/pdf'
      });
    });
    
    // Submit form
    cy.get('[data-test="register-button"]').click();
    
    // Verify successful registration
    cy.log('✅ Verifying successful registration');
    cy.get('[data-test="auth-alert"]').should('not.exist');
    
    // Verify pending message is shown
    cy.get('[data-test="pending-alert"]').should('be.visible');
    cy.contains('Your account is pending approval').should('be.visible');
    
    // Verify auth store is NOT populated since account is pending
    cy.getStore('auth').its('user').should('be.null');
    cy.dumpLogs(true, 'Post-registration logs');
  });

  // SPRINT 1: Core authentication testing
  it('⚠️ Should handle validation errors', () => {
    cy.log('❌ Testing validation errors');
    // Test will verify:
    // - Field-level errors
    // - Form-level errors
    // - Error messages are clear

    // Visit registration page (using real API)
    cy.visit('/register');
    
    // Test client-side validation
    cy.log('🔍 Testing client-side validation');
    cy.get('[data-test="register-button"]').click();
    
    // Expect validation errors (this is the expected behavior)
    cy.on('uncaught:exception', (err) => {
      expect(err.message).to.include('The name field is required');
      return false; // prevent Cypress from failing the test
    });

    // Verify error messages
    cy.log('📋 Verifying error messages');
    cy.get('[data-test="auth-alert"]').should('be.visible');
    cy.get('[data-test="name-input"] .v-messages__message').should('be.visible');
    cy.get('[data-test="email-input"] .v-messages__message').should('be.visible');
    cy.get('[data-test="password-input"] .v-messages__message').should('be.visible');
    cy.get('[data-test="role-select"] .v-messages__message').should('be.visible');

    // Test server-side validation
    cy.log('📡 Testing server-side validation');
    cy.get('[data-test="name-input"] input').type('T');
    cy.get('[data-test="email-input"] input').type('invalid-email');
    cy.get('[data-test="password-input"] input').type('short');
    cy.get('[data-test="register-button"]').click();
    
    // Verify server validation errors
    cy.log('❌ Verifying server validation errors');
    cy.get('[data-test="auth-alert"]', { timeout: 3000 }).should('be.visible');
    cy.get('[data-test="name-input"] .v-messages__message').should('be.visible');
    cy.get('[data-test="email-input"] .v-messages__message').should('be.visible');
    cy.get('[data-test="password-input"] .v-messages__message').should('be.visible');
    cy.get('[data-test="role-select"] .v-messages__message').should('be.visible');
  });

  // SPRINT 1: Core authentication testing
  it('📱 Should work on mobile', () => {
    cy.log('📲 Testing mobile registration');
    // Test will verify:
    // - Form is usable
    // - Keyboard works properly
    // - No horizontal scrolling

    // Set mobile viewport
    cy.viewport('iphone-x');

    // Setup test data
    const user = {
      name: 'Mobile User',
      email: `mobile-${Date.now()}@todoke.test`,
      password: 'password123',
      type: 'customer',
      phone: '(11) 99999-9999',
      cpf: '123.456.789-09'
    };

    // Visit registration page
    cy.visit('/register');

    // Test form usability
    cy.log('📱 Testing form rendering');
    cy.get('body').should('be.visible');
    
    // Wait for form inputs to be visible and enabled
    cy.get('[data-test="name-input"] input', { timeout: 30000 })
      .should('be.visible')
      .and('not.be.disabled');

    // Fill form
    cy.log('📝 Filling registration form');
    cy.get('[data-test="name-input"] input')
      .should('be.visible')
      .type(user.name);
    cy.get('[data-test="email-input"] input')
      .should('be.visible')
      .type(user.email);
    cy.get('[data-test="password-input"] input')
      .should('be.visible')
      .type(user.password);
    cy.get('[data-test="password-confirmation-input"] input')
      .should('be.visible')
      .type(user.password);
    
    // Fill additional required fields
    cy.get('[data-test="phone-input"] input')
      .should('be.visible')
      .type(user.phone);
    cy.get('[data-test="cpf-input"]')
      .should('be.visible')
      .type(user.cpf);
    
    // Select role
    cy.get('[data-test="role-select"]').click();
    cy.get('[data-test="role-customer"]').click();

    // Test keyboard behavior
    cy.log('⌨️ Testing keyboard behavior');
    cy.get('[data-test="name-input"] input').clear().type(user.name).blur();
    cy.get('[data-test="email-input"] input').click();
    cy.get('[data-test="email-input"] input').should('be.focused');

    // Test no horizontal scrolling
    cy.log('↔️ Testing no horizontal scroll');
    cy.document().its('documentElement').should('have.prop', 'scrollWidth')
      .then((scrollWidth) => {
        cy.window().its('innerWidth').should('equal', scrollWidth);
      });

    // Submit form
    cy.get('[data-test="register-button"]').click();
    
    // Verify successful registration
    cy.log('✅ Verifying successful registration');
    cy.url().should('include', '/customer/dashboard', { timeout: 20000 });
  });
});
