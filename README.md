# NOVA MOTORS

NOVA MOTORS is a PHP-based car rental web application with a modern catalog UI, booking workflow, user account area, and admin panel for booking moderation and fleet management.

## Project Purpose

The project provides:

- A public catalog of rental cars with pricing and specs
- Car detail pages with booking form and optional 3D preview
- User authentication and personal profile/bookings area
- Admin tools to approve/reject bookings and manage cars (create/edit/delete, image upload)

## Stack

### Backend

- PHP (procedural pages + shared utility modules)
- MySQL (via `mysqli`)
- Session-based authentication/authorization
- File-based catalog overrides (`cars_admin_overrides.php`) for admin car CRUD persistence

### Frontend

- HTML5
- CSS3 (custom styles in `style.css`)
- Vanilla JavaScript (`app.js`, `drive_lab.js`, `quick_actions.js`, `hyperdrive.js`)
- Bootstrap 5 (layout/components)
- Bootstrap Icons
- `<model-viewer>` web component for 3D model rendering

### Runtime Environment

- XAMPP (Apache + PHP + MySQL) style local setup

## Main Modules

- `index.php` - landing page, catalog, Drive Lab entry
- `car.php` - car details, specs, booking flow, 3D modal viewer
- `profile.php` - user profile, bookings list, account forms
- `admin.php` - booking moderation + car management CRUD
- `database.php` - DB access layer (users, bookings, payments, admin checks)
- `cars_catalog.php` - base catalog, formatting/localization helpers, admin override persistence
- `style.css` - full visual system and responsive behavior

## Visual Features

### Landing & Navigation

- Fixed top navigation bar with auth-aware controls
- Hero section with brand-first presentation
- Smooth anchor scrolling between sections

### Catalog UI

- Card-based car grid with:
- Car name/type
- Image preview
- Quick specs (fuel, transmission, seats)
- Daily price in rubles
- Direct navigation to detail page
- Staggered reveal animation for cards

### Motion & Effects

- IntersectionObserver-based reveal animations (`fade-in`, `fade-in-left`, `fade-in-right`, `scale-in`)
- Transition delays for sequential card appearance
- Hover interactions on buttons/cards

### Drive Lab / Interactive Layer

- Dedicated “Drive Lab” entry section
- Animated glow/ring visual accents
- Additional interactive scripts for quick actions and enhanced UX flows

### Car Detail Experience

- Large hero image + optional gallery thumbnails
- Structured specs block
- Price and booking CTA group
- Optional 3D visualization modal:
- Native `.glb` rendering via `<model-viewer>`
- Embedded external 3D source fallback (when configured)

### Booking UX

- Date range selection with validation
- Automatic days/total calculation
- Payment method selection
- Submit-state control and error/success feedback

### Profile Area

- Sidebar layout with mobile menu behavior
- Separate account and bookings views
- Booking status display
- Responsive cards/lists for user data

### Admin UI

- Booking moderation table (approve/reject)
- Booking statuses and summary badges
- Car management section:
- Create/edit/delete cars
- Photo upload button + optional image URL/path
- Unified action button sizing and responsive layout

## Data & Business Logic Highlights

- Booking overlap checks to prevent double-booking (`isCarAvailable`)
- Price formatting and spec localization centralized in `cars_catalog.php`
- Car catalog supports:
- Base hardcoded seed catalog
- Admin “upsert + deleted ids” override layer
- Safe fallback behavior for optional integrations (e.g., booking notifications)

## Security / Validation Notes

- Session checks on private/admin pages
- Admin rights validation through DB role checks
- Server-side validation for booking and car form data
- Uploaded image MIME checks for admin car photo upload (`jpeg/png/webp`)

## Static Assets & Integrations

- Favicon set in `favicon/`
- Car and brand images in `img/`
- Optional local 3D car models in `models/cars/`
- CDN assets:
- Bootstrap CSS/JS
- Bootstrap Icons
- Google `model-viewer`

## Current Project Character

- Monolithic page-per-feature PHP architecture
- Shared utilities and helpers for DRY logic reuse
- Strong visual emphasis on animated catalog browsing and conversion-focused booking flows
