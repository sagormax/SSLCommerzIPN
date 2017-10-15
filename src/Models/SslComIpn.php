<?php
namespace Satouch\SSLCommerzIPN\Models;

use Illuminate\Database\Eloquent\Model;

class SslComIpn extends Model
{
    protected $table    =   'sslcommerz_ipn';
    
    protected $fillable = [
        'hit_receive_time',
        'hash_verify',
        'status',
        'status_message',
        'trx_status',
        'validation_call_status',
        'tran_date',
        'tran_id',
        'val_id',
        'amount',
        'amount_with_bank_fee',
        'currency',
        'store_amount',
        'bank_tran_id',
        'card_type',
        'card_no',
        'card_issuer',
        'card_brand',
        'card_issuer_country',
        'card_issuer_country_code',
        'store_id'                
    ];
}