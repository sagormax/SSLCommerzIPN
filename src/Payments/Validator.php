<?php
namespace Satouch\SSLCommerzIPN\Payments;

use Satouch\SSLCommerzIPN\Traits\WriteLogTrait;
use Satouch\SSLCommerzIPN\Traits\SslCommerzValidate;
use Satouch\SSLCommerzIPN\Traits\PaymentHashValidation;
use Satouch\SSLCommerzIPN\Contracts\ValidationContract;
use Satouch\SSLCommerzIPN\Exceptions\ValidationException;

class Validator implements ValidationContract
{
      use WriteLogTrait, PaymentHashValidation, SslCommerzValidate;

      public $sslcommerz_validation;
      public $sslcommerz_payment_status;
      public $sslcommerz_payment_response;
      private $feedback = false;

      /**
       * Validate SSLCOMMERZ Data
       * @param $store_id
       * @param $store_password
       * @param $val_id
       * @param string $payment_validate_api_url
       * @throws ValidationException
       */
      public function validate($store_id, $store_password, $payment_validate_api_url)
      {
            $listData = $this->hash_verify($store_password)
                              ->sslcommerz_validate($store_id, $store_password, $_POST['val_id'], $payment_validate_api_url);

            list($this->sslcommerz_validation,$this->sslcommerz_payment_status,$this->sslcommerz_payment_response) = $listData;

            return $this;
      }

      /**
       * Verify POST data
       * @param $storePassword
       * @return $this
       * @throws ValidationException
       */
      public function hash_verify($storePassword)
      {
            if( !isset($_POST['val_id']) ){
                  throw new ValidationException('val_id key is missing.', 422);
            }

            if( isset($_POST['verify_sign']) && isset($_POST['verify_key']) ) {
                  $this->writeLog("SSLCOMMERZ : hash_varify >>> : Processing... \n");

                  # Hash validation
                  $this->_SSLCOMMERZ_hash_verify( $storePassword, $_POST['verify_sign'], $_POST['verify_key'] );
            }
            else {
                  $this->writeLog("SSLCOMMERZ : verify_sign and verify_key >>> : Not SET \n");

                  throw new ValidationException('verify sign & verify key is missing.', 422);
            }

            return $this;
      }

      /**
       * SSLCOMMERZ validation
       * @param $store_id
       * @param $store_password
       * @param $val_id
       * @param $payment_validate_api_url
       * @return bool
       * @throws ValidationException
       */
      private function sslcommerz_validate($store_id, $store_password, $val_id, $payment_validate_api_url)
      {
            if(empty($payment_validate_api_url)){
                  throw new ValidationException("Payment validate API URL is empty", 422);
            }

            return $this->call_validate_URL($store_id, $store_password, $val_id, $payment_validate_api_url);
      }

      /**
       * Get validation status
       * @return mixed
       */
      public function validation_status()
      {
            return $this->sslcommerz_validation;
      }

      /**
       * Get payment status
       * @return mixed
       */
      public function payment_status()
      {
            return $this->sslcommerz_payment_status;
      }

      /**
       * Get payment response details
       * @return mixed
       */
      public function payment_response()
      {
            return $this->sslcommerz_payment_response;
      }
}