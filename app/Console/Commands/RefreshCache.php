<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\ApiController;
use App\Service\CacheService;

class RefreshCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rates:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshs the app cash';

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
        $controller = \App::make(ApiController::class);
        $controller->getClear();
        $this->info('cache cleared');
    }
}
