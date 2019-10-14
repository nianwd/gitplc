<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/7
 * Time: 16:14
 */

namespace App\Services\CoinServices;

use Web3\Eth;
use Web3\Personal;
use Web3\Web3;
use Web3\Contract;
use GuzzleHttp\Client;

class GethService
{
    public $web3;
    public $eth;
    public $personal;
    private $provider;

    public function __construct()
    {
        $this->provider = env('GETH_HOST');
        $this->web3 = new Web3($this->provider);
        $this->personal = new Personal($this->provider);
        $this->eth = new Eth($this->provider);
    }

    /*获取钱包信息*/
    public function getWalletInfo()
    {
        $this->web3->clientVersion(function ($err, $version) use (&$result) {
            if ($err !== null) {
                // do something
//                dd($err);
                return $result = 0;
            }
//            if (isset($client)) {
                return $result = 'Client version: ' . $version;
//            }
        });
        return $result;
    }

    /*获取交易详情*/
    public function getTransaction($transactionId)
    {
        // TODO: Implement getTransaction() method.
    }

    /*获取余额*/
    public function getBalance($account)
    {
        $this->eth->getBalance($account,function ($err,$data) use (&$result){
            if ($data){
                return $result = $data->value;
            }
            return $result = -1;
        });return $result;
    }

    /*列出所有账户*/
    public function listAccounts()
    {
//        $accountList='';
        $this->personal->listAccounts(function ($err, $account) use (&$accountList) {
            if ($err !== null) {
                // do something
                $accountList = 0;
                return 0;
            }
            $accountList = $account;
            //dd($accountList);
        });
       return $accountList;
    }

    /*创建一个账户*/
    public function newAccount($password)
    {
        return $this->interactiveEth('personal_newAccount',[$password]);
    }

    /*查询订单状态*/
    public function getTransactionReceipt($transactionHash)
    {
        $this->eth->getTransactionReceipt($transactionHash,function ($err,$result) use (&$data){
            if ($err != null){
                return $data = 0;//錯誤
            }
            if ($result){
                if (hexdec($result->status) == 1){
                    return $data = 1;//成功
                }
                return $data = -1;//失敗
            }
            return $data = 2;//無記錄,無此交易
//            if(hexdec($result->status));
//            return $data = 1;
        });
        return $data;
    }

    /*直接用guzzlehttp与以太坊交互*/
    public function interactiveEth($method,array $params)
    {
        $opts = [
            'json' => [
                'jsonrpc' => '2.0',
                'method' => $method,
                'params' => $params,
                'id' => time()
            ]
        ];
        $rsp = (new Client())->post($this->provider,$opts);
        if (isset(\GuzzleHttp\json_decode($rsp->getBody())->error)) return 0;

        return \GuzzleHttp\json_decode($rsp->getBody())->result;
    }

    /*转账交易
    参数1:转出账户
    参数2:转入账户
    参数3:数量  单位为eth
    参数4:密码
    参数5:gaslimit      参数6:gasprice 单价 单位为gwei
    */
    public function sendTransaction($fromAddress,$toAddress,$value,$password,$gaslimit,$gasprice)
    {//dd(base_convert('100000000000000000',10,16));
        $transaction = [[
            "from"=>$fromAddress,
            "to"=>$toAddress,
            "gas"=>"0x" . dechex($gaslimit),
            "gasPrice"=>'0x'.base_convert(bcmul($gasprice,'1000000000',0),10,16),
            "value"=>"0x" . base_convert(bcmul($value,'1000000000000000000',0),10,16),
            "data"=>"0xd46e8dd67c5d32be8d46e8dd67c5d32be8058bb8eb970870f072445675058bb8eb970870f072445675",
        ],"{$password}"];
//        $transaction = [[
//            "from"=>"0x7b8172e885fba4f0fd593ede603c067a7fb17971",
//            "to"=>"0x3c119f11ea139cc9432cc07d79d239c31acbb857",
//            "gas"=>"0x76c0",
//            "gasPrice"=>"0x9184e72a000",
//            "value"=>"0x1",
//            "data"=>"0xd46e8dd67c5d32be8d46e8dd67c5d32be8058bb8eb970870f072445675058bb8eb970870f072445675",
//        ],"123456"];
        return $this->interactiveEth('personal_sendTransaction',$transaction);
//        $this->personal->sendTransaction($transaction,"123456",function ($err, $account) {
//            if ($err !== null) {
//                // do something
//                dd($err);
//                return;
//            }
//            dd($account);
//        });

    }

}