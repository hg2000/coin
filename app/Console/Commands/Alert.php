<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Service\RateService;

class Alert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rates:alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends an alert when rates change.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $rateService = \App::make(RateService::class);
        $rateService->rateChangeAlert();
        $this->info('Alerts sent');
    }
}
