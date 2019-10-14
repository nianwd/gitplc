<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/7
 * Time: 14:30
 */

namespace App\Services;


use App\Models\CoinList;
use App\Models\UserWallet;
use App\Traits\Tools;
use Illuminate\Support\Facades\DB;


class CoinService
{
    use Tools;
//    public $coinServer;
    private $walletDetail;
    private $coinType;
    private $coinServer;

    public function __construct()
    {
        $this->walletDetail = new UserWallet();
        $this->coinType = new CoinList();
    }

    /*创建账户逻辑*/
    public function createNewAccount($userId,$coinId,$account = '',$coinName = '',$address='',$password='')
    {

//        $this->coinServer = $coinServer;
        if ($coinName == 'BTC'){

//            if ($address = $coinServer->getAccountAddress($account))
            return $this->walletDetail->saveOneRecord($userId,$coinId,$address,$account);

        }else if ($coinName == 'ETH'){

//            $password = 'eth_pass_' . $userId;
//            if ($address = $coinServer->newAccount($password))
            return $this->walletDetail->saveOneRecord($userId,$coinId,$address,'',0,$password);

        }else if (EthToken::where('coin_id',$coinId)->first()) {//判断是否是以太坊代币

            if (! $result = $this->coinType->getRecordByCoinName('ETH')) return 0;//查询以太坊coin_id
            if ($result = $this->walletDetail->getOneRecord($userId,$result->coin_id))//判断是否已有以太坊账户
                return $this->walletDetail->saveOneRecord($userId,$coinId,'','',$result->wallet_id);
        }else if (OmnicoreToken::where('coin_id',$coinId)->first()){
                return $this->walletDetail->saveOneRecord($userId,$coinId,'','');
        }else{
            return $this->walletDetail->saveOneRecord($userId,$coinId);
        }

    }

    /*创建区块钱包地址*/
    public function createBlockAccount($coinServer,$wallet,$coin,$userId)
    {
        if ($coin->type == 1 || $coin->type == 2){//dd($coinServer->getWalletInfo());
            $existsAddressWallet = (new UserWallet())->getExistsEthAddress($userId);

            if ($existsAddressWallet === false){
                $password = 'eth_pass_' . $userId;
                $address = $coinServer->newAccount($password);
                if (!$address) return true;
            }else{
                $password = $existsAddressWallet->wallet_password;
                $address = $existsAddressWallet->wallet_address;
            }


                return $wallet->update(['wallet_password'=>$password,'wallet_address'=>$address]);

        }
    }

/////////////////////////////////////////////////////////////////
    /*提币逻辑*/
    public function withdrawCoin($account='',$walletId,$toAddress,$amount,$coinId,$coinName,$userId)
    {
        $transfer_type = 0;
        if ((new ChatServer())->checkIsAddress($toAddress)){
            $transfer_type = 5;
        }
        //$this->coinServer = $coinServer;
        switch ($coinName){
            case 'BTC':
                return $this->bitcoinWithdraw($account,$walletId,$toAddress,$amount,$coinId,$userId,$transfer_type);
                break;
            case 'ETH':
                return $this->ETHWithdraw($walletId,$toAddress,$amount,$coinId,$userId,$transfer_type);
                break;
            case 'USDT':
                return 0;
                break;
            default:
                return $this->TOKENWithdraw($walletId,$toAddress,$amount,$coinId,$userId,$transfer_type);
        }
    }

    /*计算费用*/
    private function getFees(array $coinFee,$amount)
    {
        switch ($coinFee['fee_type']){
            case 1:
                return $coinFee['fixed_fee'];
                break;
            case 2:
                return bcmul($coinFee['percent_fee']/100,$amount,8);
                break;
        }

    }

