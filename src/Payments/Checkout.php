<?php
namespace Satouch\SSLCommerzIPN\Payments;

use Satouch\SSLCommerzIPN\Traits\HTTPTrait;
use Satouch\SSLCommerzIPN\Traits\WriteLogTrait;
use Satouch\SSLCommerzIPN\Exceptions\CheckoutException;

class Checkout
{
      use HTTPTrait, WriteLogTrait;

      private $redirectURL;

      public function handle($payment_url, $data, $live = true)
      {
            $response = $this->httpCall('POST', $payment_url, $data, $live);
            $sslcz    = json_decode($response, true);

            if( isset($sslcz['status']) && ($sslcz['status'] == 'SUCCESS') )
            {
                  if(!isset($sslcz['GatewayPageURL'])){
                        throw new CheckoutException('SSLCOMMERZ invalid GatewayPageURL');
                  }

                  $this->redirectURL = $sslcz['GatewayPageURL'];

                  if( isset($data['multi_card_name']) )
                  {
                        $this->redirectURL = $sslcz['redirectGatewayURL'].$data['multi_card_name'];
                  }

                  return $this->redirectURL;
            }
            else
            {
                  throw new CheckoutException('SSLCOMMERZ checkout error: '.$response);
            }
      }
}