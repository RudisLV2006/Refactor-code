<?php

namespace App\Exceptions;

use Exception;

class ProductOutOfStockException extends Exception
{
    public function __construct($message, $product)
    {
        //Varu modificēt message
        $message .= $product->id;
        parent::__construct($message);
    }
}
