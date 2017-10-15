<?php
namespace Satouch\SSLCommerzIPN\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'site_id',
        'order_prefix',
        'voucher_number',
        'customer_name',
        'email',
        'contact',
        'details',
        'payment_type',
        'payment_status',
        'currency',
        'currency_amount',
        'BDT_amount',
        'discount',
        'total_cost',
        'emi_avail',
        'status',
        'created_by',
    ];
}
