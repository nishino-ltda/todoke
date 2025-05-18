describe('🔐 User Registration', () => {
  it('👤 Should register as customer', () => {
    cy.log('🛒 Testing customer registration');
    // Test will verify:
    // - Form validation works
    // - Success flow
    // - Account is created
    // - Redirects to correct dashboard
    
    // Setup test data
    const customer = {
      name: 'Test Customer',
      email: 'customer@todoke.test',
      password: 'password123',
      role: 'customer'
    };
    
    // Mock API response
    cy.intercept('POST', '/api/v1/auth/register', {
      statusCode: 201,
      body: {
        user: {
          id: 1,
          name: customer.name,
          email: customer.email,
          type: customer.role
        },
        token: 'fake-jwt-token'
      }
    }).as('registerRequest');

    // Visit registration page
    cy.visit('/register');
    
    // Test form validation
    cy.get('[data-test="register-button"]').click();
    cy.log('🔍 Verifying required field validation');
    cy.contains('Name is required').should('be.visible');
    cy.contains('Email is required').should('be.visible');
    cy.contains('Password is required').should('be.visible');
    cy.contains('Account type is required').should('be.visible');
    
    // Fill and submit form
    cy.log('📝 Filling registration form');
    cy.get('[data-test="name-input"]').type(customer.name);
    cy.get('[data-test="email-input"]').type(customer.email);
    cy.get('[data-test="password-input"]').type(customer.password);
    cy.get('[data-test="password-confirm-input"]').type(customer.password);
    cy.get('[data-test="role-select"]').click();
    cy.get('.v-list-item').contains('Customer').click();
    cy.get('[data-test="register-button"]').click();
    
    // Verify API call
    cy.log('📡 Verifying API request');
    cy.wait('@registerRequest').its('request.body').should('deep.equal', {
      name: customer.name,
      email: customer.email,
      password: customer.password,
      password_confirmation: customer.password,
      role: customer.role
    });
    
    // Verify redirect and session
    cy.log('🔄 Verifying redirect and session');
    cy.url().should('include', '/customer/dashboard');
    cy.window().its('localStorage.token').should('exist');
  });

  it('🛵 Should register as courier', () => {
    cy.log('🏍️ Testing courier registration');
    // Test will verify:
    // - Additional fields work
    // - Document upload
    // - Background check initiation
    // - Approval workflow

    // Setup test data
    const courier = {
      name: 'Test Courier',
      email: 'courier@todoke.test',
      password: 'password123',
      role: 'courier',
      license_number: 'COURIER123',
      vehicle_type: 'motorcycle'
    };

    // Mock API responses
    cy.intercept('POST', '/api/v1/auth/register', {
      statusCode: 201,
      body: {
        user: {
          id: 2,
          name: courier.name,
          email: courier.email,
          type: courier.role,
          courier_profile: {
            license_number: courier.license_number,
            vehicle_type: courier.vehicle_type,
            status: 'pending_approval'
          }
        },
        token: 'fake-jwt-token'
      }
    }).as('registerRequest');

    cy.intercept('POST', '/api/document-upload', {
      statusCode: 200,
      body: { success: true }
    }).as('documentUpload');

    // Visit registration page
    cy.visit('/register');
    
    // Fill basic form
    cy.log('📝 Filling basic registration form');
    cy.get('[data-test="name-input"]').type(courier.name);
    cy.get('[data-test="email-input"]').type(courier.email);
    cy.get('[data-test="password-input"]').type(courier.password);
    cy.get('[data-test="password-confirm-input"]').type(courier.password);
    cy.get('[data-test="role-select"]').click();
    cy.get('.v-list-item').contains('Courier').click();
    
    // Verify courier-specific fields appear
    cy.log('🛵 Verifying courier fields');
    cy.get('[data-test="license-input"]').should('be.visible');
    cy.get('[data-test="vehicle-select"]').should('be.visible');
    cy.get('[data-test="document-upload"]').should('be.visible');
    
    // Fill courier-specific fields
    cy.get('[data-test="license-input"]').type(courier.license_number);
    cy.get('[data-test="vehicle-select"]').click();
    cy.get('.v-list-item').contains('Motorcycle').click();
    
    // Mock file upload
    cy.fixture('test-license.jpg').then(fileContent => {
      cy.get('[data-test="document-upload"] input[type="file"]').attachFile({
        fileContent: fileContent.toString(),
        fileName: 'test-license.jpg',
        mimeType: 'image/jpeg'
      });
    });
    
    // Submit form
    cy.get('[data-test="register-button"]').click();
    
    // Verify API calls
    cy.log('📡 Verifying API requests');
    cy.wait('@registerRequest').its('request.body').should('deep.equal', {
      name: courier.name,
      email: courier.email,
      password: courier.password,
      password_confirmation: courier.password,
      role: courier.role,
      license_number: courier.license_number,
      vehicle_type: courier.vehicle_type
    });
    cy.wait('@documentUpload');
    
    // Verify redirect and pending approval state
    cy.log('🔄 Verifying pending approval state');
    cy.url().should('include', '/courier/dashboard');
    cy.contains('Your account is pending approval').should('be.visible');
    cy.window().its('localStorage.token').should('exist');
  });

  it('🍽️ Should register as partner', () => {
    cy.log('🏢 Testing partner registration');
    // Test will verify:
    // - Business info collection
    // - Verification workflow
    // - Admin approval process

    // Setup test data
    const partner = {
      name: 'Test Partner',
      email: 'partner@todoke.test',
      password: 'password123',
      role: 'partner',
      business_name: 'Test Restaurant',
      business_type: 'restaurant',
      tax_id: '123456789',
      address: '123 Main St'
    };

    // Mock API responses
    cy.intercept('POST', '/api/v1/auth/register', {
      statusCode: 201,
      body: {
        user: {
          id: 3,
          name: partner.name,
          email: partner.email,
          type: partner.role,
          partner_profile: {
            business_name: partner.business_name,
            business_type: partner.business_type,
            tax_id: partner.tax_id,
            address: partner.address,
            status: 'pending_verification'
          }
        },
        token: 'fake-jwt-token'
      }
    }).as('registerRequest');

    cy.intercept('POST', '/api/business-documents', {
      statusCode: 200,
      body: { success: true }
    }).as('documentUpload');

    // Visit registration page
    cy.visit('/register');
    
    // Fill basic form
    cy.log('📝 Filling basic registration form');
    cy.get('[data-test="name-input"]').type(partner.name);
    cy.get('[data-test="email-input"]').type(partner.email);
    cy.get('[data-test="password-input"]').type(partner.password);
    cy.get('[data-test="password-confirm-input"]').type(partner.password);
    cy.get('[data-test="role-select"]').click();
    cy.get('.v-list-item').contains('Partner').click();
    
    // Verify partner-specific fields appear
    cy.log('🏢 Verifying partner fields');
    cy.get('[data-test="business-name-input"]').should('be.visible');
    cy.get('[data-test="business-type-select"]').should('be.visible');
    cy.get('[data-test="tax-id-input"]').should('be.visible');
    cy.get('[data-test="address-input"]').should('be.visible');
    cy.get('[data-test="business-document-upload"]').should('be.visible');
    
    // Fill partner-specific fields
    cy.get('[data-test="business-name-input"]').type(partner.business_name);
    cy.get('[data-test="business-type-select"]').click();
    cy.get('.v-list-item').contains('Restaurant').click();
    cy.get('[data-test="tax-id-input"]').type(partner.tax_id);
    cy.get('[data-test="address-input"]').type(partner.address);
    
    // Mock file upload
    cy.fixture('test-business-license.pdf').then(fileContent => {
      cy.get('[data-test="business-document-upload"] input[type="file"]').attachFile({
        fileContent: fileContent.toString(),
        fileName: 'test-business-license.pdf',
        mimeType: 'application/pdf'
      });
    });
    
    // Submit form
    cy.get('[data-test="register-button"]').click();
    
    // Verify API calls
    cy.log('📡 Verifying API requests');
    cy.wait('@registerRequest').its('request.body').should('deep.equal', {
      name: partner.name,
      email: partner.email,
      password: partner.password,
      password_confirmation: partner.password,
      role: partner.role,
      business_name: partner.business_name,
      business_type: partner.business_type,
      tax_id: partner.tax_id,
      address: partner.address
    });
    cy.wait('@documentUpload');
    
    // Verify redirect and pending verification state
    cy.log('🔄 Verifying pending verification state');
    cy.url().should('include', '/partner/dashboard');
    cy.contains('Your business is pending verification').should('be.visible');
    cy.window().its('localStorage.token').should('exist');
  });

  it('⚠️ Should handle validation errors', () => {
    cy.log('❌ Testing validation errors');
    // Test will verify:
    // - Field-level errors
    // - Form-level errors
    // - Error messages are clear

    // Setup test data
    const invalidUser = {
      name: 'T',
      email: 'invalid-email',
      password: 'short',
      role: null
    };

    // Mock API response for validation errors
    cy.intercept('POST', '/api/v1/auth/register', {
      statusCode: 422,
      body: {
        message: 'The given data was invalid.',
        errors: {
          name: ['The name must be at least 3 characters.'],
          email: ['The email must be a valid email address.'],
          password: ['The password must be at least 8 characters.'],
          role: ['The role field is required.']
        }
      }
    }).as('registerRequest');

    // Visit registration page
    cy.visit('/register');
    
    // Fill form with invalid data
    cy.log('📝 Filling form with invalid data');
    cy.get('[data-test="name-input"]').type(invalidUser.name);
    cy.get('[data-test="email-input"]').type(invalidUser.email);
    cy.get('[data-test="password-input"]').type(invalidUser.password);
    cy.get('[data-test="register-button"]').click();
    
    // Verify client-side validation
    cy.log('🔍 Verifying client-side validation');
    cy.contains('Name must be at least 3 characters').should('be.visible');
    cy.contains('Email must be valid').should('be.visible');
    cy.contains('Password must be at least 8 characters').should('be.visible');
    cy.contains('Account type is required').should('be.visible');
    
    // Fix some fields and submit
    cy.log('🛠 Fixing some fields');
    cy.get('[data-test="name-input"]').clear().type('Valid Name');
    cy.get('[data-test="email-input"]').clear().type('valid@email.com');
    cy.get('[data-test="password-input"]').clear().type('validpassword');
    cy.get('[data-test="password-confirm-input"]').type('validpassword');
    cy.get('[data-test="register-button"]').click();
    
    // Verify server-side validation
    cy.log('📡 Verifying server-side validation');
    cy.wait('@registerRequest');
    cy.contains('The name must be at least 3 characters').should('be.visible');
    cy.contains('The email must be a valid email address').should('be.visible');
    cy.contains('The password must be at least 8 characters').should('be.visible');
    cy.contains('The role field is required').should('be.visible');
  });

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
      email: 'mobile@todoke.test',
      password: 'password123',
      role: 'customer'
    };

    // Mock API response
    cy.intercept('POST', '/api/v1/auth/register', {
      statusCode: 201,
      body: {
        user: {
          id: 4,
          name: user.name,
          email: user.email,
          type: user.role
        },
        token: 'fake-jwt-token'
      }
    }).as('registerRequest');

    // Visit registration page
    cy.visit('/register');

    // Test form usability
    cy.log('📱 Testing form usability');
    cy.get('[data-test="name-input"]').should('be.visible').type(user.name);
    cy.get('[data-test="email-input"]').should('be.visible').type(user.email);
    cy.get('[data-test="password-input"]').should('be.visible').type(user.password);
    cy.get('[data-test="password-confirm-input"]').should('be.visible').type(user.password);
    
    // Test role selection
    cy.get('[data-test="role-select"]').click();
    cy.get('.v-list-item').contains('Customer').click();
    
    // Test keyboard behavior
    cy.log('⌨️ Testing keyboard behavior');
    cy.get('[data-test="name-input"]').clear().type(user.name).blur();
    cy.get('[data-test="email-input"]').click();
    cy.get('[data-test="email-input"]').should('be.focused');
    
    // Test no horizontal scrolling
    cy.log('↔️ Testing no horizontal scroll');
    cy.document().its('documentElement').should('have.prop', 'scrollWidth')
      .then((scrollWidth) => {
        cy.viewport('iphone-x').then(() => {
          cy.window().its('innerWidth').should('equal', scrollWidth);
        });
      });

    // Submit form
    cy.get('[data-test="register-button"]').click();
    cy.wait('@registerRequest');
    cy.url().should('include', '/customer/dashboard');
  });
});
