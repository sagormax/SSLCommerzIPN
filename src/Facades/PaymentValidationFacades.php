<?php
namespace SSLWIRELESS\SSLCommerzIPN\Facades;

use Illuminate\Support\Facades\Facade;

class PaymentValidationFacades extends Facade
{
    protected static function getFacadeAccessor() 
    { 
        return 'payment-validator';
    }    
}