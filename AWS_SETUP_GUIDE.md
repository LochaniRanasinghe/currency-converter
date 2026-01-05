# AWS S3 & SQS Setup Guide

## Step 1: AWS Account Setup

### Create IAM User

1. Go to AWS Console → IAM → Users
2. Click "Create user"
3. Username: `laravel-currency-app`
4. Enable "Programmatic access"
5. Click "Next: Permissions"

### Attach Policies

Attach these policies:

-   `AmazonS3FullAccess`
-   `AmazonSQSFullAccess`

Or create a custom policy (more secure):

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "s3:PutObject",
                "s3:GetObject",
                "s3:DeleteObject",
                "s3:ListBucket"
            ],
            "Resource": [
                "arn:aws:s3:::your-bucket-name",
                "arn:aws:s3:::your-bucket-name/*"
            ]
        },
        {
            "Effect": "Allow",
            "Action": [
                "sqs:SendMessage",
                "sqs:ReceiveMessage",
                "sqs:DeleteMessage",
                "sqs:GetQueueAttributes",
                "sqs:GetQueueUrl"
            ],
            "Resource": "arn:aws:sqs:*:*:*"
        }
    ]
}
```

### Save Credentials

After creating user, save:

-   Access Key ID
-   Secret Access Key

---

## Step 2: Create S3 Bucket

1. Go to AWS Console → S3
2. Click "Create bucket"
3. Bucket name: `currency-exchange-app-files` (must be globally unique)
4. Region: Choose closest to your users (e.g., `us-east-1`)
5. Uncheck "Block all public access" if you need public files
6. Click "Create bucket"

### Configure CORS (if needed for uploads)

In your bucket → Permissions → CORS:

```json
[
    {
        "AllowedHeaders": ["*"],
        "AllowedMethods": ["GET", "PUT", "POST", "DELETE"],
        "AllowedOrigins": ["*"],
        "ExposeHeaders": []
    }
]
```

---

## Step 3: Create SQS Queue

1. Go to AWS Console → SQS
2. Click "Create queue"
3. Type: **Standard Queue**
4. Name: `currency-exchange-payments`
5. Configuration:
    - Visibility timeout: **120 seconds** (adjust based on job duration)
    - Message retention period: **4 days**
    - Maximum message size: **256 KB**
    - Delivery delay: **0 seconds**
6. Click "Create queue"
7. Copy the Queue URL (e.g., `https://sqs.us-east-1.amazonaws.com/123456789/currency-exchange-payments`)

---

## Step 4: Update Environment Variables

Add to your `.env` file:

```env
# AWS Credentials
AWS_ACCESS_KEY_ID=your-access-key-id
AWS_SECRET_ACCESS_KEY=your-secret-access-key
AWS_DEFAULT_REGION=us-east-1

# S3 Configuration
AWS_BUCKET=currency-exchange-app-files
AWS_URL=https://currency-exchange-app-files.s3.amazonaws.com
FILESYSTEM_DISK=s3

# SQS Configuration
QUEUE_CONNECTION=sqs
SQS_PREFIX=https://sqs.us-east-1.amazonaws.com/your-account-id
SQS_QUEUE=currency-exchange-payments
```

### On AWS EC2/ECS (using IAM Roles - Recommended)

If your app runs on AWS infrastructure, use IAM roles instead:

```env
# Leave these empty, IAM role will handle authentication
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=currency-exchange-app-files
FILESYSTEM_DISK=s3
QUEUE_CONNECTION=sqs
SQS_PREFIX=https://sqs.us-east-1.amazonaws.com/your-account-id
SQS_QUEUE=currency-exchange-payments
```

---

## Step 5: Update GitHub Secrets

Add to GitHub Secrets for deployment:

1. Go to your repo → Settings → Secrets and variables → Actions
2. Add these secrets:
    - `AWS_ACCESS_KEY_ID`
    - `AWS_SECRET_ACCESS_KEY`
    - `AWS_DEFAULT_REGION`
    - `AWS_BUCKET`
    - `SQS_PREFIX`
    - `SQS_QUEUE`

---

## Step 6: Test S3 Integration

```bash
# Test uploading to S3
php artisan tinker
```

In tinker:

```php
Storage::disk('s3')->put('test.txt', 'Hello S3!');
Storage::disk('s3')->exists('test.txt'); // should return true
Storage::disk('s3')->get('test.txt'); // should return "Hello S3!"
Storage::disk('s3')->delete('test.txt');
```

---

## Step 7: Test SQS Integration

```bash
# Dispatch a test job
php artisan tinker
```

In tinker:

```php
dispatch(new \App\Jobs\ProcessPaymentCsv('test.csv'));
```

Then check SQS queue in AWS Console to see the message.

---

## Step 8: Run Queue Worker

### For Development:

```bash
php artisan queue:work sqs --tries=3 --timeout=120
```

### For Production (Supervisor):

Create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/app/artisan queue:work sqs --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/app/storage/logs/worker.log
stopwaitsecs=3600
```

Then:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

---

## Usage Examples

### Upload File to S3

```php
// In your controller
public function uploadFile(Request $request)
{
    $path = $request->file('document')->store('payments', 's3');
    return response()->json(['path' => $path]);
}
```

### Get File URL

```php
$url = Storage::disk('s3')->url('payments/file.pdf');
// Temporary signed URL (expires in 30 minutes)
$temporaryUrl = Storage::disk('s3')->temporaryUrl('payments/file.pdf', now()->addMinutes(30));
```

### Dispatch Job to SQS

```php
// Already using this in your app
ProcessPaymentCsv::dispatch($filename);
```

---

## Costs Estimate

-   **S3**: ~$0.023 per GB/month + $0.005 per 1,000 requests
-   **SQS**: First 1 million requests/month are FREE, then $0.40 per million
-   **Data Transfer**: First 100GB/month is FREE

**Typical monthly cost for small app**: $1-5

---

## Troubleshooting

### "Credentials not found"

-   Check `.env` variables are set correctly
-   Run `php artisan config:clear`

### "Access Denied" on S3

-   Verify IAM user has S3 permissions
-   Check bucket policy

### SQS Jobs Not Processing

-   Verify queue worker is running: `ps aux | grep queue:work`
-   Check SQS queue has messages in AWS Console
-   Check `storage/logs/laravel.log` for errors

### Slow S3 Uploads

-   Choose S3 region close to your server
-   Use S3 Transfer Acceleration for uploads

---

## Security Best Practices

1. ✅ Use IAM roles on EC2/ECS instead of access keys when possible
2. ✅ Never commit `.env` file
3. ✅ Use separate AWS accounts for dev/staging/production
4. ✅ Enable S3 bucket versioning for important files
5. ✅ Set up CloudWatch alarms for unusual activity
6. ✅ Use VPC endpoints for S3/SQS to avoid internet traffic costs
7. ✅ Rotate AWS credentials every 90 days
