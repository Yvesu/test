<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CacheKeywords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CacheKeywords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CacheKeywords';

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
        $keyword_obj = Keywords::distinct('keyword')->get(['keyword']);

        $arr = $keyword_obj->toArray();

        $keyword_arr = array_column($arr, 'keyword');

        \Cache::put('keywords',$keyword_arr,'1450');
    }
}
