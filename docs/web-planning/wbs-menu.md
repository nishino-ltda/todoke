# Work Breakdown Structure: Menu

## 1. Page Structure
- [ ] Implement dual routing:
  - [ ] Traditional `/menu/:partnerSlug` route
  - [ ] Direct `/:partnerSlug` route support
  - [ ] Error handling for invalid slugs (with translated messages)
- [ ] Implement partner-specific layout (header with branding)
- [ ] Create responsive product grid with translation support

## 2. Product Display
- [ ] Create Product List component (`ProductList.vue`) with translation support
- [ ] Create Product Card component (`ProductCard.vue`) with translation support
- [ ] Display product details (name, description, price) from translated content
- [ ] Show product variations (if applicable) with translated labels
- [ ] Implement product search/filter functionality with translated placeholders/errors

## 3. Product Details
- [ ] Create Product Detail modal (`ProductDetail.vue`) with translation support
- [ ] Add image gallery/carousel with translated captions
- [ ] Implement addon selection (`AddonSelector.vue`) with translated labels
- [ ] Add quantity selector with translated aria-labels

## 4. Shopping Cart
- [ ] Create Cart Icon component (`CartIcon.vue`) with translated aria-labels
- [ ] Create Cart modal/page (`Cart.vue`) with full translation support
- [ ] Implement cart state management (Pinia store with localStorage persistence)
- [ ] Add/remove items functionality with translated confirmation messages
- [ ] Calculate and display subtotal with translated labels

## 5. Checkout Flow
- [ ] Create Checkout Form component (`CheckoutForm.vue`) with full translation support
- [ ] Collect delivery address with translated labels/placeholders
- [ ] Implement payment method selection with translated options
- [ ] Add form validation with translated error messages
- [ ] Handle order submission with translated success/error messages

## 6. Authentication Integration
- [ ] Add login/registration prompts when needed (translated)
- [ ] Link to auth endpoints with translated error handling

## 7. API Integration
- [ ] Fetch partner details with locale parameter
- [ ] Fetch product list with translated content
- [ ] Fetch product addons with translated names/descriptions
- [ ] Submit orders with locale context

## 8. Testing
- [ ] Unit tests for components (ProductList, ProductCard, CartStore) including translation tests
- [ ] E2E tests for ordering flow (browsing, cart, persistence) with locale switching
- [ ] Verify all UI text is externalized to translation files
- [ ] Test fallback behavior when translations are missing
