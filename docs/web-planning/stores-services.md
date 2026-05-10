# Web Area Planning: Stores and Services

## Purpose
Plan for frontend state management (stores) and reusable logic (services) to handle data fetching, authentication, and other cross-cutting concerns.

## State Management (Stores - Pinia)
- **Auth Store:**
    - Manages user authentication state (logged in/out).
    - Stores user information (ID, name, email, type, token, language preference).
    - Handles login, registration, and logout actions (interacting with API).
    - Persists authentication state (e.g., using local storage).
- **Translation Store (Sprint 4):**
    - Manages current language selection and fallback logic
    - Loads and caches translation files dynamically
    - Provides translation lookup methods with:
      - String interpolation support
      - Pluralization support
      - Fallback to default language
    - Persists language preference in localStorage
    - Syncs with user profile when authenticated
    - Emits events on language change
    - Unit tested with 100% coverage
- **Cart Store:**
    - Manages the state of the customer's shopping cart.
    - Stores selected products, quantities, and addons.
    - Provides methods to add, remove, and update items.
    - Calculates subtotal and total value.
    - Persists cart state (e.g., using local storage).
- **User Data Store:**
    - Stores currently logged-in user's profile information (address, payment methods).
    - Provides methods to fetch and update user data (interacting with API).
- **Notifications Store:**
    - Manages UI notifications (alerts).
    - Stores a list of active notifications.
    - Provides methods to add and remove notifications.
- **Loading Store:**
    - Manages global loading state.
    - Indicates when an asynchronous operation is in progress.

## Frontend Services
- **API Service (Axios):**
    - Centralized service for making HTTP requests to the backend API using Axios.
    - Handles setting authorization headers and Accept-Language header (Sprint 4).
    - Provides methods for different API calls (GET, POST, PUT, PATCH, DELETE).
    - Implements error handling and potentially request interceptors.
- **Translation Service (Sprint 4):**
    - Centralized service for all translation operations
    - Handles language switching with:
      - Browser language detection
      - User preference override
      - Fallback to English
    - Provides translation utilities including:
      - String interpolation
      - Pluralization
      - Date/number formatting
      - Right-to-left (RTL) support
    - Loads and caches translation files
    - Supports hot reload of translations in development
    - Unit tested with 100% coverage
- **Auth Service:**
    - Encapsulates authentication logic.
    - Interacts with the Auth Store and API Service for login, registration, and logout.
- **User Service:**
    - Encapsulates user-related logic.
    - Interacts with the User Data Store and API Service for fetching and updating user profile information.
- **Delivery Service:**
    - Encapsulates delivery-related logic for customers and couriers.
    - Interacts with the API Service for fetching available deliveries, accepting assignments, and updating statuses.
- **Partner Service:**
    - Encapsulates partner-related logic.
    - Interacts with the API Service for fetching metrics, managing orders, products, addons, and regions.
- **Admin Service:**
    - Encapsulates admin-related logic.
    - Interacts with the API Service for user management and fetching system stats.
- **Support Service:**
    - Encapsulates support ticket logic.
    - Interacts with the API Service for submitting and retrieving support tickets.
- **Map Service:**
    - Encapsulates logic for map integration (e.g., geocoding, displaying routes).
    - Interacts with a mapping library (Leaflet or Google Maps API).

## Implementation Considerations
- Use Pinia for state management due to its simplicity and performance.
- Place stores in a dedicated directory (e.g., `src/stores/`).
- Place services in a dedicated directory (e.g., `src/services/`).
- Services should primarily interact with the API Service and relevant stores.
- Implement proper error handling within services and propagate errors to Components/stores.
- Utilize environment variables for API base URL and other configuration.

## Potential Files
- `src/stores/auth.js`
- `src/stores/cart.js`
- `src/stores/userData.js`
- `src/stores/notifications.js`
- `src/stores/loading.js`
- `src/services/api.js`
- `src/services/auth.js`
- `src/services/user.js`
- `src/services/delivery.js`
- `src/services/partner.js`
- `src/services/admin.js`
- `src/services/support.js`
- `src/services/map.js`
