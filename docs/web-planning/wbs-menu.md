# Work Breakdown Structure: Menu

## 1. Page Structure
- [ ] Implement dual routing:
  - [ ] Traditional `/menu/:partnerSlug` route
  - [ ] Direct `/:partnerSlug` route support
  - [ ] Error handling for invalid slugs (with translated messages)
- [ ] Implement partner-specific layout (header with branding)
- [ ] Create responsive product grid with translation support

## 2. Product Display
- [x] Create Product List component (`ProductList.vue`) with translation support
- [x] Create Product Card component (`ProductCard.vue`) with translation support
- [x] Display product details (name, description, price) from translated content
- [ ] Show product variations (if applicable) with translated labels
- [x] Implement product search/filter functionality with translated placeholders/errors

## 3. Product Details
- [x] Create Product Details modal (`ProductDetailsModal.vue`) with translation support
- [ ] Add image gallery/carousel with translated captions
- [x] Implement addon selection with translated labels
- [x] Add quantity selector with translated aria-labels

## 4. Shopping Cart
- [x] Create Cart Icon component (`CartIcon.vue`) with translated aria-labels
- [ ] Create Cart modal/page (`Cart.vue`) with full translation support
- [x] Implement cart state management (Pinia store with localStorage persistence)
- [x] Add/remove items functionality with translated confirmation messages
- [x] Calculate and display subtotal with translated labels

## 5. Checkout Flow
- [x] Create Checkout Form component (`CheckoutForm.vue`) with full translation support
- [x] Collect delivery address with translated labels/placeholders
- [x] Implement payment method selection with translated options
- [x] Add form validation with translated error messages
- [x] Handle order submission with translated success/error messages

## 6. Authentication Integration
- [ ] Add login/registration prompts when needed (translated)
- [ ] Link to auth endpoints with translated error handling

## 7. API Integration
- [ ] Fetch partner details with locale parameter
- [ ] Fetch product list with translated content
- [ ] Fetch product addons with translated names/descriptions
- [ ] Submit orders with locale context

## 8. Testing
- [x] Unit tests for components (ProductList, ProductCard, CartStore, CartIcon, ProductDetailsModal, CheckoutForm, Menu, Checkout) including translation tests (43 tests passing)
- [ ] E2E tests for ordering flow (browsing, cart, persistence) with locale switching
- [x] Verify all UI text is externalized to translation files
- [x] Test fallback behavior when translations are missing