    /*处理比特币的提币逻辑*/
    public function bitcoinWithdraw($account,$walletId,$toAddress,$amount,$coinId,$userId,$transfer_type)
    {
        if (! $this->isBTCAddress($toAddress)) return -1;
        //if (!$this->coinServer->getWalletInfo()) return 0;
//        dd($this->coinServer->move('','btp_center',0.008));
        //$fee = $this->coinServer->checkTransactionFees($toAddress,$amount);//费用预估

        //if ($fee == 0) return 0;//钱包错误

        $wallet = $this->walletDetail->getRecordByWalletId($walletId);//查询钱包信息

        if (! $wallet['coin_fees']) return 0;

        $fee = $this->getFees($wallet['coin_fees'],$amount);

        $totalAmount = bcadd($fee,$amount,8);

        if ((bccomp($totalAmount,$wallet['wallet_withdraw_balance'],8) == 1)) return 1;//余额不足

        //if (bccomp($totalAmount,$this->coinServer->getBalance($wallet['center_wallet']['center_wallet_account']),8) == 1) return 0;//中央钱包没钱了,让他联系客服吧

        DB::beginTransaction();

        $result1 = $this->coinTradeOrder->saveOneRecord($userId,$coinId,'','',$toAddress,$amount,1,$fee,$transfer_type);
        $result2 = $this->walletDetail->decrementRecord($walletId,'wallet_withdraw_balance',$totalAmount);
        $result3 = $this->walletDetail->incrementRecord($walletId,'wallet_freeze_balance',$totalAmount);
        (new WalletFlow())->insertOne($wallet['user_id'],$wallet['wallet_id'],$wallet['coin_id'],$amount,1,2,'提币',2,1,$fee);

        if ($result1 && $result2 && $result3){
            //if ($result4 = $this->coinServer->sendFrom($wallet['center_wallet_account'],$toAddress,$amount)){//由中心钱包进行转账,此处不再转账,交由后台审核后进行转账
                //$this->coinTradeOrder->updateOneRecord($result1->order_id,['order_trade_hash'=>$result4]);
                DB::commit();
                return 2;
            //}

        }
        DB::rollBack();
        return 0;

    }

    /*处理以太坊的提币逻辑*/
    public function ETHWithdraw($walletId,$toAddress,$amount,$coinId,$userId,$transfer_type)
    {

        if (! $this->isETHAddress($toAddress)) return -1;

        $wallet = $this->walletDetail->getRecordByWalletId($walletId);//查询钱包信息

        if ($wallet['coin_fees'] == null) return 0;

        $fees = $this->getFees($wallet['coin_fees'],$amount);
        //$fees = bcdiv($wallet['center_wallet']['eth_gaslimit']*$wallet['center_wallet']['eth_gasprice'],'1000000000',8);
        $totalAmount = bcadd($amount,$fees,8);//计算总金额

        if ((bccomp($totalAmount,$wallet['wallet_withdraw_balance'],8) == 1)) return 1;//账户余额不足


        DB::beginTransaction();
        $result1 = $this->coinTradeOrder->saveOneRecord($userId,$coinId,'','',$toAddress,$amount,1,$fees,$transfer_type);//订单入库
        $result2 = $this->walletDetail->decrementRecord($walletId,'wallet_withdraw_balance',$totalAmount);//余额递减
        $result3 = $this->walletDetail->incrementRecord($walletId,'wallet_freeze_balance',$totalAmount);//
        (new WalletFlow())->insertOne($wallet['user_id'],$wallet['wallet_id'],$wallet['coin_id'],$amount,1,2,'提币',2,1,$fees);
        if ($result1 && $result2 && $result3){
            DB::commit();return 2;
        }
//        dd($this->coinServer->sendTransaction($wallet['center_wallet']['center_wallet_address'],$toAddress,$amount,$wallet['center_wallet']['center_wallet_password'],$wallet['center_wallet']['eth_gaslimit'],$wallet['center_wallet']['eth_gasprice']));
        DB::rollBack();
        return 0;
    }

