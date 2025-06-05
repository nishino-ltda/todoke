# Web Area Planning: Menu

## Purpose
Display a partner's menu, allow customers to browse products, add items to a cart, and place orders.

## Internationalization Requirements
- All UI text must use translation keys from JSON files
- Default language: pt-BR (Portuguese - Brazil)
- Support for English via language switcher
- Menu-specific translations should be in `menu.json` files
- Product descriptions and addon names should support multiple languages
- Cart and checkout messages must be translated
- Error messages during ordering must be localized

## Key Features
- Dynamic routing to display menus for specific partners (e.g., `/menu/:partnerId`).
- Display partner branding (logo, colors).
- List products with details, variations, and available addons.
- Allow customers to select product quantities and choose addons.
- Shopping cart functionality: add, remove, update quantities, display subtotal.
- Display real-time pricing, potentially reflecting community pricing adjustments.
- Customer login and registration integration for placing orders.
- Collect delivery information (address) and payment method.
- Order submission and confirmation.

## Implementation Considerations
- Utilize Vue Router for dynamic partner routes.
- Implement state management (Vuex or Pinia) for the shopping cart and user session.
- Create components for product display (`ProductCard.vue`), addon selection (`AddonSelector.vue`), shopping cart (`Cart.vue`), and checkout forms (`CheckoutForm.vue`).
- Integrate with the existing API for:
    - Fetching partner details and menu (`GET /api/v1/products`, potentially filtered by partner).
    - Fetching product addons (`GET /api/v1/products/{product}/addons`).
    - User authentication (`POST /api/v1/auth/login`, `POST /api/v1/auth/register`).
    - User profile updates (for address/payment, `PATCH /api/v1/users/me`).
    - Order submission (`POST /api/v1/orders`).
- Implement input validation for forms.
- Handle different payment methods (integration with a payment gateway will be needed).

## API Endpoints Used
- `GET /api/v1/products`
- `GET /api/v1/products/{product}/addons`
- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `PATCH /api/v1/users/me`
- `POST /api/v1/orders`

## Potential Components
- Partner Header component (`PartnerHeader.vue`)
- Product List component (`ProductList.vue`)
- Product Detail/Modal component (`ProductDetail.vue`)
- Addon Selector component (`AddonSelector.vue`)
- Cart Icon/Summary component (`CartIcon.vue`)
- Cart Modal/Page component (`Cart.vue`)
- Checkout Form component (`CheckoutForm.vue`)
- Login/Registration Modal/Page (common)
- Address Input component (common)
- Payment Method Input component (common)
