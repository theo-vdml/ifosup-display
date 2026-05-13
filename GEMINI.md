# 📺 IFOSUP Display - Gemini Context

This document provides architectural overview, development guidelines, and technical context for the **IFOSUP Display** project.

## 🚀 Project Overview

**IFOSUP Display** is a dynamic display management system (Backoffice) designed for a school environment. It allows administrators to manage and schedule information (announcements, course schedules, news) displayed on school televisions.

### Core Stack
- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Inertia.js with Vue 3 & TypeScript
- **Styling:** Tailwind CSS 4
- **Database:** Relational (MySQL/PostgreSQL/SQLite)
- **Authentication:** Laravel Fortify
- **Routing:** Laravel Wayfinder (integrated with Vite)

### Key Features
- **Resources Management:** Teachers, Rooms, Groups, and Courses.
- **Scheduler:** A timeline interface to manage `Assignments`.
- **Recurrences:** Logic for handling `RecurringAssignments` that automatically populate the schedule.
- **Public Screen:** A dedicated route (`/screen`) for the dynamic display view.

---

## 🛠️ Building and Running

### Prerequisites
- PHP 8.2 or higher
- Node.js (Latest LTS recommended)
- `pnpm` (Package Manager)
- `composer`

### Installation
1.  **Dependencies:**
    ```bash
    composer install
    pnpm install
    ```
2.  **Environment:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    touch database/database.sqlite # If using SQLite
    php artisan migrate --seed
    ```

### Development Commands
- **Start All (Server, Vite, Queue, Logs):** `composer run dev`
- **Frontend Only:** `pnpm dev`
- **Build for Production:** `pnpm build`
- **SSR Build:** `pnpm build:ssr`

---

## 🧪 Testing and Validation

### Running Tests
- **Pest (PHP):** `php artisan test` or `composer run test` (runs linting before tests).
- **Type Checking:** `pnpm types:check` (runs `vue-tsc`).

### Code Quality
- **PHP Linting (Pint):** `composer run lint`
- **JS/TS Linting (ESLint):** `pnpm lint`
- **Formatting (Prettier):** `pnpm format`

---

## 📐 Development Conventions

### Backend (Laravel)
- **Strict Typing:** Use PHP 8.2 type hinting for properties, arguments, and return types.
- **Dates:** `CarbonImmutable` is enforced via `AppServiceProvider`.
- **Validation:** Use FormRequests for controller logic (e.g., `StoreCourseRequest`).
- **Models:** Follow standard Laravel Eloquent conventions. Key models: `Assignment`, `Course`, `Room`, `Teacher`, `Group`.
- **Periods:** Assignments are divided into three periods: `morning`, `afternoon`, `evening`.

### Frontend (Vue 3 + Inertia)
- **TypeScript:** All new components and logic should be written in TypeScript.
- **Components:** Organized in `resources/js/components/`. UI primitives (Shadcn-like) are in `resources/js/components/ui/`.
- **State:** Prefer Inertia's `useForm` for handling server-side interactions.
- **Styles:** Use Tailwind CSS 4 utility classes. Prefer CSS variables for theme customization (defined in `resources/css/app.css`).

### Project Structure Highlights
- `app/Http/Controllers/`: Contains the logic for resource management and the scheduler.
- `database/migrations/`: Defines the schema for assignments and recurrences.
- `resources/js/pages/`: Contains Inertia views.
- `resources/js/wayfinder/`: Contains routing/navigation logic powered by Laravel Wayfinder.
- `LOGBOOK.md`: Local development log (if present, check for recent changes).
