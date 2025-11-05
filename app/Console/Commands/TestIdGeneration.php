<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Reservation;
use App\Models\Transaction;

class TestIdGeneration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:id-generation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the ID generation system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing ID generation formats...');
        
        // Show current formats
        $lastUser = User::latest()->first();
        $lastReservation = Reservation::latest()->first();
        $lastTransaction = Transaction::latest()->first();
        
        $this->info('Current ID formats:');
        $this->info('User: ' . ($lastUser ? $lastUser->user_id : 'None found'));
        $this->info('Reservation: ' . ($lastReservation ? $lastReservation->reservation_id : 'None found'));
        $this->info('Transaction: ' . ($lastTransaction ? $lastTransaction->transaction_id : 'None found'));
        
        $this->info('');
        $this->info('ID generation formats:');
        $this->info('Users: USR + random (incremental)');
        $this->info('Reservations: RSV-YYYYMMDD-#### (date-based, matches transactions)');
        $this->info('Transactions: TXN-YYYYMMDD-#### (date-based)');
        
        $this->info('');
        $this->info('Example reservation ID for today: ' . \App\Models\Reservation::generateReservationId());
        
        return 0;
    }
}
