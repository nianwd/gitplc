<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Jobs\SetInvestOverflowJob;
use Illuminate\Console\Command;

class SetInvestOverflow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setInvestOverflow';

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
//        $users = User::query()->whereHas('user_wallet_log', function ($q) {
//            $q->where('log_type','=',7)->whereDate('created_at',date('Y-m-d'));
//        })->whereHas('user_wallet_log', function ($q) {
//            $q->where('log_type','=',8)->whereDate('created_at',date('Y-m-d'));
//        })->get();
        $users = User::query()->whereHas('user_wallet_log', function ($q) {
            $q->where('log_type','=',8)->whereDate('created_at',date('Y-m-d'));
        })->get();
//dd($users->toArray());
        foreach ($users as $user) {
            dispatch(new SetInvestOverflowJob($user));
//            echo "计算用户{$user['user_id']}团队溢出\n";
        }

        echo "团队溢出计算完成!\n";
        return;
//        info("==========团队溢出计算完成==========");
    }
}
