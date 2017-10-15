<?php
namespace Satouch\SSLCommerzIPN\Traits;

use Satouch\SSLCommerzIPN\Models\SslComIpn;
use Satouch\SSLCommerzIPN\Models\Order;
use Satouch\SSLCommerzIPN\Models\PaymentDetails;
use Satouch\SSLCommerzIPN\Models\OrderLog;

trait SslCommerzValidate
{
    private $order          = [];
    private $payment_status = '';

    public function call_validate_URL( $storeID, $storePassword, $payment_validate_api_url, $request )
    {
        # TRANSACTION INFO
        $status         = $request->status;	
        $tran_date      = $request->tran_date;
        $tran_id        = $request->tran_id;
        $val_id         = $request->val_id;
        $totalChargedWithFee = $request->amount; // total chargeable amount with bank fee
        $validateAmount = $request->currency_amount; // after curl validate amount 
        $card_type      = $request->card_type;
        $risk_title     = $request->risk_title;
        $risk_level     = $request->risk_level;

        # site_store_info
        $store_id       = ($storeID);
        $store_passwd   = $storePassword;

        # Write log
        $this->writeLog(" SSL COMMERZ VALIDATE cURL Execution >> : Processing... \n");

        # Call payment validation URL
        $requested_url = ($payment_validate_api_url."?val_id=".$val_id."&store_id=".$store_id."&store_passwd=".$store_passwd."&v=3&format=json");

        # Write Log
        $this->writeLog(" Call Validate URL >> : ". str_replace($store_passwd, '***', $requested_url) ."\n");

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $requested_url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($handle);
        $code   = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        # Write Log
        $this->writeLog(" cURL response >>> : ".json_encode($result)."\n");
        $this->writeLog(" CURL error no >>> : ".json_encode( curl_errno($handle) )."\n");

        if( ($code == 200) && (curl_errno($handle) == 0) )
        {
            # Write Log
            $this->writeLog(" Status >>> : success \n");
            $this->writeLog(" Message >>> : cURL executed. \n");

            # Return curl process
            $feedback =  $this->curl_validate_data_process($result);
            $this->writeLog(" curl_validate_data_process feedback >>> :". json_encode($feedback) ."\n");
            return $feedback;
        }
        else
        {
            return false;
        }      
    }

    private function get_total_due_by_order_number($order_number)
    {
        try{
            $order      = Order::where('voucher_number', '=', $order_number)->firstOrFail();
            $total_pay  = PaymentDetails::where('order_id', '=', $order->id)->sum('paid_amount');
            $due        = ($order->total_cost - floatval($total_pay));
            return ( $due === 0.0 ) ? 0 : $due;
        }
        catch(Exception $e)
        {
            $this->writeLog(" Exception >>> :". json_encode($e) ."\n");
        }
    }

