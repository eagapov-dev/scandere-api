# Scandere AI Store - API

Laravel 11 API for e-commerce store with Stripe integration and Sanctum authentication.

## 🚀 Quick Start (Production)

### 1️⃣ Upload script to server
```bash
scp deploy-setup.sh root@your-server-ip:/root/
```

### 2️⃣ Run installation
```bash
ssh root@your-server-ip
chmod +x /root/deploy-setup.sh
/root/deploy-setup.sh
```

### 3️⃣ Upload project
```bash
rsync -avz --exclude='vendor' --exclude='node_modules' --exclude='.git' \
  ./ root@your-server-ip:/var/www/scandere-api/
```

### 4️⃣ Configure .env
```bash
ssh root@your-server-ip
cd /var/www/scandere-api
cp .env.production .env
nano .env  # Add DB password and Stripe keys
```

### 5️⃣ Deploy application
```bash
chmod +x deploy-app.sh
./deploy-app.sh
```

### 6️⃣ Setup SSL
```bash
certbot --nginx -d api.scandereai.store
```

✅ **Done!** API available at `https://api.scandereai.store`

📖 Detailed guide: [DEPLOYMENT.md](DEPLOYMENT.md)

## 🛠️ Local Development

### Requirements
- PHP 8.2+
- Composer
- MySQL/MariaDB

### Installation
```bash
# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure DB in .env, then:
php artisan migrate

# Run server
php artisan serve
```

API will be available at `http://localhost:8000`

## 🧪 Testing

### Run Tests
```bash
# All tests
php artisan test

# Feature tests only
php artisan test --testsuite=Feature

# Unit tests only
php artisan test --testsuite=Unit

# With coverage
php artisan test --coverage
```

### Test Coverage
- ✅ Authentication (register, login, logout)
- ✅ Products (CRUD, filtering, featured)
- ✅ Shopping Cart (add, remove, clear)
- ✅ Orders (checkout, admin management)
- ✅ Comments (create, approve, delete)
- ✅ Payment Service (calculations, validation)

See [TESTING.md](TESTING.md) for detailed testing guide.

## 📦 Project Structure

```
scandere-api/
├── app/
│   ├── Http/Controllers/     # API controllers
│   ├── Models/               # Data models
│   └── Services/             # Business logic
├── database/
│   ├── migrations/           # Database migrations
│   └── seeders/              # Seeders
├── routes/
│   ├── api.php              # API routes
│   └── web.php              # Web routes
└── config/                  # Configuration
```

## 🔌 API Endpoints

### Public
- `GET /api/products` - Product list
- `GET /api/products/{slug}` - Product details
- `GET /api/categories` - Categories
- `GET /api/featured` - Featured products

### Authentication
- `POST /api/auth/register` - Register
- `POST /api/auth/login` - Login
- `POST /api/auth/logout` - Logout
- `GET /api/auth/user` - Current user

### Cart
- `GET /api/cart` - View cart
- `POST /api/cart/add` - Add to cart
- `DELETE /api/cart/{product}` - Remove from cart

### Orders
- `POST /api/checkout` - Checkout
- `POST /api/webhook/stripe` - Stripe webhook

### Admin (requires authentication)
- `GET /api/admin/products` - Manage products
- `GET /api/admin/orders` - Manage orders
- `GET /api/admin/stats` - Statistics

Full list: `php artisan route:list`

## 🔐 Authentication

API uses Laravel Sanctum for authentication.

```javascript
// Login
const response = await fetch('https://api.scandereai.store/api/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email, password })
});

const { token } = await response.json();

// Using token
fetch('https://api.scandereai.store/api/cart', {
  headers: { 'Authorization': `Bearer ${token}` }
});
```

## 💳 Stripe Integration

### Setup
1. Get keys from [Stripe Dashboard](https://dashboard.stripe.com/apikeys)
2. Add to `.env`:
   ```
   STRIPE_KEY=pk_live_xxx
   STRIPE_SECRET=sk_live_xxx
   STRIPE_WEBHOOK_SECRET=whsec_xxx
   ```

### Webhook
Configure in Stripe:
- URL: `https://api.scandereai.store/api/webhook/stripe`
- Events: `checkout.session.completed`, `payment_intent.succeeded`

## 📊 Database

### Tables
- `users` - Users
- `products` - Products
- `categories` - Categories
- `orders` - Orders
- `cart` - Shopping cart
- `comments` - Comments
- `subscribers` - Newsletter subscribers
- `bundles` - Product bundles

### Migrations
```bash
# Run migrations
php artisan migrate

# Rollback
php artisan migrate:rollback

# Fresh database
php artisan migrate:fresh
```

## 🔄 Server Updates

```bash
# 1. Upload new files
rsync -avz --exclude='vendor' ./ root@your-server-ip:/var/www/scandere-api/

# 2. Run update
ssh root@your-server-ip
cd /var/www/scandere-api
./update.sh
```

## 🐛 Debugging

### Logs
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/error.log

# PHP-FPM logs
tail -f /var/log/php8.3-fpm.log
```

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Check Database Connection
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

## 📦 Deployment Scripts

- `deploy-setup.sh` - Initial server setup (Nginx, PHP, MariaDB)
- `deploy-app.sh` - Laravel application deployment
- `update.sh` - Quick code update

## 🔒 Security

- ✅ HTTPS with Let's Encrypt
- ✅ CORS configured for frontend
- ✅ Sanctum tokens for API
- ✅ SQL injection protection (Eloquent ORM)
- ✅ XSS protection (Laravel Blade/API responses)
- ✅ CSRF protection for web routes

## 📝 Tech Stack

- **Framework:** Laravel 11
- **PHP:** 8.3
- **Database:** MariaDB
- **Web Server:** Nginx
- **Auth:** Laravel Sanctum
- **Payments:** Stripe PHP SDK
- **Code Style:** Laravel Pint

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## 📄 License

Proprietary - Scandere AI Store

## 🆘 Support

When issues occur:
1. Check logs (see Debugging section)
2. Ensure all services are running: `systemctl status nginx php8.3-fpm mariadb`
3. Verify `.env` settings
4. Clear Laravel cache

---

**Scandere AI Store** © 2024
