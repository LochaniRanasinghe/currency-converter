<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Currency Exchange App

A Laravel application for processing payment CSV files with automatic currency conversion to USD. Features AWS S3 for file storage and AWS SQS for background job processing.

## 🚀 Features

-   📤 **CSV Upload API**: Upload payment CSV files via REST API
-   💱 **Currency Conversion**: Automatic conversion to USD using live exchange rates
-   ☁️ **AWS S3 Storage**: Secure file storage on Amazon S3
-   🔄 **AWS SQS Queues**: Background job processing with Amazon SQS
-   🐳 **Docker Support**: Full Docker containerization with docker-compose
-   🚀 **CI/CD Pipeline**: Automated deployment to AWS EC2 via GitHub Actions

---

## 📋 Requirements

-   PHP 8.2+
-   Composer
-   MySQL 8.0+
-   Docker & Docker Compose (for containerized setup)
-   AWS Account (for S3 and SQS)

---

## 🏃 Quick Start

### 1. Clone the Repository

```bash
git clone https://github.com/AbishekPerera/currency-exchange-app-main.git
cd currency-exchange-app-main
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database and AWS credentials (see `QUICKSTART.md` for AWS setup).

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Start Application

```bash
# Option A: Using Docker
docker-compose up -d

# Option B: Using PHP Artisan
php artisan serve
```

### 6. Start Queue Worker

```bash
php artisan queue:work sqs --tries=3 --timeout=120
```

---

## 📚 Documentation

-   **[QUICKSTART.md](QUICKSTART.md)** - Quick setup guide for S3 & SQS
-   **[AWS_SETUP_GUIDE.md](AWS_SETUP_GUIDE.md)** - Detailed AWS configuration guide

---

## 🧪 Testing

### Test AWS Integration

```bash
php artisan aws:test
```

### Upload Test CSV

```bash
curl -X POST http://localhost:8000/api/payments/upload \
  -F "file=@sample-payments.csv"
```

---

## 🏗️ Architecture

```
User → API Endpoint → S3 Upload → SQS Queue → Worker → Database
                                              ↓
                                    Currency API (Exchange Rates)
```

---

## 🔑 API Endpoints

### Upload Payment CSV

```http
POST /api/payments/upload
Content-Type: multipart/form-data

Body:
- file: CSV file with columns:
  - customer_id
  - customer_name
  - customer_email
  - amount
  - currency
  - reference_no
  - date_time
```

**Response:**

```json
{
    "status": "success",
    "message": "File is being processed in the background."
}
```

---

## 🛠️ Tech Stack

-   **Framework**: Laravel 12
-   **Database**: MySQL 8.0
-   **Queue**: AWS SQS
-   **Storage**: AWS S3
-   **Container**: Docker
-   **CI/CD**: GitHub Actions
-   **Web Server**: Nginx

---

## 📦 AWS Services Used

-   **S3**: File storage for uploaded CSV files
-   **SQS**: Message queue for background job processing
-   **EC2**: Application hosting
-   **IAM**: Access management

---

## 🔧 Configuration

### Environment Variables

```env
# AWS
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name

# SQS
QUEUE_CONNECTION=sqs
SQS_PREFIX=https://sqs.us-east-1.amazonaws.com/account-id
SQS_QUEUE=queue-name

# Storage
FILESYSTEM_DISK=s3
```

---

## 🚀 Deployment

Push to main branch to trigger automatic deployment:

```bash
git push origin main
```

GitHub Actions will:

1. ✅ Connect to EC2 via SSH
2. ✅ Pull latest code
3. ✅ Build Docker containers
4. ✅ Run migrations
5. ✅ Cache configuration
6. ✅ Restart queue workers

---

## 📊 Monitoring

### Check Queue Status

```bash
# On EC2
sudo supervisorctl status laravel-worker
```

### View Logs

```bash
# Application logs
tail -f storage/logs/laravel.log

# Worker logs
tail -f storage/logs/worker.log
```

---

## 💰 Cost Estimate

**Monthly AWS costs for typical usage:**

-   S3: $0.50 - $2
-   SQS: FREE (first 1M requests/month)
-   **Total: < $5/month**

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

-   [Simple, fast routing engine](https://laravel.com/docs/routing).
-   [Powerful dependency injection container](https://laravel.com/docs/container).
-   Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-   Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-   Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-   [Robust background job processing](https://laravel.com/docs/queues).
-   [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

-   **[Vehikl](https://vehikl.com)**
-   **[Tighten Co.](https://tighten.co)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel)**
-   **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-   **[Redberry](https://redberry.international/laravel-development)**
-   **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
