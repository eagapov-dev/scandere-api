<?php

namespace App\Console\Commands;

use App\Models\Comment;
use Illuminate\Console\Command;

class PublishAllComments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comments:publish-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish all draft comments (migration helper)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = Comment::where('status', 'draft')->update(['status' => 'published']);

        $this->info("Published {$count} comments.");

        return 0;
    }
}
