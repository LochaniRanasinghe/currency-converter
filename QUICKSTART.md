# 🚀 Quick Start: S3 & SQS Integration

## ✅ What's Been Done

1. ✅ Installed AWS SDK and Flysystem S3 packages
2. ✅ Updated `PaymentController` to store files on S3
3. ✅ Updated `ProcessPaymentCsv` job to read from S3
4. ✅ Updated `.env.example` with AWS configuration
5. ✅ Created test command to verify integration
6. ✅ Created comprehensive setup guide

---

## 📋 Next Steps (Do These in Order)

### 1. Create AWS Resources (10 minutes)

#### A. Create IAM User

```bash
AWS Console → IAM → Users → Create User
- Name: laravel-currency-app
- Access type: Programmatic access
- Attach policies: AmazonS3FullAccess, AmazonSQSFullAccess
- Save Access Key ID and Secret Access Key
```

#### B. Create S3 Bucket

```bash
AWS Console → S3 → Create Bucket
- Name: currency-exchange-app-files (or your unique name)
- Region: us-east-1 (or closest to your server)
- Uncheck "Block all public access" if you need public files
```

#### C. Create SQS Queue

```bash
AWS Console → SQS → Create Queue
- Type: Standard Queue
- Name: currency-exchange-payments
- Visibility timeout: 120 seconds
- Copy the Queue URL
```

---

### 2. Update Local Environment

Update your `.env` file:

```env
# Filesystem Configuration
FILESYSTEM_DISK=s3

# Queue Configuration
QUEUE_CONNECTION=sqs

# AWS Credentials
AWS_ACCESS_KEY_ID=your-actual-access-key-id
AWS_SECRET_ACCESS_KEY=your-actual-secret-key
AWS_DEFAULT_REGION=us-east-1

# S3 Configuration
AWS_BUCKET=currency-exchange-app-files
AWS_URL=https://currency-exchange-app-files.s3.amazonaws.com

# SQS Configuration
SQS_PREFIX=https://sqs.us-east-1.amazonaws.com/123456789
SQS_QUEUE=currency-exchange-payments
```

**Important:** Replace:

-   `your-actual-access-key-id`
-   `your-actual-secret-key`
-   `currency-exchange-app-files` (your actual bucket name)
-   `123456789` (your AWS Account ID - find in AWS Console)

Then clear cache:

```bash
php artisan config:clear
```

---

### 3. Test Integration Locally

```bash
# Test S3 and SQS connection
php artisan aws:test
```

This will:

-   ✅ Upload/download/delete a test file on S3
-   ✅ Dispatch a test job to SQS
-   ✅ Show troubleshooting tips if something fails

---

### 4. Test Full Upload Flow

```bash
# Start queue worker
php artisan queue:work sqs --tries=3 --timeout=120
```

In another terminal, test the API:

```bash
curl -X POST http://localhost:8000/api/payments/upload \
  -F "file=@sample-payments.csv"
```

You should see:

1. File uploaded to S3 ✅
2. Job dispatched to SQS ✅
3. Queue worker processing the job ✅
4. Payments saved to database ✅

---

### 5. Update GitHub Secrets for Production

Go to: `GitHub Repo → Settings → Secrets and Variables → Actions`

Add these secrets:

-   `AWS_ACCESS_KEY_ID`
-   `AWS_SECRET_ACCESS_KEY`
-   `AWS_DEFAULT_REGION`
-   `AWS_BUCKET`
-   `AWS_URL`
-   `SQS_PREFIX`
-   `SQS_QUEUE`

Then update your `ENV_FILE` secret to include these variables.

---

### 6. Deploy to AWS

Push to main branch:

```bash
git add .
git commit -m "Add AWS S3 and SQS integration"
git push origin main
```

GitHub Actions will automatically deploy.

---

### 7. Setup Queue Worker on EC2 (Production)

SSH into your EC2 instance:

```bash
# Install Supervisor
sudo apt-get update
sudo apt-get install supervisor

# Create worker configuration
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

Paste this configuration:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=docker-compose exec -T app php artisan queue:work sqs --sleep=3 --tries=3 --max-time=3600
directory=/home/ubuntu/currency-exchange-app-main
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=ubuntu
numprocs=2
redirect_stderr=true
stdout_logfile=/home/ubuntu/currency-exchange-app-main/storage/logs/worker.log
stopwaitsecs=3600
```

Start the worker:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*

# Check status
sudo supervisorctl status
```

---

## 🧪 Verification Checklist

-   [ ] AWS IAM user created with S3 & SQS permissions
-   [ ] S3 bucket created
-   [ ] SQS queue created
-   [ ] Local `.env` updated with AWS credentials
-   [ ] `php artisan aws:test` runs successfully
-   [ ] Queue worker processes jobs from SQS
-   [ ] Files upload to S3 bucket
-   [ ] GitHub secrets configured
-   [ ] Deployed to production
-   [ ] Supervisor queue worker running on EC2

---

## 📊 Monitoring

### Check S3 Files

```bash
# List files in S3 bucket
php artisan tinker
Storage::disk('s3')->files('uploads/payments');
```

### Check SQS Queue

```bash
AWS Console → SQS → Select Queue
- Check "Messages Available"
- Check "Messages In Flight"
```

### Check Queue Worker Logs

```bash
# On EC2
sudo supervisorctl tail -f laravel-worker:laravel-worker_00

# Or check Laravel logs
tail -f storage/logs/laravel.log
```

---

## 💰 Cost Estimate

**Monthly costs for typical usage:**

-   S3: $0.50 - $2 (storage + requests)
-   SQS: FREE (first 1M requests/month)
-   **Total: < $5/month**

---

## 🆘 Troubleshooting

### "Credentials not found"

```bash
php artisan config:clear
php artisan cache:clear
# Check .env file has AWS credentials
```

### "Access Denied" (S3)

-   Verify IAM user has S3 permissions
-   Check bucket name in .env matches actual bucket
-   Verify region is correct

### Jobs not processing (SQS)

```bash
# Check queue worker is running
ps aux | grep queue:work

# Restart queue worker
php artisan queue:restart
php artisan queue:work sqs
```

### Files not uploading to S3

```bash
# Test connection
php artisan aws:test

# Check disk configuration
php artisan tinker
config('filesystems.default'); // should be 's3'
```

---

## 📚 Additional Resources

-   [Laravel Filesystem Documentation](https://laravel.com/docs/12.x/filesystem)
-   [Laravel Queues Documentation](https://laravel.com/docs/12.x/queues)
-   [AWS S3 Documentation](https://docs.aws.amazon.com/s3/)
-   [AWS SQS Documentation](https://docs.aws.amazon.com/sqs/)

---

## 🎯 What Changed in Your Code

1. **PaymentController.php**

    - Now stores files on S3 (or configured disk)
    - Passes storage disk to job

2. **ProcessPaymentCsv.php**

    - Reads files from S3 (or configured disk)
    - Handles file cleanup from storage disk

3. **composer.json**

    - Added `aws/aws-sdk-php`
    - Added `league/flysystem-aws-s3-v3`

4. **.env.example**
    - Added AWS credentials configuration
    - Added S3 configuration
    - Added SQS configuration

---

Need help? Check `AWS_SETUP_GUIDE.md` for detailed step-by-step instructions!
