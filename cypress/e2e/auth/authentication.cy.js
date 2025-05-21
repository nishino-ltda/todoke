describe('User Authentication E2E Tests', () => {
  before(() => {
    cy.fixture('authentication').as('authData');
  });

  // SPRINT 1: Core authentication testing
  it('should handle successful customer login and logout', function() {
    cy.log('🔑 Starting customer login/logout test');
    
    cy.log('👤 Creating test customer account');
    cy.request('POST', '/api/register', this.authData.users.customer)
      .then((response) => {
        expect(response.status).to.eq(201);
        cy.log('✅ Customer created successfully');
      });

    cy.log('🔐 Attempting login');
    cy.visit('/login');
    cy.get('[data-test="email-input"]').type(this.authData.users.customer.email);
    cy.get('[data-test="password-input"]').type(this.authData.users.customer.password);
    cy.get('[data-test="login-button"]').click();

    // Verify successful login redirect
    cy.url().should('include', '/dashboard');
    cy.log('✅ Login successful - redirected to dashboard');

    // Verify authenticated state
    cy.get('[data-test="user-menu"]').should('exist');
    cy.log('👋 User menu visible - authenticated state confirmed');

    // Test logout
    cy.log('🚪 Testing logout');
    cy.get('[data-test="logout-button"]').click();
    cy.url().should('include', '/login');
    cy.log('✅ Logout successful - redirected to login');

    // Cleanup
    cy.log('🧹 Cleaning up test customer');
    cy.request('DELETE', `/api/users/${this.authData.users.customer.email}`);
  });

  // SPRINT 1: Core authentication testing
  it('should handle successful partner login and logout', function() {
    cy.log('🔑 Testing successful partner login and logout');
    
    cy.log('👤 Creating test partner account');
    cy.request('POST', '/api/register', this.authData.users.partner)
      .then((response) => {
        expect(response.status).to.eq(201);
        cy.log('✅ Partner created successfully');
      });

    cy.log('🔐 Attempting login');
    cy.visit('/login');
    cy.get('[data-test="email-input"]').type(this.authData.users.partner.email);
    cy.get('[data-test="password-input"]').type(this.authData.users.partner.password);
    cy.get('[data-test="login-button"]').click();

    cy.url().should('include', '/partner/dashboard');
    cy.log('✅ Login successful - redirected to partner dashboard');

    // Cleanup
    cy.log('🧹 Cleaning up test partner');
    cy.request('DELETE', `/api/users/${this.authData.users.partner.email}`);
  });

  // SPRINT 1: Core authentication testing
  it('should handle successful courier login and logout', function() {
    cy.log('🔑 Testing successful courier login and logout');
    
    cy.log('👤 Creating test courier account');
    cy.request('POST', '/api/register', this.authData.users.courier)
      .then((response) => {
        expect(response.status).to.eq(201);
        cy.log('✅ Courier created successfully');
      });

    cy.log('🔐 Attempting login');
    cy.visit('/login');
    cy.get('[data-test="email-input"]').type(this.authData.users.courier.email);
    cy.get('[data-test="password-input"]').type(this.authData.users.courier.password);
    cy.get('[data-test="login-button"]').click();

    cy.url().should('include', '/courier/dashboard');
    cy.log('✅ Login successful - redirected to courier dashboard');

    // Cleanup
    cy.log('🧹 Cleaning up test courier');
    cy.request('DELETE', `/api/users/${this.authData.users.courier.email}`);
  });

  // SPRINT 1: Core authentication testing
  it('should handle successful admin login and logout', function() {
    cy.log('🔑 Testing successful admin login and logout');
    
    cy.log('👤 Creating test admin account');
    cy.request('POST', '/api/register', this.authData.users.admin)
      .then((response) => {
        expect(response.status).to.eq(201);
        cy.log('✅ Admin created successfully');
      });

    cy.log('🔐 Attempting login');
    cy.visit('/login');
    cy.get('[data-test="email-input"]').type(this.authData.users.admin.email);
    cy.get('[data-test="password-input"]').type(this.authData.users.admin.password);
    cy.get('[data-test="login-button"]').click();

    cy.url().should('include', '/admin/dashboard');
    cy.log('✅ Login successful - redirected to admin dashboard');

    // Cleanup
    cy.log('🧹 Cleaning up test admin');
    cy.request('DELETE', `/api/users/${this.authData.users.admin.email}`);
  });

  // SPRINT 1: Core authentication testing
  it('should prevent access to protected routes without authentication', function() {
    cy.log('🚫 Testing protected route access without authentication');
    
    const protectedRoutes = [
      '/dashboard',
      '/partner/dashboard',
      '/courier/dashboard',
      '/admin/dashboard'
    ];

    protectedRoutes.forEach(route => {
      cy.log(`🔒 Testing ${route} access without authentication`);
      cy.visit(route);
      cy.url().should('include', '/login');
      cy.log(`✅ ${route} protected - redirected to login`);
    });
  });

  // SPRINT 1: Core authentication testing
  it('should handle incorrect login credentials', () => {
    cy.log('❌ Testing incorrect login credentials');
    
    cy.log('👤 Creating test customer account');
    cy.request('POST', '/api/register', this.authData.users.customer)
      .then((response) => {
        expect(response.status).to.eq(201);
        cy.log('✅ Customer created successfully');
      });

    cy.log('🔐 Testing invalid email format');
    cy.visit('/login');
    cy.get('[data-test="email-input"]').type(this.authData.invalidCredentials.invalidEmail);
    cy.get('[data-test="password-input"]').type(this.authData.users.customer.password);
    cy.get('[data-test="login-button"]').click();
    
    // Verify error message and no redirect
    cy.get('[data-test="auth-alert"]').should('contain', 'Invalid email format');
    cy.url().should('include', '/login');
    cy.log('✅ Invalid email format handled correctly');

    cy.log('🔐 Testing incorrect password');
    cy.get('[data-test="email-input"]').clear().type(this.authData.users.customer.email);
    cy.get('[data-test="password-input"]').clear().type(this.authData.invalidCredentials.invalidPassword);
    cy.get('[data-test="login-button"]').click();
    
    // Verify error message and no redirect
    cy.get('[data-test="auth-alert"]').should('contain', 'Invalid credentials');
    cy.url().should('include', '/login');
    cy.log('✅ Invalid credentials handled correctly');

    // Cleanup
    cy.log('🧹 Cleaning up test customer');
    cy.request('DELETE', `/api/users/${this.authData.users.customer.email}`);
  });
});
