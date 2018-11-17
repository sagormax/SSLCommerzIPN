<?php

class CheckoutTest extends \PHPUnit_Framework_TestCase
{
      /** @test */
      public function checkout()
      {
            $payment_url = (new \Satouch\SSLCommerzIPN\Payments\Checkout())->handle(
                'https://sandbox.sslcommerz.com/gwprocess/v3/api.php', // SSLCOMMERZ payment api url
                $this->getData(), // post data
                false // is production(live)
            );

            $this->assertNotEmpty($payment_url, '');
      }

      /**
       * Get dummy post data
       * @return array
       */
      private function getData()
      {
            $post_data = array();
            $post_data['store_id'] = "test5be7d3b945c8c";
            $post_data['store_passwd'] = "test5be7d3b945c8c@ssl";
            $post_data['total_amount'] = "103";
            $post_data['currency'] = "BDT";
            $post_data['tran_id'] = "SSLCZ_TEST_".uniqid();
            $post_data['success_url'] = "http://localhost/SSLCommerzIPN/success.php";
            $post_data['fail_url'] = "http://localhost/SSLCommerzIPN/fail.php";
            $post_data['cancel_url'] = "http://localhost/SSLCommerzIPN/cancel.php";

            return $post_data;
      }
}