    /*处理以太坊代币的提币逻辑*/
    public function TOKENWithdraw($walletId,$toAddress,$amount,$coinId,$userId,$transfer_type)
    {
        if (! $this->isETHAddress($toAddress)) return -1;

        $wallet = $this->walletDetail->getRecordByWalletId($walletId);//查询钱包信息

        if (! $wallet['coin_fees']) return 0;

        //$parentWallet = $this->walletDetail->getRecordByWalletId($wallet['parent_id']);//查询父钱包信息
        //$fees = bcdiv($wallet['center_wallet']['eth_gaslimit']*$wallet['center_wallet']['eth_gasprice'],'1000000000',8);//计算费用

        $fees = $this->getFees($wallet['coin_fees'],$amount);
        $totalAmount = bcadd($amount,$fees,8);//计算总金额

        if ((bccomp($totalAmount,$wallet['wallet_withdraw_balance'],8) == 1)) return 1;//账户余额不足
//dd($amount);
        DB::beginTransaction();
        $result1 = $this->coinTradeOrder->saveOneRecord($userId,$coinId,'','',$toAddress,$amount,1,$fees,$transfer_type);//订单入库
        $result2 = $this->walletDetail->decrementRecord($walletId,'wallet_withdraw_balance',$totalAmount);
        $result3 = $this->walletDetail->incrementRecord($walletId,'wallet_freeze_balance',$totalAmount);
        (new WalletFlow())->insertOne($wallet['user_id'],$wallet['wallet_id'],$wallet['coin_id'],$amount,1,2,'提币',2,1,$fees);
        //$result3 = $this->walletDetail->decrementRecord($parentWallet['wallet_id'],'wallet_usable_balance',$fees);
        if ($result1 && $result2 && $result3){
            DB::commit();return 2;
        }
        DB::rollBack();return 0;

    }

/////////////////////////////////////////////////////
    /*处理提币订单状态的查询及更新逻辑*/
    public function updateOrderStatus(CoinServerInterface $coinServer,$userId,$coinId,$orderId,$orderHash,$totalAmount,$coinName)
    {
        $this->coinServer = $coinServer;
        switch ($coinName){
            case 'BTC':
                return $this->updateBTCOrder($userId,$coinId,$orderId,$orderHash,$totalAmount);
                break;
            case 'ETH':
                return $this->updateGETHOrder($orderId,$orderHash,$userId,$coinId,$totalAmount);
                break;
//            case 'BABC':
//                return $this->updateGETHOrder($orderId,$orderHash,$userId,$coinId,$totalAmount);
//                break;
            case 'USDT':
                return 1;
                break;
            default:
                return $this->updateGETHOrder($orderId,$orderHash,$userId,$coinId,$totalAmount);
                break;
                //待完善
        }

    }

    /*更新比特币的订单状态*/
    public function updateBTCOrder($userId,$coinId,$orderId,$orderHash,$totalAmount)
    {
        if ($this->coinServer->getWalletInfo() == 0) return 0;
//        dd($coinServer->getAccountAddress('btp_center'));
        $orderStatus = $this->coinServer->getTransaction($orderHash);
        if ($orderStatus['confirmations'] >= 2){
            DB::beginTransaction();
            $result1 = $this->coinTradeOrder->updateOneRecord($orderId,['order_status'=>1]);
            //$result2 = $this->walletDetail->decrementRecordC(['user_id'=>$userId,'coin_id'=>$coinId,'is_usable'=>1],'wallet_freeze_balance',$totalAmount);
            if ($result1){
                DB::commit();return 1;
            }
            DB::rollBack();return 0;
        }
        return 0;
    }

    /*更新以太坊的订单状态*/
    public function updateGETHOrder($orderId,$orderHash,$userId,$coinId,$totalAmount)
    {
//        dd($this->coinServer->getTransactionReceipt('0x1a906a1c58ec27493bd5f8c6e6a31e987f7170dd5c6063a9c603c4935d9f803a'));
        if ($this->coinServer->getTransactionReceipt($orderHash) == 1){
            DB::beginTransaction();
            if (
                $this->coinTradeOrder->updateOneRecord($orderId,['order_status'=>1])
                //&& $this->walletDetail->decrementRecordC(['user_id'=>$userId,'coin_id'=>$coinId,'is_usable'=>1],'wallet_freeze_balance',$totalAmount)
            ){
                DB::commit();return 1;
            }

            DB::rollBack();return 0;
        }
        return 0;
    }

///////////////////////////////////////////////////////
    /*处理更新用户钱包余额的逻辑*/
    public function updateUserWallet(CoinServerInterface $coinServer,$wallet,$token=[])
    {
        $this->coinServer = $coinServer;

        switch ($wallet['coin_name']['coin_name']){
            case 'BTC':
                return $this->updateBTCWallet($wallet);
                break;
            case 'ETH':
                return $this->updateGETHWallet($wallet);
                break;
            case 'USDT':
                return $this->updateUSDTWallet($wallet);
                break;
            default:
                return $this->updateTokenWallet($wallet,$token);
        }

    }
    /*处理比特币钱包的更新逻辑*/
    public function updateBTCWallet($wallet)
    {
        if ($this->coinServer->getWalletInfo() == 0) return 0;

//        dd($this->coinServer->getAccountAddress('btp_center'));
        $bitCoreBalance = $this->coinServer->getBalance($wallet['wallet_account']);//dd($this->coinServer->listUnspent());
        if ($bitCoreBalance == 0) return 0;


        $totalBalance = bcadd($bitCoreBalance,$wallet['wallet_divert_amount'],8);
        $usable = bcsub($totalBalance,$wallet['wallet_into_balance_amount'],8);
        if ($usable <= 0) return 1;
        DB::beginTransaction();
        if (
            $this->walletDetail->incrementRecord($wallet['wallet_id'],'wallet_into_balance_amount',$usable)
            &&  $this->walletDetail->incrementRecord($wallet['wallet_id'],'wallet_usable_balance',$usable)
            && $this->coinTradeOrder->saveOneRecord($wallet['user_id'],$wallet['coin_id'],'','',$wallet['wallet_address'],$usable,2)
            && (new WalletFlow())->insertOne($wallet['user_id'],$wallet['wallet_id'],$wallet['coin_id'],$usable,2,1,'转入',1)
        ){
            DB::commit();return 1;
        }
        DB::rollBack();return 0;

    }

