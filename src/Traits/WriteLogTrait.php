<?php
namespace SSLWIRELESS\SSLCommerzIPN\Traits;

use Illuminate\Support\Facades\File;

trait WriteLogTrait
{
    public $log_pc_id           = '';
    public $log_instance_id     = '';
    public $log_instance_serial = 0;

    private function writeLog( $logdata = "" )
    {
        date_default_timezone_set('Asia/Dhaka');

        $this->log_pc_id     = isset( $_COOKIE['log_pc_id'] ) ? $_COOKIE['log_pc_id'] : "";
        if( $this->log_pc_id == "" )
        {
            $this->log_pc_id = strtoupper(base_convert(ip2long($_SERVER['REMOTE_ADDR']).rand(1000,9999),10,36 ));
            setcookie("log_pc_id", $this->log_pc_id);
        }
        $this->log_instance_id = $this->log_pc_id.'_'."SSLCOMIPN_".rand(10000,99999);

        $this->log_instance_serial++;
        $time     = date('h-A');
        $name     = 'page-hit';
        $log_path = base_path() . '/storage/logs/'.date('Y-m-d');
        if (!file_exists($log_path))
        {
            mkdir($log_path, 0777, true);
        }

        $filename       = $log_path."/".$time."-".$name.".log";
        $logtime        = date('H:i:s');
        $logwritedata   = "[".$this->log_instance_id."_".str_pad($this->log_instance_serial,2,'0',STR_PAD_LEFT)."|".$logtime."] ".$logdata."\r\n";
        File::append($filename,$logwritedata);
    }

}