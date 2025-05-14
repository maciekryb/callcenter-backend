<?php

namespace App\Console\Commands;

use App\Models\WorkSchedule;
use App\Services\WorkScheduleService;
use Illuminate\Console\Command;

class createWorkSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-work-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating work schedule...');

        // Call the WorkScheduleService to create the work schedule
        $date = now()->startOfWeek();

        $this->info('Creating work schedule for date: ' . $date);
        WorkScheduleService::createWorkScheduleForWeek($date);

        $this->info('Work schedule created successfully.');
    }
}
