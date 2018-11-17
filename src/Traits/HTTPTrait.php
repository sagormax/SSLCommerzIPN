<?php
namespace Satouch\SSLCommerzIPN\Traits;

use Satouch\SSLCommerzIPN\Exceptions\CurlHTTPException;

trait HTTPTrait
{
      protected $timeout = 40.0;
      protected $production;

      /**
       * HTTP call
       *
       * @param [type] $type
       * @param [type] $base_uri
       * @param Array $parm
       * @param boolean $live
       * @param string $timeout
       * @return mixed
       */
      public function httpCall($type, $base_uri, $parm, $live, $timeout = '60.0')
      {
            $this->timeout    = $timeout;
            $this->production = $live;

            try {
                  if( $type == "GET" ){
                        return $this->cURLTo(
                            $base_uri."?".http_build_query($parm)
                        );
                  }
                  else{
                        return $this->cURLTo(
                            $base_uri,
                            true,
                            $parm
                        );
                  }
            } 
            catch (\Exception $e) {
                  throw new CurlHTTPException('HTTP request exception has been made.');
            }
      }

      private function cURLTo($url, $isPOST = false, $post_data = [])
      {
            $handle = curl_init();
            curl_setopt($handle, CURLOPT_URL, $url);
            curl_setopt($handle, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, $this->timeout);
            curl_setopt($handle, CURLOPT_POST, $isPOST );
            curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, $this->production); # KEEP IT FALSE IF YOU RUN FROM LOCAL PC

            $content = curl_exec($handle);

            $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

            if($code == 200 && !( curl_errno($handle))) {
                  curl_close( $handle);
                  return $content;
            } else {
                  curl_close( $handle);

                  throw new CurlHTTPException('cURL error : '.curl_errno($handle));
            }
      }
}