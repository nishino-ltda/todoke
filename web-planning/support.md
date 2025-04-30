# Web Area Planning: Support

## Purpose
Provide a common area for all user types (Customer, Partner, Courier, Admin) to open support tickets and view their status.

## Key Features
- Secure access for authenticated users.
- Form for submitting new support tickets:
    - Select ticket category (e.g., Technical Issue, Billing, Delivery Problem).
    - Provide a detailed description.
    - Option to attach relevant files (e.g., screenshots).
- List of submitted tickets with their current status (e.g., Open, In Progress, Closed).
- Ability to view details of a specific ticket.
- (Potential Future) Communication thread within a ticket for back-and-forth with support.

## Implementation Considerations
- Implement protected routes accessible by any authenticated user.
- Utilize Vuetify components for forms, lists, and modals.
- Create components for the support ticket form (`SupportTicketForm.vue`) and the ticket list (`SupportTicketList.vue`).
- Requires new API endpoints for:
    - Submitting new tickets (`POST /api/v1/support/tickets`).
    - Listing user's tickets (`GET /api/v1/support/tickets`).
    - Viewing a specific ticket (`GET /api/v1/support/tickets/{id}`).
    - (Potential Future) Adding messages to a ticket (`POST /api/v1/support/tickets/{id}/messages`).
- Implement input validation for the support ticket form.

## API Endpoints Used
- `POST /api/v1/support/tickets` (requires new endpoint)
- `GET /api/v1/support/tickets` (requires new endpoint)
- `GET /api/v1/support/tickets/{id}` (requires new endpoint)
- `POST /api/v1/support/tickets/{id}/messages` (requires new endpoint - potential future)

## Potential Components
- Support Layout component (`SupportLayout.vue`)
- Support Ticket Form component (`SupportTicketForm.vue`)
- Support Ticket List component (`SupportTicketList.vue`)
- Support Ticket Detail component (`SupportTicketDetail.vue`)
