# SSLCOMMERZ IPN Payment Package


## Install

``` bash
"Satouch/SSLCommerzIPN": "dev-master"
```

``` bash
"repositories": [
    {
        "type": "git",
        "url": "https://github.com/sagormax/SSLCommerzIPN.git"
    }
], 
```

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
# @return a collection of data that in last inserted otherwise FALSE;
# @note if tran_id/voucher_number not matched return load 404 page

PaymentValidation::sslcommerz_ipn_data_insert( $store_id, $store_passwd, $validate_url, $request )


# payment processing happen on cron job
# @return a json data with status_code, feedback, order collection

PaymentValidation::ipn_payment_process( $request );


# payment checkout page
# @return if false return json_object or view sslcommerz checkout page

PaymentValidation::payment_checkout($payment_api_url, $post_data);

```

## Credits

- [SSL Wireless Team]