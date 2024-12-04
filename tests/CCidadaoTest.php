<?php

/**
 * CCidadaoTeste | src/CCidadaoTest.php
 *
 * @package     CCidadao
 * @author      Daniel Silva
 * @version     v.0.1
 */

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use PHPUnit\Framework\TestCase;
use Dannyps\CCidadao\CCidadao;

/**
 * Tests for CCidadao
 * @see CCidadao
 * @author Daniel Silva
 *
 */
class CCidadaoTest extends TestCase
{

	/**
	 * Test Function
	 * 
	 * Assert that a newly created CCidadao has its `$ccd` properly parsed.
	 * 
	 * @see CCidadao::getCCD()
	 * @see CCidadao
	 */
	public function testCCD()
	{
		$this->assertEquals(8, (new CCidadao("1569448_"))->getCCD());
		$this->assertEquals(1, (new CCidadao("19283745_"))->getCCD());
	}

	/**
	 * Test Function
	 * 
	 * Assert that a newly created CCidadao has its `$vcd` properly parsed.
	 * 
	 * @see CCidadao::getVCD()
	 * @see CCidadao
	 */
	public function testVCD()
	{
		$this->assertEquals(7, (new CCidadao("045212244ZZ"))->getVCD());
		$this->assertEquals(5, (new CCidadao("096273801ZY"))->getVCD());
		$this->assertEquals(2, (new CCidadao("62350080ZZ"))->getVCD());
	}

	/**
	 * Test Function
	 * 
	 * Assert that a newly created `CCidadao` has its version properly parsed from the passed `$vcc`.
	 *
	 * Assert that function `CCidadao::getVCCbyVersion()` can properly turn a version to a `$vcc`.
	 * 
	 * Assert that function `CCidadao::staticGetVersion()` can properly turn a `$vcc` to a version.
	 *
	 * @see CCidadao::getVersion()
	 * @see CCidadao::getVCCbyVersion()
	 * @see CCidadao::staticGetVersion()
	 * @see CCidadao
	 */
	public function testVersions()
	{
		$this->assertEquals(1, (new CCidadao("62350080ZZ"))->getVersion());
		$this->assertEquals(26, (new CCidadao("62350080ZA"))->getVersion());
		$this->assertEquals(27, (new CCidadao("62350080YZ"))->getVersion());
		$this->assertEquals(28, (new CCidadao("62350080YY"))->getVersion());

		// reverse

		$this->assertEquals("ZZ", CCidadao::getVCCbyVersion(1));
		$this->assertEquals("ZA", CCidadao::getVCCbyVersion(26));
		$this->assertEquals("YZ", CCidadao::getVCCbyVersion(27));
		$this->assertEquals("YY", CCidadao::getVCCbyVersion(28));
		$this->assertEquals("YA", CCidadao::getVCCbyVersion(52));
		$this->assertEquals("XZ", CCidadao::getVCCbyVersion(53));

		// and yet

		$this->assertEquals(1, CCidadao::staticGetVersion('ZZ'));
		$this->assertEquals(2, CCidadao::staticGetVersion('ZY'));
		$this->assertEquals(676, CCidadao::staticGetVersion('AA'));
	}

	/**
	 * Test Function
	 * 
	 * Assert that non positive versions are not allowed for CCs.
	 * 
	 * @see CCidadao
	 */
	public function testConstructorException()
	{

		$this->expectException(InvalidArgumentException::class);
		new CCidadao("35354354", "0");
	}

	/**
	 * Test Function
	 * 
	 * Assert that the constructor can parse a full CC number and validate it.
	 * 
	 * @see CCidadao
	 */
	public function testConstructor()
	{
		$ncc = new CCidadao("045212244ZZ7");

		$this->assertEquals(4521224, $ncc->getNum());

		$this->assertEquals(7, $ncc->getVCD());
		$this->assertEquals(4, $ncc->getCCD());
		$this->assertEquals(1, $ncc->getVersion());
		$ncc->next();
		$this->assertEquals(2, $ncc->getVersion());
		$this->assertEquals(9, $ncc->getVCD());
		$this->assertTrue($ncc->equals("045212244ZY9"));
		$ncc->rewind();
		$this->assertEquals(1, $ncc->getVersion());
		$this->assertTrue($ncc->equals("045212244ZZ7"));

		$ncc = new CCidadao("15000000_", 1);
		$this->assertEquals("ZZ", $ncc->getvcc());
	}

	/**
	 * Test Function
	 * 
	 * Assert that a CC can be iterated through all versions.
	 * 
	 * @see CCidadao
	 */
	public function testIteration()
	{
		$iter = 1;
		foreach (new CCidadao("1563256_ZZ_") as $c) {
			$this->assertEquals($iter++, $c->getVersion());
		}
	}

	/**
	 * Test Function
	 * 
	 * Assert that a CC can be jsoned.
	 * 
	 * @see CCidadao
	 */
	public function testJson()
	{
		$cc = new CCidadao("1563256_ZZ_");
		$this->assertEquals(json_encode($cc), '{"num":1563256,"vcc":"ZZ","ccd":3,"vcd":5,"iv":1}');
	}
}
