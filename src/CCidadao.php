<?php

/**
 * Citizen cards are a complicated subject. THey have two check digits, and a weird version control system.
 * Internal variables contain the following fields:
 * 
 * In the example number:
 * 
 * 12345678  9  ZZ  0
 *     |     |   |  |
 *     |     |   |  -> Versioned check digit ----> $vcd
 *     |     |   ----> Verion chars -------------> $vcc
 *     |     --------> Constant check digit -----> $ccd
 *     --------------> the number itslef --------> $num
 *
 *  The version chars represent the version of the document in the following manner:
 *   - ZZ => v1
 *   - ZY => v2
 *   - ZX => v3
 *   - ...
 *   - ZA => v26
 *   - YZ => v27
 *   etc...
 *
 *  Both $ccd and $vcd can be determined, provided the $num and $ver/$vcc are available, respectively.
 *  
 *  $ver is an internal integer variable representing the version of the present document.
 *
 */
class CCidadao{
    /**
     * @brief the version of the present document (1 to 676).
     * @var int
     */
    private $ver;
    
    /** @brief the constant check digit of the current document (0 to 9).
    * @var int
    */
    private $ccd;
    
    /** @brief the versioned check digit of the current document (0 to 9).
     * @var int
     */
    private $vcd;
    
    /** @brief the number of the current document (0000000 to 9999999).
     * @var int
     */
    private $num;
    
    /** @brief A two-lengthed string constaining the version characters (ZZ to AA).
     * @var string
     */
    private $vcc;
    
    function __construct(){
        return;
    }

    public static function getCCD($num):int{
        $array  = array_map('intval', str_split($num));
        $sum=0;
        for($i = count($array)-1, $j=2; $i>=0;$i--, $j++){
            $sum+=$array[$i]*($j);
        }
        $res = ceil($sum/11)*11-$sum;
        if($res == 10) $res = 0;
        return $res;
    }

    private static function f($a):int{
        $a*=2;
        if($a >=10) $a-=9;
        return $a;
    }

    public static function getVCD($num):int{


        $arr = sscanf ( $num , "%d%c%c");
        $arr[1] = CCidadao::getValueByLetter($arr[1]);
        $arr[2] = CCidadao::getValueByLetter($arr[2]);
        $sum = CCidadao::f($arr[2])+$arr[1];
        $arr2  = array_map('intval', str_split($arr[0]));
        for($i = count($arr2)-1, $j=0; $i>=0; $j++, $i--){
            if($j % 2 == 0){
                $sum+=CCidadao::f($arr2[$i]);
            }else{
                $sum+=$arr2[$i];
            }
        }
        $sum2=ceil($sum/10);
        $sum2*=10;

        return $sum2-$sum;
    }

    public static function getValueByLetter($letter):int {
        return ord($letter)-55;
    }
    
}

$c = new CCidadao();

#echo $c->getVCD('153666960ZZ');

