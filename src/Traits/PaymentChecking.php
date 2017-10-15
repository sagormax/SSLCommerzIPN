<?php
namespace SSLWIRELESS\SSLCommerzIPN\Traits;

// use SSLWIRELESS\SSLCommerzIPN\Traits\WriteLogTrait;
use SSLWIRELESS\SSLCommerzIPN\Models\SslComIpn;
use SSLWIRELESS\SSLCommerzIPN\Models\Order;
use SSLWIRELESS\SSLCommerzIPN\Models\PaymentDetails;
use SSLWIRELESS\SSLCommerzIPN\Models\OrderLog;

trait PaymentChecking
{
    # Fixed duplicate dependency writeLog method on trait.
    // use WriteLogTrait{
    //     writeLog as protected make_log;
    // }
    private $order = [];

    public function do_payment_checking( $paidAmount, $result )
    {
        # TRANSACTION INFO
        $status         = $result->status;	
        $tran_date      = $result->tran_date;
        $tran_id        = $result->tran_id;
        $val_id         = $result->val_id;
        $totalChargedWithFee = $result->amount; // total chargeable amount with bank fee
        $validateAmount = $result->currency_amount; // after curl validate amount 
        $card_type      = $result->card_type;
        $risk_title     = $result->risk_title;
        $risk_level     = $result->risk_level;

        # API AUTHENTICATION
        $APIConnect     = $result->APIConnect;
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
                }
                else
                {
                    # Write log
                    $this->writeLog(" Message >>> : ". $status_msg ."\n");
                    $this->writeLog(" Risk Level >>> : ". $risk_level ."\n");
                    $this->writeLog(" Risk Title >>> : ". $risk_title ."\n");

                    # checking post amount === validate amount === due amount
                    $due = $this->get_total_due_by_order_number($tran_id);

                    if( (floatval($paidAmount) != floatval($validateAmount)) ) 
                    {
                        $this->payment_status = 'CANCELLED';
                        # Write log
                        $this->writeLog(" DUE CHECKING MESSAGE >>> : paidAmount and validateAmount not matched.\n");
                        $status_msg   = 'Transaction ID #'.$tran_id.' has been on hold, please contact our support center > support@sslwireless.com.';
                        
                        # Update payment status to order table.
                        Order::where('voucher_number', $tran_id)->update(['payment_status' => 'CANCELLED']);

                        return json_encode([
                            'status'        => $status,
                            'status_msg'    => $status_msg,
                        ]);  
                    }
                    if( (floatval($validateAmount) != $due) ) 
                    {
                        $this->payment_status = 'CANCELLED';
                        # Write log
                        $this->writeLog(" DUE CHECKING MESSAGE >>> : validateAmount and dueAmount not matched.\n");
                        $status_msg   = 'Transaction ID #'.$tran_id.' has been on hold, please contact our support center > support@sslwireless.com.';
                        
                        # Update payment status to order table.
                        Order::where('voucher_number', $tran_id)->update(['payment_status' => 'CANCELLED']);

                        return json_encode([
                            'status'        => $status,
                            'status_msg'    => $status_msg,
                        ]);   
                    }
                    if( (floatval($paidAmount) != $due) ) 
                    {
                        $this->payment_status = 'CANCELLED';
                        # Write log
                        $this->writeLog(" DUE CHECKING MESSAGE >>> : paidAmount and dueAmount not matched.\n");
                        $status_msg   = 'Transaction ID #'.$tran_id.' has been on hold, please contact our support center > support@sslwireless.com.';
                        
                        # Update payment status to order table.
                        Order::where('voucher_number', $tran_id)->update(['payment_status' => 'CANCELLED']);

                        return json_encode([
                            'status'        => $status,
                            'status_msg'    => $status_msg,
                        ]);
                    }
                    # END 

                    /**
                    * update order table
                    * order payment_type
                    * order payment_status
                    */
                    if( $due === floatval($validateAmount) )
                    {
                        $this->writeLog(" DUE CHECKING MESSAGE >>> : Due amount and paid amount are same and matched \n");

                        $this->order = $this->do_update_payment_by_transaction_id( $tran_id, $card_type, $status, $status_msg, $validateAmount, $totalChargedWithFee );
                        
                        $order = Order::where('voucher_number', '=', $tran_id)->first();
                        OrderLog::insert([
                            'order_id'      => $order->id,
                            'payment_log'   => json_encode(array('validate_api_post_data' => $result))
                        ]);
                        # Write log
                        $this->writeLog(" Order log table update >>> : Done \n");
                    }
                    else
                    {
                        $status_msg   = 'Transaction ID #'.$tran_id.' has been on hold, you are paying less than due.';
                        
                        $this->payment_status = 'CANCELLED';

                        # Write log
                        $this->writeLog(" Message >>> : ". $status_msg ."\n");

                        # Update payment status to order table.
                        Order::where('voucher_number', $tran_id)->update(['payment_status' => 'CANCELLED']);

                        return json_encode([
                            'status'        => $status,
                            'status_msg'    => $status_msg,
                        ]);
                    }
                }

                return json_encode([
                    'status'        => $status,
                    'status_msg'    => $status_msg,
                ]);
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

                return json_encode([
                    'status'        => $status,
                    'status_msg'    => $status_msg,
                ]);
            }
            else
            {   
                $status_msg = "Invalid payment";
            }

            return json_encode([
                'status'        => $status,
                'status_msg'    => $status_msg,
                'order'         => $this->order
            ]);
        }
        else
        {
            $this->payment_status = 'CANCELLED';
            # Write log
            $this->writeLog(" APIConnect >>> : ". $APIConnect ."\n");
            $status_msg = "API Conntection failed.";

            # Update payment status to order table.
            Order::where('voucher_number', $tran_id)->update(['payment_status' => 'CANCELLED']);

            return json_encode([
                'status'        => $status,
                'status_msg'    => $status_msg,
            ]);
        }        
    }

    /**
     * Do update payment by transaction ID
     */
    private function do_update_payment_by_transaction_id( $tran_id, $card_type, $status, $status_msg, $validateAmount, $totalChargedWithFee = '' )
    {
        $feedback = Order::where('voucher_number', $tran_id)->update(['payment_type' => $card_type, 'payment_status' => $status]);

        # Write log
        $this->writeLog(" Update Order Table >>> : Done\n");
        
        $order = Order::where('voucher_number', '=', $tran_id)->first();

        /**
        * add payment on payment_details table
        */
        $payment_details                  = new PaymentDetails;
        $payment_details->order_id        = $order->id;
        $payment_details->paid_amount     = $validateAmount;
        $payment_details->total_paid_with_bank_fee= $totalChargedWithFee;
        $payment_details->payment_method  = $card_type;
        $payment_details->created_by      = 0;
        $payment_details->comments        = $status_msg;
        $payment_details->save();

        # Write log
        $this->writeLog(" Update Payment Details Table >>> : Done\n");
        $this->writeLog(" Last update ID >>> : ". $payment_details->id ."\n");

        return $order;
    }


    private function get_total_due_by_order_number($order_number)
    {
        $order      = Order::where('voucher_number', '=', $order_number)->firstOrFail();
        $total_pay  = PaymentDetails::where('order_id', '=', $order->id)->sum('paid_amount');
        $due        = ($order->total_cost - floatval($total_pay));
        return ( $due === 0.0 ) ? 0 : $due;
    }
}
