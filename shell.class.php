<?php


/**
 * @description popular development for shell
 * @author by ricolau<ricolau@qq.com>
 * @version 2014-07-24
 *
*/

class shell{
    public static function getphp(){
        return self::pget('which php');
    }

    public static function execute($cmd, $redirect = '/dev/null', $runBackground = false){
        $str = $cmd .' > '.$redirect;
        if($runBackground){
            $str .= ' &';
        }
        return self::pget($str);
    }
    public static function pget($str){
        $hdl = @popen($str, "r");
        if(!$hdl){
            return false;
        }
        $ret = '';
        while(!feof($hdl)){
            $ret .=fread($hdl, 1024);
        }
        @pclose($hdl);
        return trim($ret);
    }


    public static function mycmd(){
        $cmd = implode(' ',$GLOBALS['argv']);
        return $cmd;
    }

}
