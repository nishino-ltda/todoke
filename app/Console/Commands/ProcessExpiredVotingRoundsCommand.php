<?php

namespace App\Console\Commands;

use App\Services\VotingRoundService;
use App\Services\VotingCalculationService;
use App\Services\FareUpdateService;
use Illuminate\Console\Command;

class ProcessExpiredVotingRoundsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voting:process-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process expired voting rounds, calculate results, and update pricing';

    /**
     * The voting round service.
     *
     * @var VotingRoundService
     */
    protected $votingRoundService;

    /**
     * The voting calculation service.
     *
     * @var VotingCalculationService
     */
    protected $calculationService;

    /**
     * The fare update service.
     *
     * @var FareUpdateService
     */
    protected $fareUpdateService;

    /**
     * Create a new command instance.
     *
     * @param VotingRoundService $votingRoundService
     * @param VotingCalculationService $calculationService
     * @param FareUpdateService $fareUpdateService
     * @return void
     */
    public function __construct(
        VotingRoundService $votingRoundService,
        VotingCalculationService $calculationService,
        FareUpdateService $fareUpdateService
    ) {
        parent::__construct();
        $this->votingRoundService = $votingRoundService;
        $this->calculationService = $calculationService;
        $this->fareUpdateService = $fareUpdateService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Processing expired voting rounds...');
        
        try {
            $results = $this->votingRoundService->processExpiredVotingRounds(
                $this->calculationService,
                $this->fareUpdateService
            );
            
            if (empty($results)) {
                $this->info('No expired voting rounds found.');
                return Command::SUCCESS;
            }
            
            $successCount = 0;
            $failureCount = 0;
            
            foreach ($results as $roundId => $result) {
                if ($result['success']) {
                    $successCount++;
                    $winningOption = $result['winning_option'];
                    $this->info("Round {$roundId}: Processed successfully.");
                    $this->info("  Winning option: Min: {$winningOption['min_fare_per_km']}, " .
                                "Avg: {$winningOption['avg_fare_per_km']}, " .
                                "Max: {$winningOption['max_fare_per_km']}");
                } else {
                    $failureCount++;
                    $this->error("Round {$roundId}: {$result['error']}");
                }
            }
            
            $this->info("Completed: {$successCount} rounds processed, {$failureCount} failures.");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to process expired voting rounds: {$e->getMessage()}");
            
            return Command::FAILURE;
        }
    }
}
