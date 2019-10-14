<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/19
 * Time: 14:00
 */

namespace App\Services\CoinServices;

use App\Http\Controllers\Web\Bitcoin\BitcoinController as BitcoinClient1;
use Denpa\Bitcoin\Client as BitcoinClient2;

class OmnicoreService
{

    private $bitcoinClient;//初始化接口对象
    private $bitcoinClient1;//备用接口对象
    private $walletPassword;//钱包秘钥

    public function __construct()
    {
        $this->walletPassword = env('OMNICORE_PASSWORD');
        $this->bitcoinClient = new BitcoinClient1(env('OMNICORE_USERNAME'), env('OMNICORE_UPASSWORD'),env('OMNICORE_HOST'),env('OMNICORE_PORT'));
        $this->bitcoinClient1 = new BitcoinClient2([
            'scheme' => 'http',
            'host'   => env('OMNICORE_HOST'),
            'port'   => env('OMNICORE_PORT'),
            'user'   => env('OMNICORE_USERNAME'),
            'pass'   => env('OMNICORE_UPASSWORD'),
        ]);
    }

    /*获取钱包信息*/
    public function getWalletInfo()
    {
        if ($result = $this->bitcoinClient->getwalletinfo()) return $result;return 0;
    }

    /*设置交易费率
    即每1000kb所需的手续费*/
    public function setTXFee($fee)
    {
        if ($this->bitcoinClient->settxfee($fee)) return 1;return 0;
    }

    /*交易费率估计
    参数:需要网络确认的节点数
    返回费率的估算值*/
    public function getEstimateFee(int $blocks)
    {
        if ($result = $this->bitcoinClient->estimatesmartfee($blocks))return number_format($result['feerate'],8);return 0;
    }

    /*返回与给定地址关联的帐户*/
    public function getAccount($address)
    {
        if ($result = $this->bitcoinClient->getaccount($address)) return $result;return 0;
    }

    /*返回具有帐户名称作为键，帐户余额作为值的数组*/
    public function listAccounts()
    {
        if ($result = $this->bitcoinClient->listaccounts()) return $result;return 0;
    }

    /*返回账户的余额
    默认property_id为31 usdt
    */
    public function getBalance($address,$propertyId = 31)
    {
        $result =  $this->bitcoinClient->omni_getbalance($address,$propertyId);
        if ($result) return $result['balance'];return 0;
    }

    /*返回用于接收此帐户付款的当前比特币地址。
    如果<account>不存在，它将与将返回的相关新地址一起创建*/
    public function getAccountAddress($account='')
    {
        if ($result = $this->bitcoinClient->getaccountaddress($account)){
            return $result;
        }else{
            $this->walletPassPhrase();
            if ($result = $this->bitcoinClient->getaccountaddress($account))
                return $result;
            return 0;
        }


    }

    public function newAccount($password)
    {
    }

    /*返回给定帐户的地址列表*/
    public function getAddressByAccount($account='')
    {
        if ($result = $this->bitcoinClient->getaddressesbyaccount($account)) return $result;return 0;
    }

    /*发起交易*/
    public function send($fromAddress,$toAddress,$propertyId,$amount,$feeAddress='')
    {
        $feeAddress = $feeAddress ? $feeAddress : $fromAddress;
        $result = $this->walletPassPhrase();//dd($result);
//        return $this->bitcoinClient->sendfrom($account,$address,$amount,$confirm,$comment);
        if (($result = $this->bitcoinClient->omni_send($fromAddress,$toAddress,$propertyId,$amount)))
            return $result;//return 0;
    }

    /*用于在服务器内部的账户中进行转账,无需手续费*/
    public function move($fromAccount,$toAccount,$amount)
    {
        if (($this->getBalance($fromAccount)>=$amount) && $this->bitcoinClient->move($fromAccount,$toAccount,$amount)) return 1;return 0;
    }

    /*获取一笔交易的详细信息*/
    public function getTransaction($transactionId)
    {
        if ($result = $this->bitcoinClient->gettransaction($transactionId)) return $result;return 0;
    }

    /*加密钱包*/
    public function encryptWallet($password)
    {
        if ($result = $this->bitcoinClient->encryptwallet($password)) return $result;return 0;
    }

    /*解锁钱包*/
    public function walletPassPhrase($password='')
    {
//        dd($this->bitcoinClient1->walletpassphrase('888888',10));
        if ($password){
            if ($this->bitcoinClient->walletpassphrase($password,10))return 1;return 0;
        }else{
            if ($this->bitcoinClient->walletpassphrase($this->walletPassword,10)) return 1;return 0;
        }
    }

    /*列出*/
    public function listreceivedbyaccount()
    {
        if ($result = $this->bitcoinClient->listreceivedbyaccount()) return $result;return 0;
    }

    /*列出钱包中可供交易的output*/
    public function listUnspent()
    {
        if ($result = $this->bitcoinClient->listunspent()) return $result;return 0;
    }

    /*创建一个事务*/
    public function createRawTransaction($data1,$data2)
    {
        return $this->bitcoinClient->createrawtransaction($data1,$data2);
    }

    /*查询事务详情,可用于观测费用*/
    public function fundRawTransaction($transaction)
    {
        return $this->bitcoinClient->fundrawtransaction($transaction);
    }






}