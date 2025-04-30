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
