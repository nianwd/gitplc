<?php

namespace App\Models;

use App\Exceptions\ApiException;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $primaryKey = 'user_id';

    /*表名称*/
    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $guarded = [];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password','paypwd','login_code'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $attributes = [
        'sex' => 0,
        'status' => 'enable',
        'user_group' => 1,
    ];

    protected $appends = ['user_group_name','set_paypwd'];

    public function getSetPaypwdAttribute(){
        return blank($this->paypwd) ? 0 : 1;
    }

    public function getUserGroupNameAttribute(){
        return UserGroup::query()->where('group_id',$this->user_group)->value('group_name');
    }

    public function getAvatarAttribute($value)
    {
        if(blank($value)){
            $avatar = $this->sex == 2 ? env('IMG_URL') . 'female.png' : env('IMG_URL') . 'male.png';
        }else{
            $avatar = env('IMG_URL') . $value;
        }

        return $avatar;
    }

    public function getUserByPhone($phone)
    {
        return $this->newQuery()->where(['phone'=>$phone])->first();
    }

    public function getUserByEmail($email)
    {
        return $this->newQuery()->where('email',$email)->first();
    }

    public static function gen_invite_code($length = 8)
    {
        $pattern = '0123456789';
        $code = self::gen_comm($pattern, $length);
        $users = User::query()->where('invite_code', $code)->first();
        if ($users) {
            return self::gen_invite_code($length);
        } else {
            return $code;
        }
    }

    public static function gen_login_code($length = 10)
    {
        $pattern = '01234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return self::gen_comm($pattern, $length);
    }

    private static function gen_comm($content, $length)
    {
        $key = '';
        for ($i = 0; $i < $length; $i++) {
            $key .= $content{mt_rand(0, strlen($content) - 1)};    //生成php随机数
        }

        return $key;
    }

    public function passwordHash($password)
    {
        return password_hash($password,PASSWORD_DEFAULT);
    }

    public function verifyPassword($password,$pHash)
    {
        return password_verify($password,$pHash);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    //更新用户钱包 并记录日志
    public function update_wallet_and_log($rich_type,$amount,$log_type,$log_note='',$logable_id=0,$logable_type='')
    {
        //如果$amount为零，则不记录;
//        if ($amount == 0) {
////            return;
//            throw new ApiException('参数错误');
//        }

        if( blank($this->user_wallet) ){
            $user_wallet = $this->user_wallet()->create(['user_id'=>$this->user_id]);
        }else{
            $user_wallet = $this->user_wallet;
        }

        $money = $user_wallet->$rich_type;
//        dd($money);
        if ($amount < 0 && $money < abs($amount)) {
            throw new ApiException('资产不足');
        }

        if ($amount > 0) {
            $user_wallet->increment($rich_type, abs($amount));
        } else {
            $user_wallet->decrement($rich_type, abs($amount));
        }

        $this->user_wallet_log()->create([
            'rich_type' => $rich_type,
            'amount' => $amount,
            'log_type' => $log_type,
            'log_note' => $log_note,
            'logable_id' => $logable_id,
            'logable_type' => $logable_type,
        ]);

    }

    public function parent_user()
    {
        return $this->belongsTo('App\Models\User', 'pid');
    }

    public function children()
    {
        return $this->hasMany('App\Models\User', 'pid');
    }

    public function direct_user_count()
    {
        return $this->children()->count();
    }

    //根据用户取出无限级子用户
    public static function getSubChildren($user_id){
        $users = self::all();

        if(blank($users)){
            return [];
        }else{
            $users = $users->toArray();
        }

        $subIds = get_tree_child2($users,$user_id);

        return $subIds;
    }

    public static function user_status()
    {
        //用户状态： deleted删除，lock锁定 ，enable 正常，trans_lock交易功能锁定
        return [
            'enable'=> '正常',
            'lock'=> '锁定',
            'withdraw_lock'=> '提现锁定',
//            'deleted'=> '删除',
        ];
    }

    public function user_wallet()
    {
        return $this->hasOne('App\Models\UserWallet', 'user_id','user_id');
    }

    public function user_wallet_log(){
        return $this->hasMany('App\Models\UserWalletLog','user_id','user_id');
    }

    public function user_upgrade_log(){
        return $this->hasMany('App\Models\UserUpgradeLog','user_id','user_id');
    }

    public function user_withdraw_records(){
        return $this->hasMany('App\Models\UserWithdrawRecord','user_id','user_id');
    }

    public function invest_orders(){
        return $this->hasMany(InvestOrder::class,'user_id','user_id');
    }

    public function conversion_orders(){
        return $this->hasMany(ConversionOrder::class,'user_id','user_id');
    }

    public function overflow_logs(){
        return $this->hasMany(OverflowLog::class,'user_id','user_id');
    }

}
