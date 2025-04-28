# TODOKE Technical Context

## Core Technology Stack

### Backend
- **Framework**: Laravel 12
- **PHP Version**: 8.2+
- **Key Packages**:
  - Laravel Sanctum (API authentication)
  - Laravel Tinker (REPL)
  - Faker (Test data generation)

### Frontend
- **Build Tool**: Vite 6
- **CSS Framework**: Tailwind CSS 4
- **HTTP Client**: Axios

### Testing
- PHPUnit 11
- Mockery
- Laravel Pail (log viewer)

## Development Setup

### Key Commands
```bash
# Run full development environment
composer dev

# Run tests
composer test

# Frontend development
npm run dev

# Production build
npm run build
```

### Database
- SQLite for development (default)
- Migration system for schema management
- Factories for test data generation

## API Characteristics
- RESTful design
- Sanctum token authentication
- JSON responses
- Status code standardization:
  - 200: Success
  - 201: Created
  - 422: Validation error
  - 403: Forbidden

## Deployment Considerations
- Vite-based asset compilation
- Environment variable configuration
- Queue worker for async tasks
- Database migration on deployment
