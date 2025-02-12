<?php

namespace Dannyps\CCidadao;

use Iterator;
use JsonSerializable;

/**
 * CCidadao | src/CCidadao.php
 *
 * @package     CCidadao
 * @author      Daniel Silva
 * @version     v.0.1
 */


/**
 * Portuguese Citizen Cards are a complicated subject.
 * They have two check digits, and a weird version control system.
 * Internal variables contain the following fields:
 *
 * In the example number:
 *
 * ```
 * 12345678 9 ZZ 0
 * | 		|  | |
 * |		|  | ---> Versioned check digit ----> $vcd
 * | 		|  -----> Verion chars -------------> $vcc
 * | 		--------> Constant check digit -----> $ccd
 * -----------------> the number itself --------> $num
 * ```
 *
 * The version chars represent the version of the document in the following manner:
 * - ZZ => v1
 * - ZY => v2
 * - ZX => v3
 * - ...
 * - ZA => v26
 * - YZ => v27
 * - etc...
 *
 * Both `$ccd` and `$vcd` can be determined, provided the `$num` and `$ver/$vcc` are available, respectively.
 */
class CCidadao implements Iterator, JsonSerializable {

	/**
	 *
	 * @brief the constant check digit of the current document (0 to 9).
	 * @var int
	 */
	private $ccd;

	/**
	 *
	 * @brief the versioned check digit of the current document (0 to 9).
	 * @var int
	 */
	private $vcd;

	/**
	 *
	 * @brief the number of the current document (0000000 to 9999999).
	 * @var int
	 */
	private $num;

	/**
	 *
	 * @brief A two-lengthed string constaining the version characters (ZZ to AA).
	 * @var string
	 */
	private $vcc;

	/**
	 * @return string
	 */
	public function getVcc() {
		return $this->vcc;
	}

	/**
	 * CCidadao constructor.
	 *
	 * @param string $num
	 * @param int $ver
	 *
	 * @throws \InvalidArgumentException
	 */
	function __construct($num, $ver = null) {
		if ($ver != null) {
			if ($ver < 1 || $ver > 676) {
				throw new \InvalidArgumentException("Invalid version.");
			}
		}
		$match = [];
		preg_match("/^(?<num>\d*)(?<ccd>\d|_)(?<vcc>.{2}|__|)(?<vcd>\d|_|)$/m", $num, $match);

		if (empty($match))
			throw new \InvalidArgumentException("Invalid number.");

		// num is always passed
		$this->num = (int) $match['num'];

		$this->validateCCD($match['ccd']);

		// version numbers take precedence. If a version number is set, the $vcc is discarded.
		$this->validateVCC($ver, $match['vcc']);

		$this->validateVCD($match['vcd']);
	}

	/**
	 * Make sure the passed ccd is valid, considering the currently set num.
	 *
	 * @param int $ccd
	 *
	 * @throws \Exception when invalid `$ccd` passed.
	 */
	private function validateCCD($ccd): void {
		if ($ccd == '_') {
			// incomplete. We must compute the ccd
			$this->ccd = self::getCCDbyNum($this->num);
			return;
		}

		if ($ccd != self::getCCDbyNum($this->num)) {
			throw new \Exception("Invalid CCD passed.");
		} else {
			$this->ccd = (int) $ccd;
		}
	}

	/**
	 *
	 * @param string $num
	 * @return int
	 */
	public static function getCCDbyNum($num): int {
		$array = array_map('intval', str_split($num));
		$sum = 0;
		for ($i = count($array) - 1, $j = 2; $i >= 0; $i--, $j++) {
			$sum += $array[$i] * ($j);
		}
		$res = ceil($sum / 11) * 11 - $sum;
		if ($res == 10)
			$res = 0;
		return (int) $res;
	}

	/**
	 *
	 * @param int $ver
	 * @param int $vcc
	 */
	private function validateVCC($ver, $vcc): void {
		if ($ver == null) {
			if ($vcc == '__' || $vcc == "") {
				// default version is 1 => ZZ
				$this->vcc = 'ZZ';
			} else {
				$this->vcc = strtoupper($vcc);
			}
		} else {
			$this->vcc = $this->getVCCbyVersion($ver);
		}
	}

	public static function getVCCbyVersion($ver): string {
		$ch1 = self::getLetterByIndexRev((int) (($ver - 1) / 26));
		$ch2 = self::getLetterByIndexRev(($ver - 1) % 26);
		return $ch1 . $ch2;
	}

	private static function getLetterByIndexRev($ind) {
		// 0 -> Z
		// 1 -> Y
		// 25 -> A
		return chr(ord('Z') - $ind);
	}

	/**
	 *
	 * @param int $vcd
	 */
	private function validateVCD($vcd): void {
		if ($vcd == '_' || $vcd == null) {
			// incomplete. We must compute the ccd
			$this->vcd = self::getVCD();
		} else {

			if ($vcd != self::getVCD()) {
				throw new \InvalidArgumentException("Invalid VCD passed.");
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

	private static function f($arg): int {
		$arg *= 2;
		if ($arg >= 10)
			$arg -= 9;
		return $arg;
	}

	/**
	 *
	 * @return int
	 */
	public function getCCD(): int {
		return $this->ccd;
	}

	public function equals($num) {
		$match = [];
		preg_match("/^(?<num>\d*)(?<ccd>\d|_)(?<vcc>.{2}|__|)(?<vcd>\d|_|)$/m", $num, $match);

		if ($this->num !== (int) $match['num']) {
			return false;
		}

		if ($this->ccd !== (int) $match['ccd']) {
			return false;
		}

		if ($this->vcc !== $match['vcc']) {
			return false;
		}

		if ($this->vcd !== (int) $match['vcd']) {
			return false;
		}

		// all checks passed.
		return true;
	}
	public function getNum() {
		return $this->num;
	}

	public function jsonSerialize(): mixed {
		return [
			'num' => $this->num,
			'vcc' => $this->vcc,
			'ccd' => $this->ccd,
			'vcd' => $this->vcd,
			'iv' => $this->getVersion()
		];
	}

	/**
	 * Return the current element
	 *
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 * @since 5.0.0
	 */
	public function current(): CCidadao {
		return $this;
	}

	/**
	 * Move forward to next element
	 *
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function next(): void {
		$this->vcc = self::getVCCbyVersion($this->getVersion() + 1);
		$this->vcd = $this->getVCD();
	}

	public function getVersion() {
		return self::staticGetVersion($this->vcc);
	}

	public static function staticGetVersion(String $twoChars): int {
		if (strlen($twoChars) != 2) {
			throw new \InvalidArgumentException("Two characters expected.");
		}
		$ch1 = $twoChars[0];
		$ch2 = $twoChars[1];

		return (ord('Z') - ord($ch1)) * 26 + (ord('Z') - ord($ch2)) + 1;
	}

	/**
	 * Return the key of the current element
	 *
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return int on success, or null on failure.
	 * @since 5.0.0
	 */
	public function key(): int | null {
		return $this->getVersion();
	}

	/**
	 * Checks if current position is valid
	 *
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 *         Returns true on success or false on failure.
	 * @since 5.0.0
	 */
	public function valid(): bool {
		return ($this->getVersion() >= 0 && $this->getVersion() <= 676);
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function rewind(): void {
		$this->vcc = "ZZ";
		$this->vcd = $this->getVCD();
	}
	public function __toString() {
		return "" . $this->num . $this->ccd . $this->vcc . $this->vcd;
	}
}
