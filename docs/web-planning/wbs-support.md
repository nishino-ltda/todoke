# Work Breakdown Structure: Support System

## 1. Page Structure
- [x] Create protected support route (already existed via web.php)
- [x] Implement support layout with navigation (`SupportLayout.vue` with sidebar)
- [x] Add responsive design
- [x] Add language selector component (shared `LanguageSelector` used via layouts)

## 2. Ticket Submission
- [x] Create Support Ticket Form (`TicketCreate.vue`) with translation support (subject, category, priority, message, file attachment)
- [x] Implement category selection with translated options (`support.categories.*`)
- [x] Add rich text description field with locale tracking (textarea via i18n)
- [x] Implement file attachment functionality (optional file input via FormData)
- [x] Add form validation with translated messages

## 3. Ticket Management
- [x] Create Support Ticket List (`Tickets.vue`) with translation support
- [x] Display user's tickets with translated statuses (colored chips)
- [x] Add search/filter functionality with translated labels (DataTable search + status filter buttons)
- [x] Implement pagination with translated controls (DataTable built-in)

## 4. Ticket Details
- [x] Create Support Ticket Detail (`TicketDetail.vue`) with translation support
- [x] Display full ticket information with translated labels (subject, status, category, priority, message thread)
- [x] Add message thread for communication with translation (chronological message list)
- [x] Implement ticket status updates (close ticket with confirmation dialog)

## 5. API Integration
- [x] Create SupportService frontend (`services/support.js`) with getTickets, getTicket, createTicket, addReply, getFaqs, closeTicket
- [x] Create SupportController backend with mock responses for all ticket operations
- [x] Register API routes (`/api/v1/support/tickets`, `/api/v1/support/faq`)
- [x] Submit new tickets with Accept-Language header (via global api service interceptor)
- [x] Fetch user's tickets with locale-specific responses
- [x] Get ticket details with translated content
- [x] (Future) Add messages to tickets with translation support

## 6. Testing
- [x] Unit tests for all 6 pages (12 tests passing across 6 spec files)
- [ ] E2E tests for ticket submission flow in multiple languages
- [ ] Test file upload functionality
- [ ] Test language switching functionality
