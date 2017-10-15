<?php
namespace Satouch\SSLCommerzIPN\Models;

use Illuminate\Database\Eloquent\Model;

class OrderLog extends Model
{
    protected $table    =   'orders_log';
    
    protected $fillable = [
        'order_id',
        'payment_log'               
    ];
}