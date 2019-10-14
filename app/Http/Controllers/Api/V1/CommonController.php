<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommonController extends ApiController
{
    //

    //获取实时PLC币值
    public function getPlcPrice()
    {
        $plc_price = getCoinCnyPrice();

        $data['plc_price'] = $plc_price;

        return $this->successWithData($data);
    }

}
