# Nabdh Backend API

This is the backend for Nabd, a platform built to help people in the Gaza Strip find essential commodities and track their prices during the ongoing supply crisis. With commercial crossings closed and goods scarce, this system helps connect people who need basic necessities with those who have them available.

## What This Platform Does

Nabd tracks essential commodity prices in real-time and helps people locate where they can find flour, sugar, cooking oil, and other critical supplies. The platform lets vendors list what they have available, allows people to exchange goods when cash isn't an option, and keeps everyone informed about price changes and product availability.

## The Four Core Modules

**Admin Dashboard** - Manages the entire system including verifying vendors to ensure legitimacy, updating commodity data, monitoring platform activity, and maintaining data integrity so people can trust the information they see.

**Exchange Module** - Enables item-for-item trading when money isn't available. People can propose exchanges, negotiate through the platform, and complete trades to get what they need.

**Notifications System** - Keeps users informed through the platform, email, and SMS/WhatsApp about price updates, when essential items become available, and vendor verification status.

**Home Page Backend** - Powers the main interface with product listings, city-based filtering, recent updates, and optimized data delivery for low-bandwidth conditions common in Gaza.

## Technical Foundation

Built with Laravel 10 using these key components:

- Laravel Sanctum for secure API authentication
- Pusher for real-time price updates and messaging
- Spatie Laravel Permission for role management (admin, vendor, user)
- Twilio SDK for SMS notifications about critical updates
- Laravel Socialite for simplified Google login
- MySQL for reliable data storage
- Redis for caching to reduce bandwidth usage

## Getting Started

You need PHP 8.1 or higher, Composer, MySQL, and Node.js installed.

Navigate to the nabdh folder:

```bash
cd nabdh
```

Install dependencies:

```bash
composer install
```

Copy the environment template:

```bash
cp .env.example .env
```

Configure your .env file with database and service credentials:

```env
APP_NAME=Nabd
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nabd_db
DB_USERNAME=root
DB_PASSWORD=your_password

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_key
PUSHER_APP_SECRET=your_secret
PUSHER_APP_CLUSTER=your_cluster

TWILIO_SID=your_twilio_sid
TWILIO_AUTH_TOKEN=your_twilio_token
TWILIO_PHONE_NUMBER=your_twilio_number

GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/google/callback
```

Generate your application key:

```bash
php artisan key:generate
```

Set up the database:

```bash
php artisan migrate
```

Load initial data including essential commodity categories and Gaza cities:

```bash
php artisan db:seed
```

This creates the admin account, sets up categories for essential goods like flour, sugar, oil, rice, and loads Gaza Strip city data.

Link storage for product images:

```bash
php artisan storage:link
```

Start the server:

```bash
php artisan serve
```

The API runs at http://localhost:8000

## Database Structure

**User** - Everyone using the platform (regular users, vendors, admins) with role-based access

**Store** - Vendor locations selling essential commodities, requires admin verification before appearing publicly

**Product** - Essential items listed with current prices and availability

**Category** - Essential commodity types (flour, sugar, cooking oil, rice, etc.)

**City** - Gaza Strip cities and neighborhoods for location-based searches

**Barter** - Exchange proposals when people need to trade items instead of buying

**BarterOffer** - Specific items offered in an exchange

**BarterMessage** - Negotiation messages between people arranging trades

**Conversation** - Direct chat between users and vendors

**Message** - Individual messages within conversations

**Favorite** - Items people track to monitor price changes

**Report** - User reports about incorrect prices or suspicious vendors

**UserNotification** - All notification types including price alerts and availability updates

**PasswordOtp** - Verification codes for account security

## User Roles

**Regular User** - Browse commodities, track prices, save favorites, propose exchanges, chat with vendors

**Vendor** - List essential goods they have available, set prices, respond to exchange offers, must be verified by admin

**Admin** - Verify new vendors, monitor prices for accuracy, manage categories, handle reports, maintain platform integrity

## Core API Routes

All routes start with /api. Most require authentication through Sanctum tokens.

**Public Access:**
- POST /register - Create account
- POST /login - Sign in and get token
- POST /forgot-password - Reset password request
- POST /reset-password - Complete password reset
- GET /categories - Essential commodity categories
- GET /products - Browse available items
- GET /auth/google - Google sign-in option

**User Routes (authenticated):**
- GET /customer/stores - Find vendors by location
- POST /customer/barters - Propose an exchange
- GET /customer/barters - View your exchange offers
- PUT /customer/barters/{id}/respond - Respond to exchange proposal
- POST /customer/favorites - Track item for price alerts
- DELETE /customer/favorites/{id} - Stop tracking item
- GET /customer/notifications - Get your notifications
- PATCH /notifications/mark-as-read - Mark notifications read
- GET /products - Browse essential commodities with filters
- GET /search/stores/{product} - Find where specific items are available

