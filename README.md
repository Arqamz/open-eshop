# QuickShop

This is the backend for an eCommerce platform built with Laravel. The backend is fully API-driven, designed to handle authentication, product management, categories, sliders, and more, for use with any frontend (not included in this repository).

## Features

### Authentication

- **Customer Registration & Login**: Customers can create accounts, log in, and manage their sessions.
- **Admin Authentication**: Admins can register, log in, and manage administrative tasks securely via JWT tokens.
- **Password Reset**: Full support for customer and admin password reset functionality.

### Category Management

- Admins can manage product categories, including creating, editing, deleting, and enabling/disabling categories.
- Subcategory management is also supported.

### Slider Management

- Admins can upload, replace, and delete slider images used for promotional content on the platform.

### Admin Panel

- The backend includes full administrative control over categories, sliders, inventory, customers, and other store elements.

## Project Setup

### Prerequisites

Ensure you have the following installed:

- PHP 8.x or higher
- Composer 2.x or higher
- Laravel 11.x or higher

### Running Locally

1. **Clone the Repository**

   Start by cloning the repository:

   ```bash
   git clone https://github.com/yourusername/ecommerce-backend.git
   cd ecommerce-backend
   ```

2. **Install Dependencies**

   Run Composer to install all necessary dependencies:

   ```bash
   composer install
   ```

3. **Set Up Environment Variables**

   Copy the `.env.example` file to `.env`:

   ```bash
   cp .env.example .env
   ```

   Update the `.env` file with the correct database and other application configurations.

4. **Generate Application Key**

   Generate the application key:

   ```bash
   php artisan key:generate
   ```

5. **Run Migrations**

   Run the database migrations to set up the required tables:

   ```bash
   php artisan migrate
   ```

6. **Start the Development Server**

   Start the Laravel development server:

   ```bash
   php artisan serve
   ```

   The application should be accessible at `http://127.0.0.1:8000` by default.

7. **API Interaction**

   Since this project is purely API-driven, you can interact with the backend using API testing tools such as Postman, Insomnia or your terminal.

   **Coming Soon**: For those interested in exploring the full API capabilities, an OpenAPI specification or `.http` example file will be provided shortly to show how to make all necessary API calls.

---

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
