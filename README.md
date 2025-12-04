# Nord Flex - Backend

Laravel API backend for Nord Flex e-commerce platform.

## 🚀 Quick Start

```bash
# Install dependencies
composer install

# Configure environment
cp .env.example .env
# Edit .env with your database credentials

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Start development server
php artisan serve
# API runs on http://localhost:8000
```

## 🛠️ Tech Stack

- **Laravel 11** with PHP 8.2+
- **MySQL** database
- **Laravel Sanctum** for authentication
- **Laravel Mail** for email notifications
- **Local Image Storage** (replaced Cloudinary for cost savings)
- **Stripe** for payment processing

## 🧩 Key Features

- **Product Management** with variants and images
- **Order Management** with tracking support
- **Dual Authentication** (Auth0 + traditional)
- **Guest Checkout** functionality
- **Admin Dashboard** API endpoints
- **Email Notifications** with professional templates
- **Bulk Operations** for product management
- **Product Review System** with media support
- **Stripe Payment Integration** with webhook handling
- **Local Image Storage** with automatic optimization

## 🔧 Environment Configuration

### Database (.env)
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nordic_fashion
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Mail Configuration
```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@nordflex.shop"
MAIL_FROM_NAME="Nord Flex"
```

### Stripe Configuration
```env
STRIPE_SECRET_KEY=sk_test_your_stripe_secret_key_here
STRIPE_PUBLISHABLE_KEY=pk_test_your_stripe_publishable_key_here
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here
```

### Local Image Storage
```env
FILESYSTEM_DISK=public
IMAGE_DRIVER=gd
IMAGE_QUALITY=85
```

## 🏗️ Development Commands

```bash
php artisan migrate       # Run database migrations
php artisan db:seed       # Seed sample data
php artisan cache:clear   # Clear application cache
php artisan test          # Run tests
```

## 📁 API Structure

```
app/
├── Http/Controllers/Api/ # API controllers
├── Models/              # Eloquent models
├── Mail/               # Email templates
└── Providers/          # Service providers

routes/api.php          # API route definitions
database/migrations/    # Database schema
```

For full project documentation, see the main README.md in the parent directory.

## 📦 Getting Started

1.  **Clone the repository:**

2.  **Install PHP dependencies:**
    ```bash
    composer install
    ```

3.  **Copy the environment file:**
    ```bash
    cp .env.example .env
    ```

4.  **Generate an application key:**
    ```bash
    php artisan key:generate
    ```

5.  **Configure your `.env` file:**
    Open the `.env` file and update your database credentials (`DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) and any other necessary environment variables.

    *Example database configuration (for MySQL):*
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=ecommerce_db
    DB_USERNAME=root
    DB_PASSWORD=
    ```

6.  **Run database migrations:**
    ```bash
    php artisan migrate
    ```

7.  **Start the Laravel development server:**
    ```bash
    php artisan serve
    ```
    The API will be available at `http://localhost:8000`.

## ⚙️ API Endpoints (Examples)

* `GET /api/products` - Retrieve a list of all products.
* `POST /api/register` - User registration.
* `POST /api/login` - User login.
* `POST /api/stripe/create-payment-intent` - Create payment intent.
* `POST /api/products/{id}/reviews` - Create product review.

## 🔒 Authentication

This project integrates **Auth0** for secure API authentication and authorization using JWTs. Protected routes require a valid JWT Access Token in the `Authorization: Bearer <TOKEN>` header.

## 💡 Current Implementation Status

This version of the backend is fully implemented with:
- Complete product management system with variants and images
- Order management with tracking support
- Dual authentication system (Auth0 + traditional)
- Guest checkout functionality
- Admin dashboard with full CRUD operations
- Email notifications for various events
- Bulk operations for product management
- Product review system with media support
- Stripe payment integration with webhook handling
- Local image storage with automatic optimization (replaced Cloudinary)

## 🛣️ Future Enhancements

* **Advanced Analytics:** Implementation of sales and user behavior tracking
* **Inventory Management:** Advanced stock level tracking and alerts
* **Mobile App API:** Dedicated endpoints for mobile application

## 🤝 Contributing

Contributions are welcome! Please open an issue or submit a pull request.

## 📄 License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).