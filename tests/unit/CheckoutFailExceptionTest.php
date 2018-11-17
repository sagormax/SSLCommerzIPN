<?php

class CheckoutFailExceptionTest extends \PHPUnit_Framework_TestCase
{
      public $checkoutExceptionHappen;

      /** @test */
      public function checkoutException()
      {
            try{
                  (new \Satouch\SSLCommerzIPN\Payments\Checkout())->handle(
                      'https://sandbox.sslcommerz.com/gwprocess/v3/api.php', // SSLCOMMERZ payment api url
                      $this->getData(), // post data
                      false // is production(live)
                  );
            }
            catch (\Satouch\SSLCommerzIPN\Exceptions\CheckoutException $checkoutException){
                  $this->checkoutExceptionHappen = true;
            }

            $this->assertEquals(true, $this->checkoutExceptionHappen);
      }

      /**
       * Get dummy post data
       * @return array
       */
      private function getData()
      {
            $post_data = array();
            $post_data['store_id'] = "test5be7d3b945c8c";
            $post_data['store_passwd'] = "INVALID_PASSWORD";
            //$post_data['total_amount'] = "103";
            $post_data['currency'] = "BDT";
            $post_data['tran_id'] = "SSLCZ_TEST_".uniqid();
            $post_data['success_url'] = "http://localhost/SSLCommerzIPN/success.php";
            $post_data['fail_url'] = "http://localhost/SSLCommerzIPN/fail.php";
            $post_data['cancel_url'] = "http://localhost/SSLCommerzIPN/cancel.php";

            return $post_data;
      }
}