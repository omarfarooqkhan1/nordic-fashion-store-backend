# Nordic Fashion Store - Backend

Laravel API backend for Nordic Fashion Store e-commerce platform.

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

## �️ Tech Stack

- **Laravel 11** with PHP 8.2+
- **MySQL** database
- **Laravel Sanctum** for authentication
- **Laravel Mail** for email notifications
- **Cloudinary** for image storage

## 🧩 Key Features

- **Product Management** with variants and images
- **Order Management** with tracking support
- **Dual Authentication** (Auth0 + traditional)
- **Guest Checkout** functionality
- **Admin Dashboard** API endpoints
- **Email Notifications** with professional templates
- **Bulk Operations** for product management

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
MAIL_FROM_ADDRESS="noreply@nordicskin.com"
MAIL_FROM_NAME="Nordic Fashion Store"
```

### Optional: Cloudinary
```env
CLOUDINARY_URL=cloudinary://key:secret@cloud_name
```

## � Development Commands

```bash
php artisan migrate       # Run database migrations
php artisan db:seed       # Seed sample data
php artisan cache:clear   # Clear application cache
php artisan test          # Run tests
```

## � API Structure

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

*(As you develop your API, list key endpoints here with their methods and brief descriptions.)*

* `GET /api/products` - Retrieve a list of all products.
* `POST /api/register` - User registration.
* `POST /api/login` - User login.

## 🔒 Authentication

This project intends to integrate **Auth0** for secure API authentication and authorization using JWTs. The integration is currently in progress. Once complete, protected routes will require a valid JWT Access Token in the `Authorization: Bearer <TOKEN>` header.

## 🤝 Contributing

Contributions are welcome! Please open an issue or submit a pull request.

## 📄 License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).