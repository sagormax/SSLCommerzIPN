<?php
namespace Satouch\SSLCommerzIPN\Contracts;


interface ValidationContract
{
      public function hash_verify($storePassword);
      public function validate($store_id, $store_password, $payment_validate_api_url);
}