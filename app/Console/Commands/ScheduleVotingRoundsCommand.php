<?php

namespace App\Console\Commands;

use App\Services\VotingRoundService;
use Illuminate\Console\Command;

class ScheduleVotingRoundsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voting:schedule-rounds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule monthly voting rounds for all regions';

    /**
     * The voting round service.
     *
     * @var VotingRoundService
     */
    protected $votingRoundService;

    /**
     * Create a new command instance.
     *
     * @param VotingRoundService $votingRoundService
     * @return void
     */
    public function __construct(VotingRoundService $votingRoundService)
    {
        parent::__construct();
        $this->votingRoundService = $votingRoundService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Scheduling monthly voting rounds...');
        
        try {
            $results = $this->votingRoundService->scheduleMonthlyVotingRounds();
            
            $successCount = 0;
            $failureCount = 0;
            
            foreach ($results as $regionId => $result) {
                if ($result['success']) {
                    $successCount++;
                    $this->info("Region {$regionId}: Voting round {$result['voting_round_id']} created successfully.");
                } else {
                    $failureCount++;
                    $this->error("Region {$regionId}: {$result['error']}");
                }
            }
            
            $this->info("Completed: {$successCount} voting rounds created, {$failureCount} failures.");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to schedule voting rounds: {$e->getMessage()}");
            
            return Command::FAILURE;
        }
    }
}
