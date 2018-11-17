# SSLCOMMERZ Payment Package


## Install

``` bash
$ composer require "satouch/sslcommerz-ipn":"dev-master"
```

## PHP
##### 1. Checkout Process : 
``` bash
require_once 'vendor/autoload.php';

$post_data = array();
$post_data['store_id'] = "test5be7d3b945c8c";
$post_data['store_passwd'] = "test5be7d3b945c8c@ssl";
$post_data['total_amount'] = "103";
$post_data['currency'] = "BDT";
$post_data['tran_id'] = "SSLCZ_TEST_".uniqid();
$post_data['success_url'] = "http://localhost/SSLCommerzIPN/success.php";
$post_data['fail_url'] = "http://localhost/SSLCommerzIPN/fail.php";
$post_data['cancel_url'] = "http://localhost/SSLCommerzIPN/cancel.php";

# checkout
try{
      $payment_url = (new \Satouch\SSLCommerzIPN\Payments\Checkout())->handle(
            'https://sandbox.sslcommerz.com/gwprocess/v3/api.php', // SSLCOMMERZ payment api url
            $post_data, // post data
            false // is production(live)
      );
        
      # Redirect URL to SSLCOMMERZ payment page
      header("location:".$payment_url);
}
catch (\Exception $e){
      die($e->getMessage());
      // do action
}
# END
```
##### 2. success/fail/cancel page handle : 
``` bash

try{
      $payment = (new \Satouch\SSLCommerzIPN\Payments\Validator())->validate(
          'test5be7d3b945c8c', // storeID
          'test5be7d3b945c8c@ssl', // storePassword
          'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php' // payment validation URL
      );
      // do action

      var_dump($payment->validation_status())."<br>"; // TRUE/FALSE
      var_dump($payment->payment_status())."<br>"; // VALID/VALIDATED/FAILED
      var_dump($payment->payment_response())."<br>"; // Response details
}
catch(\Exception $e){
      die($e->getMessage());
      // do action
}
```

##### Exceptions:

``` bash
\Satouch\SSLCommerzIPN\Exceptions\ValidationException
\Satouch\SSLCommerzIPN\Exceptions\CheckoutException
\Satouch\SSLCommerzIPN\Exceptions\CurlHTTPException
\Satouch\SSLCommerzIPN\Exceptions\RiskPaymentException
\Satouch\SSLCommerzIPN\Exceptions\PaymentAlreadyValidatedException
```

<!--
## Laravel 5

``` bash
'providers' => [
    ...
    Satouch\SSLCommerzIPN\SSLCommerzIPNServiceProvider::class,
],
```

``` bash
'aliases' => [
    ...
    'PaymentValidation' => Satouch\SSLCommerzIPN\Facades\PaymentValidationFacades::class,
],
```

``` bash
$ php artisan vendor:publish
$ php artisan migrate
```

## Use

``` bash
use PaymentValidation;

# Hash validate
# @return true/false

PaymentValidation::validate($store_id, $store_passwd, $request);


# server to server IPN hit and validate callback to sslcommerz
# @return a json object with status;
# @note if tran_id/voucher_number not matched return load 404 page

PaymentValidation::sslcommerz_ipn_data_insert( $store_id, $store_passwd, $validate_url, $request )


# payment processing happen on cron job
# @return a json data with status_code, feedback, order collection

PaymentValidation::ipn_payment_process( $request );


# payment checkout page
# @return if false return json_object or view sslcommerz checkout page

-->
``` bash
# /* PHP sample post data */
# $post_data = array();
# $post_data['store_id'] = "testbox";
# $post_data['store_passwd'] = "qwerty";
# $post_data['total_amount'] = "103";
# $post_data['currency'] = "BDT";
# $post_data['tran_id'] = "SSLCZ_TEST_".uniqid();
# $post_data['success_url'] = "http://localhost/new_sslcz_gw/success.php";
# $post_data['fail_url'] = "http://localhost/new_sslcz_gw/fail.php";
# $post_data['cancel_url'] = "http://localhost/new_sslcz_gw/cancel.php";

# # EMI INFO
# $post_data['emi_option'] = "1";
# $post_data['emi_max_inst_option'] = "9";
# $post_data['emi_selected_inst'] = "9";

# # CUSTOMER INFORMATION
# $post_data['cus_name'] = "Test Customer";
# $post_data['cus_email'] = "test@test.com";
# $post_data['cus_add1'] = "Dhaka";
# $post_data['cus_add2'] = "Dhaka";
# $post_data['cus_city'] = "Dhaka";
# $post_data['cus_state'] = "Dhaka";
# $post_data['cus_postcode'] = "1000";
# $post_data['cus_country'] = "Bangladesh";
# $post_data['cus_phone'] = "01711111111";
# $post_data['cus_fax'] = "01711111111";

# # SHIPMENT INFORMATION
# $post_data['ship_name'] = "Store Test";
# $post_data['ship_add1 '] = "Dhaka";
# $post_data['ship_add2'] = "Dhaka";
# $post_data['ship_city'] = "Dhaka";
# $post_data['ship_state'] = "Dhaka";
# $post_data['ship_postcode'] = "1000";
# $post_data['ship_country'] = "Bangladesh";

# # OPTIONAL PARAMETERS
# $post_data['value_a'] = "ref001";
# $post_data['value_b '] = "ref002";
# $post_data['value_c'] = "ref003";
# $post_data['value_d'] = "ref004";

# # CART PARAMETERS
# $post_data['cart'] = json_encode(array(
#     array("product"=>"DHK TO BRS AC A1","amount"=>"200.00"),
#     array("product"=>"DHK TO BRS AC A2","amount"=>"200.00"),
#     array("product"=>"DHK TO BRS AC A3","amount"=>"200.00"),
#     array("product"=>"DHK TO BRS AC A4","amount"=>"200.00")    
# ));
# $post_data['product_amount'] = "100";
# $post_data['vat'] = "5";
# $post_data['discount_amount'] = "5";
# $post_data['convenience_fee'] = "3";

```

## Credits

- [SSL Wireless Team]