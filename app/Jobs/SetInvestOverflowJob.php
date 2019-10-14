<?php

namespace App\Jobs;

use App\Models\OverflowLog;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class SetInvestOverflowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user,$delay=300)
    {
        $this->user = $user;
//        $this->delay($delay);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = $this->user;

        DB::beginTransaction();

        try{
            $invest_earnings = $user->user_wallet_log()->where('log_type',7)->whereDate('created_at',date('Y-m-d'))->sum('amount');
            $team_earnings = $user->user_wallet_log()->where('log_type',8)->whereDate('created_at',date('Y-m-d'))->sum('amount');

            //团队溢出上限 根据用户当天理财收益的多少决定 例如当用户理财收益达到2000时 系统定义团队收益最多可以=2000*2
            $overflow_times = 1;
            $overflow_limit =  $invest_earnings * $overflow_times;

            $overflow = $team_earnings - $overflow_limit;
            if($overflow < 0){
                $overflow = 0;
            }

            $flag = $user->user_wallet_log()->where('log_type',9)->whereDate('created_at',date('Y-m-d'))->first();
            if(!$flag){
                //创建团队溢出记录
                $overflow_log = $user->overflow_logs()->create([
                    'invest_earnings' => $invest_earnings,
                    'team_earnings' => $team_earnings,
                    'overflow' => $overflow,
                ]);

                $user->update_wallet_and_log('money',-$overflow,9,'团队溢出',$overflow_log['log_id'],OverflowLog::class);
            }

            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();

            throw $exception;
        }
    }
}
