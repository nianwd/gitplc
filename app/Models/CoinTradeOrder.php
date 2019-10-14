<?php
/**
 * Created by PhpStorm.
 * User: 77507
 * Date: 2019/10/11
 * Time: 16:47
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CoinTradeOrder extends Model
{
//`order_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
//`user_id` int(11) NOT NULL DEFAULT '0' COMMENT '交易发起人的用户id',
//`wallet_id` int(11) unsigned NOT NULL,
//`order_type` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '订单的类型(1代表转出,2代表转入)',
//`order_trade_hash` char(100) NOT NULL DEFAULT '0' COMMENT '虚拟货币区块交易认证哈希值，作为交易凭证',
//`order_trade_from` char(100) NOT NULL DEFAULT '0' COMMENT '虚拟货币交易发起地址',
//`order_trade_to` char(100) NOT NULL DEFAULT '0' COMMENT '虚拟货币交易接受地址',
//`order_trade_money` decimal(20,8) unsigned NOT NULL DEFAULT '0.00000000' COMMENT '交易的金额',
//`order_trade_fee` decimal(20,8) DEFAULT '0.00000000' COMMENT '交易的费用',
//`coin_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '虚拟货币类型名称id',
//`order_check_status` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '订单审核状态(默认0为待审核,1通过审核,2拒绝)',
//`order_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '订单状态，0代表发起并记录了记录，1代表已被2个或2个以上区块网络节点接受确认.',
//`transfer_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1区块成功2成功没有hash',
//`transfer_type` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '1区块2内部3tts',
//`is_usable` tinyint(4) NOT NULL DEFAULT '1' COMMENT '该条数据是否逻辑删除，0代表不可用，1代表可用',
//`created_at` char(20) NOT NULL DEFAULT '0' COMMENT '记录创建时间',
//`updated_at` char(20) NOT NULL DEFAULT '0' COMMENT '记录更新时间',

    protected $table = 'coin_trade_order';



    protected $primaryKey = 'order_id';



    protected $guarded = [];




    public function insertOne($transfer_type,$u_id,$w_id,$o_type,$from,$to,$amount,$fee,$coin_id,$order_check_status = 0,$order_status = 0)
    {
        $this->user_id = $u_id;
        $this->wallet_id = $w_id;
        $this->order_type = $o_type;
        $this->order_trade_from = $from;
        $this->order_trade_to = $to;
        $this->order_trade_money = $amount;
        $this->order_trade_fee = $fee;
        $this->coin_id = $coin_id;
        $this->order_check_status = $order_check_status;
        $this->order_status = $order_status;
        $this->transfer_type = $transfer_type;
        return $this->save();



    }









}