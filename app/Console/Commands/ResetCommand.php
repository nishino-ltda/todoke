<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the application by wiping the database, running migrations and seeding';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Resetting application...');
        
        $this->call('db:wipe');
        $this->info('Database wiped successfully');
        
        $this->call('migrate');
        $this->info('Migrations run successfully');
        
        $this->call('db:seed');
        $this->info('Database seeded successfully');
        
        $this->info('Application reset completed!');
        
        return 0;
    }
}
