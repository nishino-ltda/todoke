# Web Area Planning: Support

## Purpose
Provide a common area for all user types (Customer, Partner, Courier, Admin) to open support tickets and view their status in their preferred language.

## Key Features
- Secure access for authenticated users with language preference.
- Form for submitting new support tickets:
    - Select ticket category (translated options).
    - Provide a detailed description (locale-aware editor).
    - Option to attach relevant files (e.g., screenshots).
- List of submitted tickets with translated statuses (e.g., Open, In Progress, Closed).
- Ability to view details of a specific ticket with translated labels.
- Language selector for switching interface language.
- (Potential Future) Communication thread with translation support.

## Implementation Considerations
- Implement protected routes accessible by any authenticated user with locale support.
- Utilize Vuetify components with i18n integration for forms, lists, and modals.
- Create components with translation support:
  - Support ticket form (`SupportTicketForm.vue`)
  - Ticket list (`SupportTicketList.vue`)
- Requires new API endpoints with Accept-Language header support:
    - Submitting new tickets (`POST /api/v1/support/tickets`).
    - Listing user's tickets (`GET /api/v1/support/tickets`).
    - Viewing a specific ticket (`GET /api/v1/support/tickets/{id}`).
    - (Potential Future) Adding messages to a ticket (`POST /api/v1/support/tickets/{id}/messages`).
- Implement input validation with translated error messages.
- Store user's language preference in their profile.
- Default to pt-BR for Portuguese-speaking users.

## API Endpoints Used
- `POST /api/v1/support/tickets` (requires new endpoint with Accept-Language)
- `GET /api/v1/support/tickets` (requires new endpoint with Accept-Language)
- `GET /api/v1/support/tickets/{id}` (requires new endpoint with Accept-Language)
- `POST /api/v1/support/tickets/{id}/messages` (requires new endpoint with Accept-Language - potential future)

## Potential Components
- Support Layout component (`SupportLayout.vue`) with language selector
- Support Ticket Form component (`SupportTicketForm.vue`) with i18n
- Support Ticket List component (`SupportTicketList.vue`) with i18n
- Support Ticket Detail component (`SupportTicketDetail.vue`) with i18n
- Language Selector component (`LanguageSelector.vue`)
