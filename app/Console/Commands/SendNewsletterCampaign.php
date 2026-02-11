<?php

namespace App\Console\Commands;

use App\Mail\NewsletterCampaign;
use App\Models\Subscriber;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendNewsletterCampaign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newsletter:send
                            {subject : The email subject line}
                            {--message= : The email message content}
                            {--file= : Path to file containing the message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a newsletter campaign to all active subscribers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $subject = $this->argument('subject');

        // Get message content from file or option
        $content = null;
        if ($this->option('file')) {
            $filePath = $this->option('file');
            if (!file_exists($filePath)) {
                $this->error("File not found: {$filePath}");
                return 1;
            }
            $content = file_get_contents($filePath);
        } elseif ($this->option('message')) {
            $content = $this->option('message');
        } else {
            $this->error('Please provide either --message or --file option');
            return 1;
        }

        if (empty($content)) {
            $this->error('Message content cannot be empty');
            return 1;
        }

        // Get all active subscribers
        $subscribers = Subscriber::whereNull('unsubscribed_at')->get();

        if ($subscribers->isEmpty()) {
            $this->warn('No active subscribers found');
            return 0;
        }

        $this->info("Found {$subscribers->count()} active subscribers");

        if (!$this->confirm('Do you want to send the newsletter campaign?', true)) {
            $this->info('Campaign cancelled');
            return 0;
        }

        $this->info('Queueing emails...');
        $progressBar = $this->output->createProgressBar($subscribers->count());
        $progressBar->start();

        // Queue emails to all subscribers
        foreach ($subscribers as $subscriber) {
            Mail::to($subscriber->email)
                ->queue(new NewsletterCampaign(
                    $subject,
                    $content,
                    $subscriber->email
                ));
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("✓ Newsletter campaign queued for {$subscribers->count()} subscribers");
        $this->info('Emails will be sent by the queue worker');

        return 0;
    }
}
