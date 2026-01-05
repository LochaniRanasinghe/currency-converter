<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessPaymentCsv;

class TestAwsIntegration extends Command
{
    protected $signature = 'aws:test';
    protected $description = 'Test AWS S3 and SQS integration';

    public function handle()
    {
        $this->info('Testing AWS Integration...');
        $this->newLine();

        // Test S3
        $this->info('🔍 Testing S3 Connection...');
        try {
            $testContent = 'Hello from Laravel at ' . now();
            Storage::disk('s3')->put('test/connection-test.txt', $testContent);

            if (Storage::disk('s3')->exists('test/connection-test.txt')) {
                $this->info('✅ S3 Upload: SUCCESS');

                $retrieved = Storage::disk('s3')->get('test/connection-test.txt');
                if ($retrieved === $testContent) {
                    $this->info('✅ S3 Download: SUCCESS');
                } else {
                    $this->error('❌ S3 Download: FAILED (content mismatch)');
                }

                Storage::disk('s3')->delete('test/connection-test.txt');
                $this->info('✅ S3 Delete: SUCCESS');
            } else {
                $this->error('❌ S3 Upload: FAILED');
            }
        } catch (\Exception $e) {
            $this->error('❌ S3 Test Failed: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Troubleshooting Tips:');
            $this->line('1. Check AWS credentials in .env file');
            $this->line('2. Verify S3 bucket exists and is accessible');
            $this->line('3. Check IAM permissions (s3:PutObject, s3:GetObject, s3:DeleteObject)');
            $this->line('4. Run: php artisan config:clear');
        }

        $this->newLine();

        // Test SQS
        $this->info('🔍 Testing SQS Connection...');
        try {
            // Create a test CSV content
            $testCsv = "customer_id,customer_name,customer_email,amount,currency,reference_no,date_time\n";
            $testCsv .= "TEST001,Test User,test@example.com,100,USD,REF-TEST-001," . now() . "\n";

            // Store test CSV on S3
            $disk = config('filesystems.default');
            Storage::disk($disk)->put('test/test-payment.csv', $testCsv);

            // Dispatch job to SQS
            ProcessPaymentCsv::dispatch('test/test-payment.csv', $disk);

            $this->info('✅ SQS Job Dispatched: SUCCESS');
            $this->newLine();
            $this->warn('⚠️  Note: Check your SQS queue in AWS Console to verify the message arrived');
            $this->line('Queue URL: ' . config('queue.connections.sqs.prefix') . '/' . config('queue.connections.sqs.queue'));
            $this->newLine();
            $this->line('To process the job, run: php artisan queue:work sqs');
        } catch (\Exception $e) {
            $this->error('❌ SQS Test Failed: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Troubleshooting Tips:');
            $this->line('1. Check AWS credentials in .env file');
            $this->line('2. Verify SQS queue exists');
            $this->line('3. Check SQS_PREFIX and SQS_QUEUE in .env');
            $this->line('4. Check IAM permissions (sqs:SendMessage)');
            $this->line('5. Run: php artisan config:clear');
        }

        $this->newLine();
        $this->info('🎉 Test Complete!');

        return 0;
    }
}
