<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CacheSensitiveWord extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CacheSensitiveWord';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CacheSensitiveWord';

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
        $sensitiveword = SensitiveWord::distinct('sensitive_word')->get(['sensitive_word']);

        $arr = $sensitiveword->toArray();

        $sensitivewords = array_column($arr, 'sensitive_word');

        Cache::put('sensitivewords',$sensitivewords,'1450');

    }
}
