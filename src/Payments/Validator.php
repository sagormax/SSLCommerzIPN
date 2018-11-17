<?php
namespace Satouch\SSLCommerzIPN\Payments;

use Satouch\SSLCommerzIPN\Traits\WriteLogTrait;
use Satouch\SSLCommerzIPN\Traits\PaymentHashValidation;
use Satouch\SSLCommerzIPN\Contracts\ValidationContract;

class Validator implements ValidationContract
{
      use WriteLogTrait, PaymentHashValidation;

      private $feedback = false;

      public function validate($storePassword)
      {
            if( isset($_POST['verify_sign']) && isset($_POST['verify_key']) )
            {
                  $verify_sign    = $_POST['verify_sign'];
                  $verify_key     = $_POST['verify_key'];

                  $this->writeLog("SSLCOMMERZ : hash_varify >>> : Processing... \n");

                  # Hash validation
                  $this->feedback = $this->_SSLCOMMERZ_hash_verify( $storePassword, $verify_sign, $verify_key );

                  $this->writeLog("SSLCOMMERZ : hash_varify status >>> :". $this->feedback ."\n");
            }
            else
            {
                  $this->writeLog("SSLCOMMERZ : verify_sign and verify_key >>> : Not SET \n");
            }

            return $this->feedback;
      }
}