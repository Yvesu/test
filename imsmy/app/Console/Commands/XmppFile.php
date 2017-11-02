<?php
namespace App\Console\Commands;

use CloudStorage;
use App\Models\XmppUserFiles;
use Illuminate\Console\Command;

class XmppFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xmppFile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'XmppFile';

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
        \Log::info('XmppFile Start');
        $this->delete();
        \Log::info('XmppFile End');
    }

    // 删除超过七天的文件
    private function delete()
    {
        // 过期的时间戳分界线
        $time = getTime() - 7*24*60*60;

        // 删除$time之前的文件
        $files = XmppUserFiles::where('time_add','<',$time)->get();

        foreach($files as $key => $value){
            CloudStorage::delete($value -> address);
            $value -> delete();
        }
    }
}