# Route - Controller - Vue Component Tracking

## Public Routes
| Route | Controller | Method | Vue Component | Exists | Notes |
|-------|------------|--------|---------------|--------|-------|
| / | HomeController | index | Home | ✅ | resources/js/Pages/Home.vue |
| /menu/{partner} | MenuController | show | Customer/Menu | ✅ | resources/js/Pages/Customer/Menu.vue |
| /{partner} | MenuController | show | Customer/Menu | ✅ | resources/js/Pages/Customer/Menu.vue |

## Authenticated Routes

### Support Routes
| Route | Controller | Method | Vue Component | Exists | Notes |
|-------|------------|--------|---------------|--------|-------|
| /support | SupportController | index | Support/Dashboard | ✅ | resources/js/Pages/Support/Dashboard.vue |
| /support/tickets | SupportController | tickets | Support/Tickets | ✅ | resources/js/Pages/Support/Tickets.vue |
| /support/tickets/create | SupportController | create | Support/TicketCreate | ✅ | resources/js/Pages/Support/TicketCreate.vue |
| /support/tickets/{id} | SupportController | show | Support/TicketDetail | ✅ | resources/js/Pages/Support/TicketDetail.vue |
| /support/tickets/{id}/reply | SupportController | reply | Support/TicketReply | ✅ | resources/js/Pages/Support/TicketReply.vue |
| /support/faq | SupportController | faq | Support/Faq | ✅ | resources/js/Pages/Support/Faq.vue |

### Customer Routes
| Route | Controller | Method | Vue Component | Exists | Notes |
|-------|------------|--------|---------------|--------|-------|
| /customer/dashboard | CustomerDashboardController | index | Customer/Dashboard | ✅ | resources/js/Pages/Customer/Dashboard.vue |
| /customer/menu | CustomerMenuController | index | Customer/Menu | ✅ | resources/js/Pages/Customer/Menu.vue |
| /customer/checkout | CustomerCheckoutController | index | Customer/Checkout | ✅ | resources/js/Pages/Customer/Checkout.vue |
| /customer/orders | CustomerOrderController | index | Customer/Orders/Index | ✅ | resources/js/Pages/Customer/Orders/Index.vue |
| /customer/orders/{id} | CustomerOrderController | show | Customer/Orders/Show | ✅ | resources/js/Pages/Customer/Orders/Show.vue |
| /customer/profile | CustomerProfileController | index | Customer/Profile | ✅ | resources/js/Pages/Customer/Profile.vue |
| /customer/terms | CustomerTermsController | index | Customer/Terms | ✅ | resources/js/Pages/Customer/Terms.vue |
| /customer/privacy | CustomerPrivacyController | index | Customer/Privacy | ✅ | resources/js/Pages/Customer/Privacy.vue |

### Partner Routes
| Route | Controller | Method | Vue Component | Exists | Notes |
|-------|------------|--------|---------------|--------|-------|
| /partner/dashboard | PartnerDashboardController | index | Partner/Dashboard | ✅ | resources/js/Pages/Partner/Dashboard.vue |
| /partner/orders | PartnerOrderController | index | Partner/Orders/Index | ✅ | resources/js/Pages/Partner/Orders/Index.vue |
| /partner/orders/create | PartnerOrderController | create | Partner/Orders/Create | ✅ | resources/js/Pages/Partner/Orders/Create.vue |
| /partner/orders/{id} | PartnerOrderController | show | Partner/Orders/Show | ✅ | resources/js/Pages/Partner/Orders/Show.vue |
| /partner/orders/batch-create | PartnerOrderController | batchCreate | Partner/Orders/BatchCreate | ✅ | resources/js/Pages/Partner/Orders/BatchCreate.vue |
| /partner/products | PartnerProductController | index | Partner/Products/Index | ✅ | resources/js/Pages/Partner/Products/Index.vue |
| /partner/products/create | PartnerProductController | create | Partner/Products/Create | ✅ | resources/js/Pages/Partner/Products/Create.vue |
| /partner/products/{id}/edit | PartnerProductController | edit | Partner/Products/Edit | ✅ | resources/js/Pages/Partner/Products/Edit.vue |
| /partner/products/{id} | PartnerProductController | show | Partner/Products/Show | ✅ | resources/js/Pages/Partner/Products/Show.vue |
| /partner/products/{product}/variations | PartnerVariationController | index | Partner/Products/Variations/Index | ✅ | resources/js/Pages/Partner/Products/Variations/Index.vue |
| /partner/products/{product}/variations/create | PartnerVariationController | create | Partner/Products/Variations/Create | ✅ | resources/js/Pages/Partner/Products/Variations/Create.vue |
| /partner/products/{product}/variations/{variation}/edit | PartnerVariationController | edit | Partner/Products/Variations/Edit | ✅ | resources/js/Pages/Partner/Products/Variations/Edit.vue |
| /partner/addons | PartnerAddonController | index | Partner/Addons/Index | ✅ | resources/js/Pages/Partner/Addons/Index.vue |
| /partner/addons/create | PartnerAddonController | create | Partner/Addons/Create | ✅ | resources/js/Pages/Partner/Addons/Create.vue |
| /partner/addons/{id}/edit | PartnerAddonController | edit | Partner/Addons/Edit | ✅ | resources/js/Pages/Partner/Addons/Edit.vue |
| /partner/addons/{id} | PartnerAddonController | show | Partner/Addons/Show | ✅ | resources/js/Pages/Partner/Addons/Show.vue |
| /partner/regions | PartnerRegionController | index | Partner/Regions/Index | ✅ | resources/js/Pages/Partner/Regions/Index.vue |
| /partner/regions/create | PartnerRegionController | create | Partner/Regions/Create | ✅ | resources/js/Pages/Partner/Regions/Create.vue |
| /partner/regions/{id}/edit | PartnerRegionController | edit | Partner/Regions/Edit | ✅ | resources/js/Pages/Partner/Regions/Edit.vue |
| /partner/regions/{id} | PartnerRegionController | show | Partner/Regions/Show | ✅ | resources/js/Pages/Partner/Regions/Show.vue |
| /partner/nodes | PartnerNodeController | index | Partner/Nodes/Index | ✅ | resources/js/Pages/Partner/Nodes/Index.vue |
| /partner/nodes/create | PartnerNodeController | create | Partner/Nodes/Create | ✅ | resources/js/Pages/Partner/Nodes/Create.vue |
| /partner/nodes/{id}/edit | PartnerNodeController | edit | Partner/Nodes/Edit | ✅ | resources/js/Pages/Partner/Nodes/Edit.vue |
| /partner/nodes/{id} | PartnerNodeController | show | Partner/Nodes/Show | ✅ | resources/js/Pages/Partner/Nodes/Show.vue |
| /partner/settings | PartnerSettingsController | index | Partner/Settings/Index | ✅ | resources/js/Pages/Partner/Settings/Index.vue |

## Missing Vue Components
All routes now have corresponding Vue components.

## Verification Complete
This document shows the complete mapping between routes, controllers and Vue components, highlighting any missing components.
