<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/20
 * Time: 15:32
 */

namespace App\Services\CoinServices;

use Web3\Eth;
use Web3\Personal;
use Web3\Contract;
use GuzzleHttp\Client;

class GethTokenService
{

    private $contract;
    public $eth;
    private $personal;
    private $provider;
    private $contractAddress;


    public function __construct($contractAddress,$abi)
    {
        $this->provider = env('GETH_HOST');
        $this->eth = new Eth($this->provider);
        $this->personal = new Personal($this->provider);
        $this->contractAddress = $contractAddress;
        $this->contract = new Contract($this->provider,$abi);
    }

    public function getWalletInfo()
    {
        // TODO: Implement getWalletInfo() method.
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

    public function getBalance($account)
    {
        $this->contract->at($this->contractAddress)->call('balanceOf',$account,function ($err,$data) use (&$balance){
            if ($data){
                return $balance = $data[0]->value;
            }
                return $balance = -1;
        });
        return $balance;
    }

    /*解锁账户*/
    public function unlockAccount($address,$password)
    {
        $param = [$address,$password,10];
        $result = $this->interactiveEth('personal_unlockAccount',$param);
        if ($result) return 1;return 0;
    }

    /*发起交易*/
    public function sendTransaction($fromAddress,$password,$toAddress,$amount,$gaslimit=60000,$gasPrice=10,$value = 0)
    {
        if ($this->unlockAccount($fromAddress,$password) != 1) return 0;

        $this->contract->setAttribute($this->contractAddress,$fromAddress,$gaslimit,$gasPrice,$value)->send('transfer',$toAddress,$amount,function ($err,$data) use (&$result) {
            if ($err !== null){
                return $result = -1;
            }
            if (strlen($data) <5) return -2;//成功但没有获取到hash
            return $result = $data;
        });
        return $result;

    }

    /*获取symbol*/
    public function getSymbol()
    {
        $this->contract->at($this->contractAddress)->call('symbol',function ($err,$data) use (&$result) {
            if ($err){
                return $result = 0;
            }
            return $result = $data[0];
        });
        return $result;
    }

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
//        dd(\GuzzleHttp\json_decode($rsp->getBody()));
        return \GuzzleHttp\json_decode($rsp->getBody())->result;
    }





    public function getTransaction($transactionId)
    {
        // TODO: Implement getTransaction() method.
    }



    public function newAccount($password)
    {
        // TODO: Implement newAccount() method.
    }

}