<?php

/**
 * Citizen cards are a complicated subject. THey have two check digits, and a weird version control system.
 * Internal variables contain the following fields:
 *
 * In the example number:
 *
 * ```
 * 12345678  9  ZZ  0
 *     |     |   |  |
 *     |     |   |  -> Versioned check digit ----> $vcd -> D
 *     |     |   ----> Verion chars -------------> $vcc -> Z
 *     |     --------> Constant check digit -----> $ccd -> C
 *     --------------> the number itself --------> $num -> N
 * ```
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
class CCidadao implements Iterator {

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

    /**
     * CCidadao constructor.
     *
     * @param string $num
     * @param int    $ver
     *
     * @throws InvalidArgumentException
     */
    function __construct($num, $ver = null) {
        if ($ver != null) {
            if ($ver <= 0 || $ver > 676) {
                throw new InvalidArgumentException("Invalid version.");
            }
        }
        $match = [];
        preg_match("/^(?<num>\d*)(?<ccd>\d|_)(?<vcc>.{2}|__|)(?<vcd>\d|_|)$/m", $num, $match);

        // num is always passed
        $this->num = (int)$match['num'];

        $this->validateCCD($match['ccd']);

        // version numbers take precedence. If a version number is set, the $vcc is discarded.
        $this->validateVCC($ver, $match['vcc']);

        $this->validateVCD($match['vcd']);
    }

    /**
     * @param $ccd
     */
    private function validateCCD($ccd): void {
        if ($ccd == '_') {
            // incomplete. We must compute the ccd
            $this->ccd = self::getCCDbyNum($this->num);
        } else {

            if ($ccd != self::getCCDbyNum($this->num)) {
                throw new InvalidArgumentException("Invalid CCD passed.");
            } else {
                $this->ccd = (int)$ccd;
            }
        }
    }

    public static function getCCDbyNum($num): int {
        $array = array_map('intval', str_split($num));
        $sum = 0;
        for ($i = count($array) - 1, $j = 2; $i >= 0; $i--, $j++) {
            $sum += $array[$i] * ($j);
        }
        $res = ceil($sum / 11) * 11 - $sum;
        if ($res == 10) $res = 0;
        return (int)$res;
    }

    /**
     * @param $ver
     * @param $vcc
     */
    private function validateVCC($ver, $vcc): void {
        if ($ver == null) {
            if ($vcc == '__') {
                // default version is 1 => ZZ
                $this->vcc = 'ZZ';
            } else {
                $this->vcc = $vcc;
            }
        } else {
            $this->vcc = $this->getVCCbyVersion($ver - 1);
        }
    }

    public static function getVCCbyVersion($ver): string {
        $ch1 = self::getLetterByIndexRev((int)($ver / 26));
        $ch2 = self::getLetterByIndexRev($ver - 1 % 26);
        return $ch1 . $ch2;

    }

    private static function getLetterByIndexRev($ind) {
        // 0  -> Z
        // 1  -> Y
        // 25 -> A

        return chr(ord('Z') - $ind);
    }

    /**
     * @param $vcd
     */
    private function validateVCD($vcd): void {
        if ($vcd == '_' || $vcd == null) {
            // incomplete. We must compute the ccd
            $this->vcd = self::getVCD();
        } else {

            if ($vcd != self::getVCD()) {
                throw new InvalidArgumentException("Invalid VCD passed.");
            } else {
                $this->vcd = $vcd;
            }
        }
    }

    public function getVCD(): int {
        $arr = sscanf($this->num . $this->ccd . $this->vcc, "%d%c%c");
        $arr[1] = self::getValueByLetter($arr[1]);
        $arr[2] = self::getValueByLetter($arr[2]);
        $sum = self::f($arr[2]) + $arr[1];
        $arr2 = array_map('intval', str_split($arr[0]));
        for ($i = count($arr2) - 1, $j = 0; $i >= 0; $j++, $i--) {
            if ($j % 2 == 0) {
                $sum += self::f($arr2[$i]);
            } else {
                $sum += $arr2[$i];
            }
        }
        $sum2 = ceil($sum / 10);
        $sum2 *= 10;

        return $sum2 - $sum;
    }

    public static function getValueByLetter($letter): int {
        return ord($letter) - 55;
    }

    private static function f($a): int {
        $a *= 2;
        if ($a >= 10) $a -= 9;
        return $a;
    }

    /**
     * @return int
     */
    public function getCCD(): int {
        return $this->ccd;
    }

    public function equals($num) {

        $match = [];
        preg_match("/^(?<num>\d*)(?<ccd>\d|_)(?<vcc>.{2}|__|)(?<vcd>\d|_|)$/m", $num, $match);

        if ($this->num !== (int)$match['num']) {
            return false;
        }

        if ($this->ccd !== (int)$match['ccd']) {
            return false;
        }

        if ($this->vcc !== $match['vcc']) {
            return false;
        }

        if ($this->vcd !== (int)$match['vcd']) {
            return false;
        }

        // all checks passed.
        return true;
    }

    public function getNum() {
        return $this->num;
    }

    /**
     * Return the current element
     *
     * @link  http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current() {
        return $this;
    }

    /**
     * Move forward to next element
     *
     * @link  http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next() {
        $this->vcc = self::getVCCbyVersion($this->getVersion() + 1);
        $this->vcd = $this->getVCD();
    }

    public function getVersion() {
        return self::staticGetVersion($this->vcc);
    }

    public static function staticGetVersion($twoChars): int {
        if (strlen($twoChars) != 2) {
            throw new InvalidArgumentException("Two characters expected.");
        }
        $ch1 = $twoChars[0];
        $ch2 = $twoChars[1];

        return (ord('Z') - ord($ch1)) * 26 + (ord('Z') - ord($ch2)) + 1;
    }

    /**
     * Return the key of the current element
     *
     * @link  http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key() {
        return $this->getVersion();
    }

    /**
     * Checks if current position is valid
     *
     * @link  http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid() {
        return ($this->getVersion() >= 0 && $this->getVersion() <= 676);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link  http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind() {
        $this->vcc = self::getVCCbyVersion($this->getVersion() - 1);
        $this->vcd = $this->getVCD();
    }

    public function __toString()
    {
        //return $num.$ccd.$vcc.
    }
}

