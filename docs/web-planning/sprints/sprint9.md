# Sprint 9: Support System (TDD Focus)

## References
- WBS: web-planning/wbs-support.md
- Planning: web-planning/support.md
- Routes: routes/web.php (Support routes), routes/api.php (Support API routes)
- Backend Controller: app/Http/Controllers/SupportController.php (Inertia), app/Http/Controllers/API/SupportController.php (API)
- Frontend Service: resources/js/services/support.js
- Frontend Service Tests: resources/js/services/__tests__/support.spec.js (6/6 passing)
- Translations: resources/lang/en/support.php (English)
- Translations: resources/lang/pt-BR/support.php (Portuguese)
- Vue Components (stubs):
  - resources/js/Pages/Support/Dashboard.vue
  - resources/js/Pages/Support/Faq.vue
  - resources/js/Pages/Support/TicketCreate.vue
  - resources/js/Pages/Support/TicketDetail.vue
  - resources/js/Pages/Support/TicketReply.vue
  - resources/js/Pages/Support/Tickets.vue
- E2E Tests (pending):
  - cypress/e2e/support/ticket-system.cy.js
  - cypress/e2e/support/tickets.cy.js

## Prerequisites (completed)
- [x] Create SupportController API backend (mock responses for tickets/FAQ)
- [x] Create SupportService frontend (getTickets, getTicket, createTicket, addReply, getFaqs)
- [x] Register API routes (`/api/v1/support/*`)
- [x] Unit tests for SupportService (6/6 passing with logging)

## Testing Goals
- [ ] Write E2E tests for:
  - [ ] Ticket submission flow
  - [ ] Ticket management
  - [ ] Language switching functionality
- [ ] Write unit tests for:
  - [ ] SupportForm component (including translation)
  - [ ] TicketList component (including translation)

## Implementation Tasks
1. **Support Components**:
   - [ ] Create SupportForm component with translation support
   - [ ] Implement TicketList component with translation support
   - [ ] Add ticket status tracking (translated)
   - [ ] Create FAQ section with multilingual content
   - [ ] Add language selector to support interface

2. **Support Management**:
   - [ ] Connect to Support API with Accept-Language header
   - [ ] Implement ticket submission with locale tracking
   - [ ] Add response functionality with translation support
   - [ ] Create knowledge base with multilingual content

3. **State Management**:
   - [ ] Implement Support Store with locale tracking
   - [ ] Add ticket history with translated statuses
   - [ ] Connect to notifications with translation support

## Acceptance Criteria
- Users can submit support tickets in their preferred language
- Tickets can be tracked with translated statuses
- FAQ content is accessible in multiple languages
- Responses are delivered via notifications in user's language
- System defaults to pt-BR for Portuguese-speaking users
- New languages can be added by creating translation files