    private function curl_validate_data_process($request)
    {
        $this->writeLog(" curl_validate_data_process requests >>> :". json_encode($request) ."\n");
	    
        $request = json_decode($request);	
        
        # TRANSACTION INFO
        $status         = $request->status;	
        $tran_date      = $request->tran_date;
        $tran_id        = $request->tran_id;
        $val_id         = $request->val_id;
        $totalChargedWithFee = $request->amount; // total chargeable amount with bank fee
        $validateAmount = $request->currency_amount; // after curl validate amount 
        $card_type      = $request->card_type;
        $risk_title     = $request->risk_title;
        $risk_level     = $request->risk_level;
        
        # API AUTHENTICATION
        $APIConnect     = $request->APIConnect;
        if( $APIConnect == "DONE" )
        {
            $status_msg = '';
            # Write log
            $this->writeLog(" APIConnect >>> : ". $APIConnect ."\n");
            $this->writeLog(" APIConnect Status >>> : ". $status ."\n");

            if( $status == "VALID" )
            {
                # Write log
                $this->writeLog(" Payment status >>> : ". $status ."\n");

                $status_msg = "Transaction ID #".$tran_id." payment successfull, Please check your email.";
                $this->payment_status = 'VALID';
                if($risk_level == '1') 
                {
                    $status_msg = "Payment is Risky. Transaction ID #".$tran_id." has been charged successfully. Please contact our support center > support@sslwireless.com.";
                    
                    # Write log
                    $this->writeLog(" Risk Level >>> : ". $risk_level ."\n");
                    $this->writeLog(" Risk Title >>> : ". $risk_title ."\n");
                    $this->payment_status = 'CANCELLED';

                    # Update payment status to order table.
                    Order::where('voucher_number', $tran_id)->update(['payment_status' => 'CANCELLED']);
                    return false;
                }
                else
                {
                    # Write log
                    $this->writeLog(" Message >>> : ". $status_msg ."\n");
                    $this->writeLog(" Risk Level >>> : ". $risk_level ."\n");
                    $this->writeLog(" Risk Title >>> : ". $risk_title ."\n");

                    # checking post amount === validate amount === due amount
                    $due = $this->get_total_due_by_order_number($tran_id);

                    if( (floatval($validateAmount) != $due) ) 
                    {
                        $this->payment_status = 'CANCELLED';
                        # Write log
                        $this->writeLog(" DUE CHECKING MESSAGE >>> : DUE and validateAmount not matched.\n");
                        $status_msg   = 'Transaction ID #'.$tran_id.' has been on hold, please contact our support center > support@sslwireless.com.';
                        
                        # Update payment status to order table.
                        Order::where('voucher_number', $tran_id)->update(['payment_status' => 'CANCELLED']);

                        return false;  
                    }
                    // if( ($request->currency_type != "BDT") ) 
                    // {
                    //     $this->payment_status = 'CANCELLED';
                    //     # Write log
                    //     $this->writeLog(" Currency Type MESSAGE >>> : Currency Not matched.\n");
                    //     $status_msg   = 'Transaction ID #'.$tran_id.' has been on hold, please contact our support center > support@sslwireless.com.';
                        
                    //     # Update payment status to order table.
                    //     return false;   
                    // }
                    # END 

                    /**
                    * update order table
                    * order payment_type
                    * order payment_status
                    */
                    if( $due === floatval($validateAmount) )
                    {
                        $this->writeLog(" DUE CHECKING MESSAGE >>> : Due amount and paid amount are same and matched \n");

                        $order = Order::where('voucher_number', '=', $tran_id)->first();
                        OrderLog::insert([
                            'order_id'      => $order->id,
                            'payment_log'   => json_encode(array('validate_api_post_data' => $request))
                        ]);
                        # Write log
                        $this->writeLog(" Order log table update >>> : Done \n");
                        return true;
                    }
                    else
                    {
                        $status_msg   = 'Transaction ID #'.$tran_id.' has been on hold, you are paying less than due.';
                        $this->payment_status = 'CANCELLED';

                        # Write log
                        $this->writeLog(" Message >>> : ". $status_msg ."\n");

                        # Update payment status to order table.
                        Order::where('voucher_number', $tran_id)->update(['payment_status' => 'CANCELLED']);
                        return false;
                    }
                    return ($this->payment_status == 'VALID') ? true : false;
                }

                return ($this->payment_status == 'VALID') ? true : false;
            }
            else if( $status == "VALIDATED" )
            {
                $this->payment_status = 'CANCELLED';
                # Write log
                $this->writeLog(" Payment status >>> : ". $status ."\n");

                $status_msg = "Transaction ID #".$tran_id." already validated by you.";
                if($risk_level == '1') 
                {
                    $status_msg = "Payment is Risky. Transaction ID #".$tran_id." already validated by you. Please contact our support center > support@sslwireless.com.";
                    
                    # Write log
                    $this->writeLog(" Risk Level >>> : ". $risk_level ."\n");
                    $this->writeLog(" Risk Title >>> : ". $risk_title ."\n");
                    return false;
                }
                else
                {
                    # Write log
                    $this->writeLog(" Message >>> : ". $status_msg ."\n");
                    $this->writeLog(" Risk Level >>> : ". $risk_level ."\n");
                    $this->writeLog(" Risk Title >>> : ". $risk_title ."\n");
                }
                # Update payment status to order table.
                Order::where('voucher_number', $tran_id)->update(['payment_status' => 'VALIDATED']);

                return false;
            }
            else
            {   
                $status_msg = "Invalid payment";
                return false;
            }

            return false;
        }
        else
        {
            $this->payment_status = 'CANCELLED';
            # Write log
            $this->writeLog(" APIConnect >>> : ". $APIConnect ."\n");
            $status_msg = "API Conntection failed.";

            # Update payment status to order table.
            Order::where('voucher_number', $tran_id)->update(['payment_status' => 'CANCELLED']);

            return false;
        }  
    }
}
