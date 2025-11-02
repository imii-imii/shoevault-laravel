<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessNotificationChecks;

class ScheduleNotificationChecks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for new notifications (low stock, new reservations, etc.)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching notification checks job...');
        
        // Dispatch the job
        ProcessNotificationChecks::dispatch();
        
        $this->info('Notification checks job has been queued.');
        
        return Command::SUCCESS;
    }
}