**Vendor Routes (authenticated vendors):**
- PUT /merchant/store - Register or update vendor information
- POST /merchant/products - List new essential item
- PUT /merchant/products/{id} - Update price or availability
- DELETE /merchant/products/{id} - Remove listing
- GET /merchant/offers - View incoming exchange proposals
- PUT /merchant/offers/{id} - Accept or decline exchanges

**Admin Routes (authenticated admins):**
- GET /admin/dashboard - Platform statistics and monitoring
- GET /admin/stores - Pending vendor verifications
- PUT /admin/stores/{id} - Approve or reject vendors
- GET /admin/products - Review commodity listings
- PUT /admin/products/{id} - Edit or remove listings
- POST /admin/categories - Add essential commodity category
- PUT /admin/categories/{id} - Update category
- DELETE /admin/categories/{id} - Remove category
- GET /admin/reports - User-submitted reports
- PUT /admin/reports/{id} - Handle report
- GET /admin/users - User management
- PUT /admin/users/{id} - Update user or change role

**Messaging Routes (authenticated):**
- GET /conversations - Your conversations
- POST /conversations - Start conversation with vendor
- GET /conversations/{id}/messages - Load messages
- POST /conversations/{id}/messages - Send message

## Real-Time Updates

Using Laravel broadcasting with Pusher for immediate notifications:

**Price changes** - Users tracking items get instant alerts when prices update

**New availability** - Notifications when tracked items become available

**Exchange offers** - Immediate notification when someone proposes a trade

**Messages** - Real-time chat delivery

**Admin alerts** - New vendor registrations, reports, or urgent issues

**Vendor verification** - Vendors notified immediately when approved

Critical for low-bandwidth environments because users don't need to constantly refresh.

## Authentication

Sanctum provides API tokens after login. The frontend includes this token in all authenticated requests.

Google OAuth offers quick sign-in without creating passwords.

Phone verification through Twilio adds security and confirms real users.

## File Storage

Product images stored in storage/app/public. Run `php artisan storage:link` to make them accessible.

Images are optimized for low-bandwidth delivery to work better under Gaza's internet conditions.

## Email and SMS Testing

Mailpit (included as mailpit.exe) lets you test emails locally without sending real ones. Run it and check http://localhost:8025 to see outgoing messages.

Twilio handles SMS notifications for critical updates like price drops on tracked items or new availability of scarce commodities.

## Background Jobs

Email sending and notifications run through Laravel queues to keep the API responsive.

Local development runs synchronously. Production should use:

```bash
php artisan queue:work
```

Set QUEUE_CONNECTION to database or redis in .env for production.

## Testing

Run tests with:

```bash
php artisan test
```

## Common Issues

**Database connection fails** - Verify MySQL is running and .env credentials are correct

**Storage link broken** - Run `php artisan storage:link` again

**Pusher not connecting** - Check credentials and that BROADCAST_DRIVER is set to pusher in .env

**Permission errors** - Ensure storage and bootstrap/cache are writable

**Fresh start needed** - Run `php artisan migrate:fresh --seed` to reset everything

## Code Organization

**app/Http/Controllers** - Handles all API requests organized by feature

**app/Models** - Database models with relationships

**app/Services** - Business logic like price rating calculations and SMS delivery

**app/Events** - Triggered when important things happen

**app/Listeners** - Respond to events by sending notifications or updating data

**app/Notifications** - Email, platform, and SMS notification classes

**app/Mail** - Email templates

**database/migrations** - Database structure definitions

**database/seeders** - Initial data including Gaza cities and essential commodity categories

**routes/api.php** - All API endpoint definitions

**routes/channels.php** - Broadcasting authorization

## Security Considerations

Use HTTPS in production. Never commit .env to version control. Keep dependencies updated. Use strong passwords. Verify vendors carefully before approval. Monitor reports for suspicious activity. Rate limit API calls to prevent abuse.

## Performance for Low-Bandwidth

Enable Redis caching to reduce database queries. Optimize images before storage. Use query result caching for frequently accessed data. Enable Laravel's response caching for static data. Minimize payload sizes in API responses. Implement pagination for large lists.

## Deployment

For production deployment:

Set APP_ENV=production and APP_DEBUG=false. Run `composer install --optimize-autoloader --no-dev`. Cache configurations with `php artisan config:cache` and `php artisan route:cache`. Ensure queue workers run continuously. Set up database backups. Configure web server to serve from public directory. Enable SSL. Monitor logs and performance. Consider CDN for static assets if bandwidth allows.

## Platform Purpose

This platform exists to help people in Gaza find essential commodities during an unprecedented supply crisis. Every feature prioritizes reliability, low bandwidth usage, and connecting people with the goods they desperately need. The exchange system helps when money is scarce. The verification system builds trust when misinformation could be dangerous. The notification system ensures people know immediately when critical items become available.

## Need Help

Check storage/logs/laravel.log for error details. Review Laravel documentation for framework issues. Consult Pusher docs for real-time problems. Check Sanctum docs for authentication questions.
