<?php
namespace App\Console\Commands;

use App\Models\CashRechargeOrder;
use Illuminate\Console\Command;

class OmnipayOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omnipayOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'OmnipayOrder';

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
        $this -> order();

    }

    private function order()
    {
        // 超过12小时的订单要删除
        $time = getTime() + 43200;

        CashRechargeOrder::where('time_add', '>', $time) -> where('status', 0) -> delete();
    }
}