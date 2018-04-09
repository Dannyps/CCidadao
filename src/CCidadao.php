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

    public static function getVCD($num):int{

    }
    
}

$c = new CCidadao();

