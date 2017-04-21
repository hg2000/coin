<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Trade;

class ClearHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trades:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears the trade history.';

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
        Trade::truncate();
        $this->info('Trade history cleared.');
    }
}
