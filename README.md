# E-Commerce Platform

A full-stack e-commerce platform built with Laravel (backend API) and Next.js (frontend).

## Features
## test

### Backend (Laravel)
- **Authentication**: Customer registration and login with role-based access (admin/customer)
- **Product Management**: CRUD operations for products with categories
- **Order Management**: Order creation, tracking, and status updates
- **Customer Management**: Admin panel for managing customers
- **Inventory Tracking**: Stock quantity management
- **API Endpoints**: RESTful API for all operations

### Frontend (Next.js)
- **Product Catalog**: Browse products with categories and search
- **User Authentication**: Login and registration forms
- **Admin Panel**: Product and order management interface
- **Responsive Design**: Mobile-friendly interface with Tailwind CSS
- **Real-time Updates**: Dynamic product listings and order status

## Database Schema

### Tables
- **categories**: Product categories (Electronics, Clothing, Books, etc.)
- **products**: Product information with pricing and inventory
- **customers**: User accounts with role-based access
- **orders**: Order information with customer details
- **order_items**: Individual items within orders

## Installation & Setup

### Prerequisites
- PHP 8.4+ with PostgreSQL extension
- PostgreSQL database
- Node.js 18+
- Composer

### Backend Setup (Laravel)
1. Navigate to the project directory:
   ```bash
   cd ecommerce-platform
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Configure environment:
   ```bash
   cp .env.example .env
   ```
   Update database credentials in `.env`:
   ```
   DB_CONNECTION=pgsql
   DB_HOST=localhost
   DB_PORT=5432
   DB_DATABASE=ecom_db
   DB_USERNAME=postgres
   DB_PASSWORD=postgres
   ```

4. Generate application key:
   ```bash
   php artisan key:generate
   ```

5. Run migrations and seed data:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. Start Laravel server:
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

### Frontend Setup (Next.js)
1. Navigate to client directory:
   ```bash
   cd client
   ```

2. Install dependencies:
   ```bash
   npm install
   ```

3. Configure environment:
   ```bash
   echo "NEXT_PUBLIC_API_URL=http://localhost:8000/api" > .env.local
   ```

4. Start development server:
   ```bash
   npm run dev
   ```

## Default Accounts

### Admin Account
- **Email**: admin@example.com
- **Password**: password
- **Role**: admin

### Customer Accounts
- **Email**: john@example.com / jane@example.com
- **Password**: password
- **Role**: customer

## API Endpoints

### Public Endpoints
- `GET /api/products` - List all products
- `GET /api/products/{id}` - Get product details
- `POST /api/register` - Register new customer
- `POST /api/login` - Customer login

### Protected Endpoints (Customer)
- `POST /api/orders` - Create new order
- `GET /api/orders/{id}` - Get order details

### Admin Only Endpoints
- `GET /api/customers` - List all customers
- `POST /api/products` - Create new product
- `PUT /api/products/{id}` - Update product
- `DELETE /api/products/{id}` - Delete product
- `GET /api/orders` - List all orders
- `PUT /api/orders/{id}` - Update order status

## Usage

### Customer Flow
1. Browse products on the homepage
2. Register or login to place orders
3. Add products to cart and checkout
4. View order history and status

### Admin Flow
1. Login with admin credentials
2. Access admin panel at `/admin`
3. Manage products (create, edit, delete)
4. View and update order statuses
5. Manage customer accounts

## Technology Stack

### Backend
- **Laravel 12**: PHP framework
- **PostgreSQL**: Database
- **Laravel Sanctum**: API authentication
- **Eloquent ORM**: Database interactions

### Frontend
- **Next.js 16**: React framework
- **TypeScript**: Type safety
- **Tailwind CSS**: Styling
- **React Hooks**: State management

## Development

### Running Tests
```bash
# Laravel tests
php artisan test

# Next.js tests
cd client && npm test
```

### Database Management
```bash
# Reset and reseed database
php artisan migrate:fresh --seed

# Create new migration
php artisan make:migration create_table_name

# Create new seeder
php artisan make:seeder TableSeeder
```

## Production Deployment

### Backend
1. Set up production environment variables
2. Run `composer install --optimize-autoloader --no-dev`
3. Run `php artisan config:cache`
4. Run `php artisan route:cache`
5. Set up web server (Apache/Nginx)

### Frontend
1. Build production assets: `npm run build`
2. Deploy to hosting platform (Vercel, Netlify, etc.)
3. Update API URL in environment variables

## Contributing

1. Fork the repository
2. Create feature branch: `git checkout -b feature-name`
3. Commit changes: `git commit -am 'Add feature'`
4. Push to branch: `git push origin feature-name`
5. Submit pull request

## License

This project is open-source and available under the MIT License.
