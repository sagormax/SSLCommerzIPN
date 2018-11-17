<?php
namespace Satouch\SSLCommerzIPN\Traits;

use Satouch\SSLCommerzIPN\Exceptions\ValidationException;

trait PaymentHashValidation
{
    # FUNCTION TO CHECK HASH VALUE
    public function _SSLCOMMERZ_hash_verify($store_passwd = "", $verify_sign, $verify_key)
    {
        if( isset($verify_sign) && isset($verify_key) ) {
            # NEW ARRAY DECLARED TO TAKE VALUE OF ALL POST    
            $pre_define_key = explode(',', $verify_key);
            
            $new_data = array();
            if(!empty($pre_define_key )) 
            {
                foreach($pre_define_key as $value) 
                {
                    if(isset($_POST[$value])) 
                    {
                        $new_data[$value] = ($_POST[$value]);
                    }
                }
            }
            # ADD MD5 OF STORE PASSWORD
            $new_data['store_passwd'] = md5($store_passwd);
            
            # SORT THE KEY AS BEFORE
            ksort($new_data);
            
            $hash_string="";
            foreach($new_data as $key=>$value) { $hash_string .= $key.'='.($value).'&'; }
            $hash_string = rtrim($hash_string,'&');
            
            if(md5($hash_string) != $verify_sign)
            {
                throw new ValidationException('Hash string and verify sign not matched.', 422);
            }

            return true;
        } 
        else
        {
              throw new ValidationException('verify sign & verify key is missing.', 422);
        }    
    }

}