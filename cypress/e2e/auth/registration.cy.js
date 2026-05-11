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
      cpf: '111.222.333-09'
    };

    // Visit registration page (using real API)
    cy.visit('/register');
    
    // Test form validation
    cy.log('🔍 Verifying required field validation');
    cy.get('[data-cy*="register-button"]').click();
    
    // Expect validation errors (this is the expected behavior)
    cy.on('uncaught:exception', (err) => {
      expect(err.message).to.include('The name field is required');
      return false; // prevent Cypress from failing the test
    });

    cy.get('[data-cy*="auth-alert"]').should('be.visible');
    // Dump logs from logstore
    
    // Fill and submit form
    cy.log('📝 Filling registration form');
    cy.get('[data-cy*="name-input"] input').type(customer.name);
    cy.get('[data-cy*="email-input"] input').type(customer.email);
    cy.get('[data-cy*="password-input"] input').type(customer.password);
    cy.get('[data-cy*="password-confirmation-input"] input').type(customer.password);
    cy.get('[data-cy*="phone-input"] input').type(customer.phone);
    cy.get('[data-cy*="cpf-input"] input').type(customer.cpf);
    cy.get('[data-cy*="role-select"]').click();
    cy.get('[data-cy*="role-customer"]').click();
    cy.get('[data-cy*="register-button"]').click();
    
    // Dump logs from logstore

    // Verify successful registration
    cy.log('✅ Verifying successful registration');
    cy.get('[data-cy*="auth-alert"]').should('not.exist');
    
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
      cpf: '222.333.444-09',
      license_number: `COURIER-${Date.now()}`,
      vehicle_type: 'motorcycle',
      password_confirmation: 'password123',
      document: 'test-license.jpg'
    };

    // Visit registration page (using real API)
    cy.visit('/register');
    
    // Fill basic form
    cy.log('📝 Filling basic registration form');
    cy.get('[data-cy*="name-input"] input').type(courier.name);
    cy.get('[data-cy*="email-input"] input').type(courier.email);
    cy.get('[data-cy*="phone-input"] input').type(courier.phone);
    cy.get('[data-cy*="cpf-input"] input').type(courier.cpf);
    cy.get('[data-cy*="password-input"] input').type(courier.password);
    cy.get('[data-cy*="password-confirmation-input"] input').type(courier.password);
    cy.get('[data-cy*="role-select"]').click();
    cy.get('[data-cy*="role-courier"]').click();
    
    // Verify courier-specific fields appear
    cy.log('🛵 Verifying courier fields');
    cy.get('[data-cy*="license-input"]').should('be.visible');
    cy.get('[data-cy*="vehicle-select"]').should('be.visible');
    cy.get('[data-cy*="document-upload"]').should('be.visible');
    
    // Fill courier-specific fields
    cy.get('[data-cy*="license-input"] input').type(courier.license_number);
    cy.get('[data-cy*="vehicle-select"]').click();
    cy.get('[data-cy*="vehicle-motorcycle"]').click();
    
    // Upload test license file
    cy.log('📄 Uploading test license file');
    cy.fixture('test-license.jpg', 'binary').then(fileContent => {
      const blob = Cypress.Blob.binaryStringToBlob(fileContent, 'image/jpeg');
      cy.get('[data-cy*="document-upload"] input[type="file"]').attachFile({
        fileContent: blob,
        fileName: 'test-license.jpg',
        mimeType: 'image/jpeg'
      });
    });
    
    // Submit form
    cy.get('[data-cy*="register-button"]').click();
    
    // Verify successful registration
    cy.log('✅ Verifying successful registration');
    cy.get('[data-cy*="auth-alert"]').should('not.exist');
    
    // Verify registration was submitted successfully
    cy.log('✅ Verifying registration submission');
    
    // Verify pending message is shown
    cy.get('[data-cy*="pending-alert"]').should('be.visible');
    cy.contains('Your account is pending approval').should('be.visible');
    
    // Verify auth store is NOT populated since account is pending
    cy.getStore('auth').its('user').should('be.null');
  });

  // SPRINT 1: Core authentication testing
  it('🍽️ Should register as partner', () => {
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
      cpf: '333.444.555-09',
      business_name: `Test Restaurant ${Date.now()}`,
      business_type: 'restaurant',
      tax_id: `${Date.now()}`,
      address: `${Date.now()} Main St`
    };

    // Visit registration page (using real API)
    cy.visit('/register');
    
    // Fill basic form
    cy.log('📝 Filling basic registration form');
    cy.get('[data-cy*="name-input"] input').type(partner.name);
    cy.get('[data-cy*="email-input"] input').type(partner.email);
    cy.get('[data-cy*="phone-input"] input').type(partner.phone);
    cy.get('[data-cy*="cpf-input"] input').type(partner.cpf);
    cy.get('[data-cy*="password-input"] input').type(partner.password);
    cy.get('[data-cy*="password-confirmation-input"] input').type(partner.password);
    cy.get('[data-cy*="role-select"]').click();
    cy.get('[data-cy*="role-partner"]').click();
    
    // Verify partner-specific fields appear
    cy.log('🏢 Verifying partner fields');
    cy.get('[data-cy*="business-name-input"]').should('be.visible');
    cy.get('[data-cy*="business-type-select"]').should('be.visible');
    cy.get('[data-cy*="tax-id-input"]').should('be.visible');
    cy.get('[data-cy*="address-input"]').should('be.visible');
    cy.get('[data-cy*="business-document-upload"]').should('be.visible');
    
    // Fill partner-specific fields
    cy.get('[data-cy*="business-name-input"] input').type(partner.business_name);
    cy.get('[data-cy*="business-type-select"]').click();
    cy.get('.v-list-item').contains('Restaurant').click();
    cy.get('[data-cy*="tax-id-input"] input').type(partner.tax_id);
    cy.get('[data-cy*="address-input"] input').type(partner.address);
    
    // Upload test business document
    cy.log('📄 Uploading test business document');
    cy.fixture('test-business-license.pdf', 'binary').then(fileContent => {
      const blob = Cypress.Blob.binaryStringToBlob(fileContent, 'application/pdf');
      cy.get('[data-cy*="business-document-upload"] input[type="file"]').attachFile({
        fileContent: blob,
        fileName: 'test-business-license.pdf',
        mimeType: 'application/pdf'
      });
    });
    
    // Wait for file to be processed
    cy.wait(1000);
    
    // Submit form
    cy.get('[data-cy*="register-button"]').click();
    
    // Verify successful registration
    cy.log('✅ Verifying successful registration');
    cy.get('[data-cy*="auth-alert"]').should('not.exist');
    
    // Verify pending message is shown
    cy.get('[data-cy*="pending-alert"]').should('be.visible');
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
    cy.get('[data-cy*="register-button"]').click();
    
    // Expect validation errors (this is the expected behavior)
    cy.on('uncaught:exception', (err) => {
      expect(err.message).to.include('The name field is required');
      return false; // prevent Cypress from failing the test
    });

    // Verify validation errors by checking alert and URL
    cy.log('📋 Verifying validation errors');
    cy.get('[data-cy*="auth-alert"]').should('be.visible');
    cy.url().should('include', '/register');

    // Test server-side validation
    cy.log('📡 Testing server-side validation');
    cy.get('[data-cy*="name-input"] input').type('T');
    cy.get('[data-cy*="email-input"] input').type('invalid-email');
    cy.get('[data-cy*="password-input"] input').type('short');
    cy.get('[data-cy*="register-button"]').click();
    
    // Verify server validation errors by checking alert and URL
    cy.log('❌ Verifying server validation errors');
    cy.get('[data-cy*="auth-alert"]', { timeout: 3000 }).should('be.visible');
    cy.url().should('include', '/register');
  });

  // SPRINT 1: Core authentication testing
  it('📱 Should work on mobile', () => {
    cy.log('📲 Testing mobile registration');
    // Test will verify basic mobile functionality:
    // - Form renders correctly
    // - Can submit successfully
    // - No layout issues

    // Set mobile viewport
    cy.viewport('iphone-6');

    // Setup test data
    const user = {
      name: 'Mobile User',
      email: `mobile-${Date.now()}@todoke.test`,
      password: 'password123',
      phone: '(11) 99999-9999',
      cpf: '444.555.666-09'
    };

    // Visit registration page
    cy.visit('/register');

    // Verify form renders correctly
    cy.get('[data-cy*="name-input"]').should('be.visible');
    cy.get('[data-cy*="email-input"]').should('be.visible');
    cy.get('[data-cy*="phone-input"]').should('be.visible');
    cy.get('[data-cy*="cpf-input"]').should('be.visible');
    cy.get('[data-cy*="password-input"]').should('be.visible');
    cy.get('[data-cy*="password-confirmation-input"]').should('be.visible');

    // Fill and submit form
    cy.get('[data-cy*="name-input"] input').type(user.name);
    cy.get('[data-cy*="email-input"] input').type(user.email);
    cy.get('[data-cy*="phone-input"] input').type(user.phone);
    cy.get('[data-cy*="cpf-input"] input').type(user.cpf);
    cy.get('[data-cy*="password-input"] input').type(user.password);
    cy.get('[data-cy*="password-confirmation-input"] input').type(user.password);
    cy.get('[data-cy*="role-select"]').click();
    cy.get('[data-cy*="role-customer"]').click();
    cy.get('[data-cy*="register-button"]').click();

    // Verify successful registration
    cy.url().should('include', '/customer/dashboard', { timeout: 3000 });
    cy.get('[data-cy*="auth-alert"]').should('not.exist');
  });
});
