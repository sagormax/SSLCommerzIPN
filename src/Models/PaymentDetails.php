<?php
namespace SSLWIRELESS\SSLCommerzIPN\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentDetails extends Model
{
    protected $table    =   'payment_details';
    
    protected $fillable = [
        'order_id',
        'currency',
        'paid_amount',
        'BDT_amount',
        'total_paid_with_bank_fee',
        'payment_method',
        'created_by',
        'comments'                
    ];
}