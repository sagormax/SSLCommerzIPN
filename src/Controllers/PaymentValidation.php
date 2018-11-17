<?php
namespace Satouch\SSLCommerzIPN\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Satouch\SSLCommerzIPN\Payments\Checkout;
use Satouch\SSLCommerzIPN\Payments\Validator;
use Satouch\SSLCommerzIPN\Traits\PaymentHashValidation;
// use Satouch\SSLCommerzIPN\Traits\PaymentChecking;
use Satouch\SSLCommerzIPN\Traits\SslCommerzValidate;
use Satouch\SSLCommerzIPN\Traits\WriteLogTrait;
use Satouch\SSLCommerzIPN\Models\SslComIpn;
use Satouch\SSLCommerzIPN\Models\Order;
use Satouch\SSLCommerzIPN\Models\PaymentDetails;

/**
 * This is a payment validation class
 *
 * @param storeID and storePassword
 * @return payment validation response with status
 */
class PaymentValidation extends BaseController
{
    use PaymentHashValidation, SslCommerzValidate, WriteLogTrait;

    private $tran_id  = 0;
    private $feedback = false;
    protected $validator;
    protected $checkout;

    public function __construct()
    {
          $this->validator = new Validator();
          $this->checkout  = new Checkout();
    }

    /***
     * SSLCOMMERZ HASH validator
     *
     * validate hash and verify_sign
     */
    public function validate( $storePassword )
    {
        $this->feedback = $this->validator->validate($storePassword);

        return $this->feedback;
    }

    /***
     * SSLCOMMERZ IPN hit 
     *
     * Server to server hit 
     * @permission you need to whitelist this server IP with SSLCOMMERZ
     */
    public function sslcommerz_ipn_data_insert( $storeID, $storePassword, $payment_validate_api_url, $request )
    {
        $this->writeLog(" sslcommerz_ipn_data_insert method >>> : Fire \n");

        # Hash validation
        $this->feedback     = $this->validate( $storeID, $storePassword, $request );

        $this->writeLog(" sslcommerz_ipn_data_insert validation >>> :". $this->feedback ."\n");
        if( $this->feedback )
        {
            /**
             * Call SSL COMMERZ VALIDATE URL
             *
             * @get store_id, store_pass, validate_url
             * @return true / false
             */
            $is_validate    = $this->call_validate_URL($storeID, $storePassword, $payment_validate_api_url, $request);
            if( !$is_validate )
            {
                $this->writeLog(" sslcommerz call_validate_URL validation >>> : Failed \n");
                // return false;
                return json_encode([
                    'status' => false,
                    'data'   => [],
                    'message'=> "sslcommerz URL validation failed. Please see logs"
                ]);
            }

            /**
             * Save IPN POST data to ssl_commerz_ipn table
             *
             * @return true or false
             */
            $status         = $request->status;
            $tran_id        = $request->tran_id;
            $val_id         = $request->val_id;
            $verify_sign    = $request->verify_sign;
            $verify_key     = $request->verify_key;
            $postAmount     = $request->amount;
            $risk_level     = $request->risk_level;
            $risk_title     = $request->risk_title;

            $status_message = '';
            if( $risk_level == '1' ) 
            {
                $status_message = "Payment is Risky. Transaction ID #".$tran_id." has been charged successfully. Please contact our support center > support@sslwireless.com.";
            }
            if( $status == 'VALIDATED' ) 
            {
                $status_message = "Payment is Risky. Transaction ID #".$tran_id." already validated by you. Please contact our support center > support@sslwireless.com.";
            }
            elseif( $status == 'CANCELLED' ) 
            {
                $status_message = "Transaction ID #".$tran_id." has been cancelled by the customer. Please contact our support center > support@sslwireless.com.";
            }
            elseif( $status == 'FAILED' ) 
            {
                $status_message = "Transaction ID #".$tran_id." has been failed. The card holder is not authorized by gateway. Please contact our support center > support@sslwireless.com.";
            }
            elseif( $status == 'INVALID' ) 
            {
                $status_message = "Transaction ID #".$tran_id." is invalid. Please contact our support center > support@sslwireless.com.";
            }

            $this->writeLog(" sslcommerz_ipn status_message >>> :". $status_message ."\n");

            $ipn_is_insert = SslComIpn::create([
                'hit_receive_time'          => date("Y-m-d H:i:s"),
                'hash_verify'               => 'success',
                'status'                    => 0,
                'status_message'            => $status_message,
                'trx_status'                => $request->status,
                'validation_call_status'    => ( $is_validate ) ? 1 : 0,
                'tran_date'                 => $request->tran_date,
                'tran_id'                   => $request->tran_id,
                'val_id'                    => $request->val_id,
                'amount'                    => $request->amount,
                'amount_with_bank_fee'      => $request->currency_amount,
                'currency'                  => $request->currency_type,
                'store_amount'              => $request->store_amount,
                'bank_tran_id'              => $request->bank_tran_id,
                'card_type'                 => $request->card_type,
                'card_no'                   => $request->card_no,
                'card_issuer'               => $request->card_issuer,
                'card_brand'                => $request->card_brand,
                'card_issuer_country'       => $request->card_issuer_country,
                'card_issuer_country_code'  => $request->card_issuer_country_code,
                'store_id'                  => $request->store_id
            ]);

            $this->writeLog(" sslcommerz_ipn INSERT status >>> :". $ipn_is_insert ."\n");

            return json_encode([
                'status' => true,
                'data'   => $ipn_is_insert,
                'message'=> ""
            ]);     
        }
        else
        {
            return json_encode([
                'status' => false,
                'data'   => [],
                'message'=> ""
            ]);
        }
    }

