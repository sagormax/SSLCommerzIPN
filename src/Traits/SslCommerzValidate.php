<?php
namespace Satouch\SSLCommerzIPN\Traits;

use Satouch\SSLCommerzIPN\Exceptions\RiskPaymentException;
use Satouch\SSLCommerzIPN\Exceptions\ValidationException;
use Satouch\SSLCommerzIPN\Exceptions\PaymentAlreadyValidatedException;

trait SslCommerzValidate
{
      use HTTPTrait;

      private $payment_status = '';
      private $status_msg     = '';
      private $validation     = false;

      /**
       * @param $storeID
       * @param $storePassword
       * @param $val_id
       * @param $payment_validate_api_url
       * @return array
       */
      public function call_validate_URL( $storeID, $storePassword, $val_id, $payment_validate_api_url )
      {
            # Write log
            $this->writeLog(" SSLCOMMERZ : VALIDATE cURL Execution >> : Processing... \n");

            # Call payment validation URL
            $requested_url = ($payment_validate_api_url."?val_id=".$val_id."&store_id=".$storeID."&store_passwd=".$storePassword."&v=3&format=json");

            $this->writeLog(" SSLCOMMERZ : Call Validate URL >> : ". str_replace($storePassword, '***', $requested_url) ."\n");

            $validation_callback = $this->httpCall('GET', $requested_url, [], false);

            if( $validation_callback ) {
                  # Write Log
                  $this->writeLog(" Status >>> : success \n");
                  $this->writeLog(" Message >>> : cURL executed. \n");

                  # Return curl process
                  $this->curl_validate_data_process($validation_callback);
            }
            else {
                  $this->validation = false;
            }

            return [$this->validation, $this->payment_status, $this->status_msg];
      }

      private function curl_validate_data_process($request)
      {
            $this->writeLog(" curl_validate_data_process requests >>> :". json_encode($request) ."\n");

            $request = json_decode($request);

            # TRANSACTION INFO
            $status         = $request->status;
            $tran_id        = $request->tran_id;
            $risk_title     = $request->risk_title;
            $risk_level     = $request->risk_level;

            # API AUTHENTICATION
            $APIConnect     = $request->APIConnect;
            if( $APIConnect == "DONE" ){
                  # Write log
                  $this->writeLog(" APIConnect >>> : ". $APIConnect ."\n");
                  $this->writeLog(" APIConnect Status >>> : ". $status ."\n");

                  if( $status == "VALID" ) {
                        $this->status_msg = "Transaction ID #".$tran_id." payment successfull, Please check your email.";
                        # Write log
                        $this->writeLog(" Payment status >>> : ". $status ."\n");
                        $this->payment_status = 'VALID';

                        if($risk_level == '1') {
                              $this->status_msg = "Payment is Risky. Transaction ID #".$tran_id." has been charged successfully. Please contact our support center > support@sslwireless.com.";

                              # Write log
                              $this->writeLog(" Risk Level >>> : ". $risk_level ."\n");
                              $this->writeLog(" Risk Title >>> : ". $risk_title ."\n");
                              $this->payment_status = 'CANCELLED';

                              throw new RiskPaymentException($this->status_msg);
                        }

                        # Write log
                        $this->writeLog(" Risk Level >>> : ". $risk_level ."\n");
                        $this->writeLog(" Risk Title >>> : ". $risk_title ."\n");
                        $this->validation = true;
                  }
                  else if( $status == "VALIDATED" ) {
                        $this->payment_status = 'VALIDATED';

                        # Write log
                        $this->writeLog(" Payment status >>> : ". $status ."\n");
                        $this->status_msg = "Transaction ID #".$tran_id." already validated.";

                        if($risk_level == '1') {
                              $this->status_msg = "Payment is Risky. Transaction ID #".$tran_id." already validated. Please contact our support center > support@sslwireless.com.";
                              throw new RiskPaymentException($this->status_msg);
                        }

                        $this->writeLog(" Message >>> : ". $this->status_msg ."\n");
                        $this->writeLog(" Risk Level >>> : ". $risk_level ."\n");
                        $this->writeLog(" Risk Title >>> : ". $risk_title ."\n");

                        throw new PaymentAlreadyValidatedException($this->status_msg);
                  }
            }
            else {
                  $this->payment_status = 'FAILED';

                  # Write log
                  $this->writeLog(" APIConnect >>> : ". $APIConnect ."\n");
                  $this->status_msg = "API Conntection failed.";

                  throw new ValidationException($this->status_msg);
            }
      }
}
