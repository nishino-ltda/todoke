# Web Area Planning: Common Components

## Purpose
Identify and plan for reusable Vue components that will be used across multiple web areas to ensure consistency and reduce development time.

## Key Common Components
- **Header/Navigation:**
    - Displays site logo and navigation links.
    - Links should be dynamic based on user authentication status and role (Customer, Partner, Courier, Admin).
    - Includes login/registration buttons or user profile link.
    - (Potential) Notification indicator.
- **Footer:**
    - Displays copyright information, links to important pages (e.g., Terms of Service, Privacy Policy), and social media links.
- **Login/Registration Forms/Modals:**
    - Reusable forms for user authentication and new user registration.
    - Should handle different user types (Customer, Partner, Courier).
    - Includes input validation and error display.
- **Address Input:**
    - Component for entering and potentially validating addresses.
    - Could integrate with a geocoding service for address lookup and validation.
- **Payment Method Input:**
    - Component for selecting or entering payment information.
    - Will require integration with a payment gateway.
- **Data Tables:**
    - Reusable component for displaying tabular data with sorting, filtering, and pagination (Vuetify's `v-data-table` is a strong candidate).
- **Modals/Dialogs:**
    - Standardized components for displaying information or collecting input in a modal window.
- **Alerts/Notifications (UI):**
    - Components for displaying success, error, or informational messages to the user.
- **Loading Indicators:**
    - Components to indicate when data is being loaded or an action is in progress.

## Implementation Considerations
- Place common components in a dedicated directory (e.g., `src/components/common/`).
- Use Vue props to make components flexible and reusable.
- Utilize Vuetify components as the base for common components where possible.
- Implement proper event handling for communication between components.
- Consider using slots for flexible content injection.
  - **Internationalization**:
    - **Translation Files**:
      - JSON format for frontend translations (resources/lang/{locale}.json)
      - PHP arrays for backend translations (resources/lang/{locale}/)
      - pt-BR as first supported language (Sprint 4)
      - Extensible structure for additional languages
    - **Component Implementation**:
      - All UI text externalized to translation files (Sprint 4)
      - Use Vue I18n for text interpolation
      - Support dynamic language switching (Sprint 4)
      - Include data-test attributes for translation testing
    - **Language Selection**:
      - Dropdown component in user profile (Sprint 4)
      - Persist user preference in auth store (Sprint 4)
      - Browser language detection (Sprint 4)
      - Fallback to English when translation missing
    - **Testing**:
      - Unit tests for translation functionality (Sprint 4)
      - Verify components render correctly in different languages (Sprint 4)
      - Test fallback behavior (Sprint 4)

## Potential Components
- `AppHeader.vue`
- `AppFooter.vue`
- `AuthForm.vue` (can be used for both login and registration with props)
- `AddressInput.vue`
- `PaymentMethodInput.vue`
- `DataTable.vue` (wrapper around `v-data-table` or direct usage)
- `AppModal.vue`
- `AppAlert.vue`
- `LoadingIndicator.vue`
