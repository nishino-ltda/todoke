# Work Breakdown Structure: Menu

## 1. Page Structure
- [x] Implement dual routing:
  - Traditional `/menu/:partnerId` route
  - Direct `/:partnerId` route support
  - Error handling for invalid slugs
- [ ] Implement partner-specific layout (header with branding)
- [ ] Create responsive product grid

## 2. Product Display
- [ ] Create Product List component (`ProductList.vue`)
- [ ] Create Product Card component (`ProductCard.vue`)
- [ ] Display product details (name, description, price)
- [ ] Show product variations (if applicable)
- [ ] Implement product search/filter functionality

## 3. Product Details
- [ ] Create Product Detail modal (`ProductDetail.vue`)
- [ ] Add image gallery/carousel
- [ ] Implement addon selection (`AddonSelector.vue`)
- [ ] Add quantity selector

## 4. Shopping Cart
- [ ] Create Cart Icon component (`CartIcon.vue`)
- [ ] Create Cart modal/page (`Cart.vue`)
- [x] Implement cart state management (Pinia store with localStorage persistence)
- [x] Add/remove items functionality
- [x] Calculate and display subtotal

## 5. Checkout Flow
- [ ] Create Checkout Form component (`CheckoutForm.vue`)
- [ ] Collect delivery address
- [ ] Implement payment method selection
- [ ] Add form validation
- [ ] Handle order submission

## 6. Authentication Integration
- [ ] Add login/registration prompts when needed
- [ ] Link to auth endpoints

## 7. API Integration
- [ ] Fetch partner details
- [ ] Fetch product list
- [ ] Fetch product addons
- [ ] Submit orders

## 8. Testing
- [x] Unit tests for components (ProductList, ProductCard, CartStore)
- [x] E2E tests for ordering flow (browsing, cart, persistence)
