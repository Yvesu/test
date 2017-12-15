<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class NoExitWord extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'NoExitWord';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'NoExitWord';

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
            $noExitWord_obj =  NoExitWord::distinct('keyword')->get(['keyword']);

            $arr = $noExitWord_obj->toArray();

            $noExitWord_arr = array_column($arr, 'keyword');

            \Cache::put('noExitWord',$noExitWord_arr,'60');
    }
}
