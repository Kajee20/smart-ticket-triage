# Smart Ticket Triage & Dashboard

A full-stack help-desk ticket triage application built with Laravel 11 and Vue 3.

## Features

- Create support tickets
- View and search tickets
- Filter tickets by status and category
- Paginate ticket results
- View individual ticket details
- Update ticket status
- Manually override ticket category
- Add and edit internal notes
- Queue AI classification jobs
- AI-generated category, explanation, and confidence
- Preserve manually selected categories during AI classification
- Dashboard with ticket statistics
- Category statistics chart
- CSV ticket export
- Responsive user interface

## Technology Stack

### Backend

- Laravel 11
- PHP 8.2+
- MySQL
- Laravel Queue
- OpenAI PHP Laravel package

### Frontend

- Vue 3
- Vite
- Vue Router
- JavaScript
- Plain CSS with BEM-style naming

## Project Structure

```text
smart-ticket-triage/
├── app/
│   ├── Http/Controllers/
│   ├── Jobs/
│   ├── Models/
│   └── Services/
├── database/
│   ├── migrations/
│   └── seeders/
├── frontend/
│   └── src/
│       ├── router/
│       └── views/
├── routes/
│   └── api.php
└── config/
``