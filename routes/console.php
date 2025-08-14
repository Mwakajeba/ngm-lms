<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\CollectMatureInterestJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('loans:collect-mature-interest', function () {
    $this->info('Starting mature interest collection...');

    try {
        CollectMatureInterestJob::dispatch();
        $this->info('Mature interest collection job has been dispatched successfully.');
        $this->info('Check the logs for detailed information about the process.');
    } catch (\Exception $e) {
        $this->error('Error dispatching mature interest collection job: ' . $e->getMessage());
        return 1;
    }

    return 0;
})->purpose('Collect mature interest from active loans and post to GL');
