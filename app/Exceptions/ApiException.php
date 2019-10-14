<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/25
 * Time: 14:16
 */

namespace App\Exceptions;

use App\Http\Response\ApiResponse;
use Exception;
use Throwable;

class ApiException extends Exception
{

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
