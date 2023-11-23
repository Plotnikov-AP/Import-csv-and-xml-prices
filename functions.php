<?php
require_once 'mysql_class.php';

function removespecsimvols($str)                            
{                                                           
	$str=strip_tags(trim($str));                        
	$str=preg_replace('#\r|\n|&[a-z]*;|\,|\;#','',$str);
	$str=trim($str);                                    
	return $str;                                        
}

function decodeUnicode($s, $output = 'utf-8') 
{ 
    return preg_replace_callback('#u([a-fA-F0-9]{4})#', function ($m) use ($output) { 
        return iconv('ucs-2be', $output, pack('H*', $m[1])); 
    }, $s); 
} 

function pattern_read($id)
{
    $db=new DB();
    $pattern=$db->pattern_read($id);
    $db->close();
    if($pattern!=null){
        $json=decodeUnicode($pattern[0]);
	    //echo $json;
	    $array=json_decode($json, true);
	    //print_r($array);
        return $array;
    }else{
        return false;
    }
}

?>