    /**
     * IPN Payment process
     *
     * Get sslcommerz_ipn table data which status = 0
     * Process a curl request and if response success than change status 0 to 1
     * Process payment processing others task
     */
    public function ipn_payment_process( $request )
    {
        $status_code = 0;
        $feed        = '';
        $order_data  = [];

        # Write log
        $this->writeLog(" IPN payment process >> : Fire \n");
        $this->writeLog(' CURRENT PAGE SLUG >>> : '.$request->path()."\n");
        # END

        $sslcommerz_ipn_data = SslComIpn::where('trx_status', 'VALID')->where('validation_call_status', 1)->where('status', 0)->first();

        if( !empty($sslcommerz_ipn_data) && count($sslcommerz_ipn_data) )
        {
            $ipn_data       = $sslcommerz_ipn_data;

            # Update sslcommerz_ipn column value
            SslComIpn::where('id', $ipn_data->id)->update(['status' => 1]);

            $tran_id        = $ipn_data->tran_id;
            $val_id         = $ipn_data->val_id;
            
            $paidAmount     = $ipn_data->amount;

            $order_data     = $this->do_service_call( $sslcommerz_ipn_data );
            $feed           = 'IPN queue has been processed successfully.';
            $status_code    = 1;
            
        }
        else
        {
            # Write log
            # POST verify issue
            $this->writeLog(" Status >> : Failed \n");
            $this->writeLog(" Message >> : NO IPN data that have status 0 \n");
            $feed = 'No IPN queue data to be process';
        }

        return json_encode([
            'status_code'   => $status_code,
            'feedback'      => $feed,
            'order'         => $order_data
        ]);
    }

    /**
     * Do service call
     * 
     * @return true or false
     */
    private function do_service_call( $sslcommerz_ipn_data )
    {
        $feedback = Order::where('voucher_number', $sslcommerz_ipn_data->tran_id)->update(['payment_type' => $sslcommerz_ipn_data->card_type, 'payment_status' => $sslcommerz_ipn_data->trx_status]);

        if($sslcommerz_ipn_data->currency == "USD")
        {
            Order::where('voucher_number', $sslcommerz_ipn_data->tran_id)->update([
                'BDT_amount' => $sslcommerz_ipn_data->amount
            ]);
        }

        # Write log
        $this->writeLog(" Update Order Table >>> : Done\n");
        
        $order = Order::where('voucher_number', '=', $sslcommerz_ipn_data->tran_id)->first();

        /**
        * add payment on payment_details table
        */
        $payment_details                  = new PaymentDetails;
        $payment_details->order_id        = $order->id;
        $payment_details->currency        = $sslcommerz_ipn_data->currency;
        $payment_details->paid_amount     = ($sslcommerz_ipn_data->currency == "USD") ? $sslcommerz_ipn_data->amount_with_bank_fee : $sslcommerz_ipn_data->amount;
        $payment_details->BDT_amount      = $sslcommerz_ipn_data->amount;
        $payment_details->total_paid_with_bank_fee= $sslcommerz_ipn_data->amount_with_bank_fee;
        $payment_details->payment_method  = $sslcommerz_ipn_data->card_type;
        $payment_details->created_by      = 0;
        $payment_details->comments        = $sslcommerz_ipn_data->status_message;
        $payment_details->save();

        # Write log
        $this->writeLog(" Update Payment Details Table >>> : Done\n");
        $this->writeLog(" Last update ID >>> : ". $payment_details->id ."\n");

        return $order;
    }

      /**
       * Render payment page
       * @param $payment_url
       * @param $data
       * @param bool $live
       * @return mixed
       * @throws \Satouch\SSLCommerzIPN\Exceptions\CheckoutException
       */
      public function payment_checkout($payment_url, $data, $live = true)
      {
          return view('SSLCOMZIPN::checkout-redirect', [
              'redirect_url' => $this->checkout->handle($payment_url, $data, $live)
          ]);
      }
}