    public function updateUSDTWallet($wallet)
    {
        $propertyId = OmnicoreToken::where('coin_id',$wallet['coin_id'])->value('property_id');
        $realBalance = $this->coinServer->getBalance($wallet['wallet_address'],$propertyId);
        if (!$realBalance) return 0;


        $totalBalance = bcadd($realBalance,$wallet['wallet_divert_amount'],8);
        $usable = bcsub($totalBalance,$wallet['wallet_into_balance_amount'],8);
        if ($usable <= 0) return 1;
        DB::beginTransaction();
        if (
            $this->walletDetail->incrementRecord($wallet['wallet_id'],'wallet_into_balance_amount',$usable)
            &&  $this->walletDetail->incrementRecord($wallet['wallet_id'],'wallet_usable_balance',$usable)
            && $this->coinTradeOrder->saveOneRecord($wallet['user_id'],$wallet['coin_id'],'','',$wallet['wallet_address'],$usable,2)
            && (new WalletFlow())->insertOne($wallet['user_id'],$wallet['wallet_id'],$wallet['coin_id'],$usable,2,1,'转入',1)
        ){
            DB::commit();return 1;
        }
        DB::rollBack();return 0;
    }

    /*处理以太坊钱包的更新逻辑*/
    public function updateGETHWallet($wallet)
    {
        //dd($this->coinServer->getBalance('0x7b8172e885fba4f0fd593ede603c067a7fb17971')/'1000000000000000000');
        $realBalance = bcdiv($this->coinServer->getBalance($wallet['wallet_address']),'1000000000000000000',8);//只保留八位小数,剩下的自动舍除
        if ($realBalance == -1) return 0;


        $totalBalance = bcadd($realBalance,$wallet['wallet_divert_amount'],8);
        $usable = bcsub($totalBalance,$wallet['wallet_into_balance_amount'],8);
        if ($usable <= 0) return 1;
        DB::beginTransaction();
        if (
            $this->walletDetail->incrementRecord($wallet['wallet_id'],'wallet_into_balance_amount',$usable)
            &&  $this->walletDetail->incrementRecord($wallet['wallet_id'],'wallet_usable_balance',$usable)
            && $this->coinTradeOrder->saveOneRecord($wallet['user_id'],$wallet['coin_id'],'','',$wallet['wallet_address'],$usable,2)
            && (new WalletFlow())->insertOne($wallet['user_id'],$wallet['wallet_id'],$wallet['coin_id'],$usable,2,1,'转入',1)
        ){
            DB::commit();return 1;
        }
        DB::rollBack();return 0;

    }

    /*处理以太坊代币钱包更新逻辑*/
    public function updateTokenWallet($wallet,$token)
    {

        //dd($this->coinServer->getBalance('0xb56f204877caa2afc6b587dbe308491917f2c76f'));
        $decimal = bcpow(10,$token['token_decimal'],0);
        $realBalance = bcdiv($this->coinServer->getBalance($wallet['wallet_address']),$decimal,8);//只保留八位小数,剩下的自动舍除
        if ($realBalance == -1) return 0;
        $totalBalance = bcadd($realBalance,$wallet['wallet_divert_amount'],8);
        $usable = bcsub($totalBalance,$wallet['wallet_into_balance_amount'],8);
        if ($usable <= 0) return 1;
        DB::beginTransaction();
        if (
            $this->walletDetail->incrementRecord($wallet['wallet_id'],'wallet_into_balance_amount',$usable)
            &&  $this->walletDetail->incrementRecord($wallet['wallet_id'],'wallet_usable_balance',$usable)
            && $this->coinTradeOrder->saveOneRecord($wallet['user_id'],$wallet['coin_id'],'','',$wallet['wallet_address'],$usable,2)
            && (new WalletFlow())->insertOne($wallet['user_id'],$wallet['wallet_id'],$wallet['coin_id'],$usable,2,1,'转入',1)
        ){
            DB::commit();return 1;
        }
        DB::rollBack();return 0;
    }



//////////////////////////////////////////////////////

}