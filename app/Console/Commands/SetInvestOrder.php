<?php

namespace App\Console\Commands;

use App\Models\InvestOrder;
use Illuminate\Console\Command;

class SetInvestOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setInvestOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $invest_orders = InvestOrder::query()
            ->where('status',1)
            ->where(function ($query){
                $query->whereNull('set_last_time')->orWhereDate('set_last_time','!=',date('Y-m-d'));
            })
            ->get();

//        dd($invest_orders);

        foreach ($invest_orders as $invest_order) {
            $res = $invest_order->setInvestOrder();
            print_r($res);
        }

        echo "订单结算完成!\n";
        return;
    }
}
