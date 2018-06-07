<?php
require 'CCidadao.php';
require '..\vendor\autoload.php';

use PHPUnit\Framework\TestCase;
class CCidadaoTest extends TestCase {
	public function testCCD() {
		$this->assertEquals ( 8, (new CCidadao ( "1569448_" ))->getCCD () );
		$this->assertEquals ( 1, (new CCidadao ( "19283745_" ))->getCCD () );
	}
	public function testVCD() {
		$this->assertEquals ( 7, (new CCidadao ( "045212244ZZ" ))->getVCD () );
		$this->assertEquals ( 5, (new CCidadao ( "096273801ZY" ))->getVCD () );
		$this->assertEquals ( 2, (new CCidadao ( "62350080ZZ" ))->getVCD () );
	}
	public function testVersions() {
		$this->assertEquals ( 1, (new CCidadao ( "62350080ZZ" ))->getVersion () );
		$this->assertEquals ( 26, (new CCidadao ( "62350080ZA" ))->getVersion () );
		$this->assertEquals ( 27, (new CCidadao ( "62350080YZ" ))->getVersion () );
		$this->assertEquals ( 28, (new CCidadao ( "62350080YY" ))->getVersion () );
		
		// reverse
		
		$this->assertEquals ( "ZZ", CCidadao::getVCCbyVersion ( 1 ) );
		$this->assertEquals ( "ZA", CCidadao::getVCCbyVersion ( 26 ) );
		$this->assertEquals ( "YZ", CCidadao::getVCCbyVersion ( 27 ) );
		$this->assertEquals ( "YY", CCidadao::getVCCbyVersion ( 28 ) );
		$this->assertEquals ( "YA", CCidadao::getVCCbyVersion ( 52 ) );
		$this->assertEquals ( "XZ", CCidadao::getVCCbyVersion ( 53 ) );
		
		// and yet
		
		$this->assertEquals ( 1, CCidadao::staticGetVersion ( 'ZZ' ) );
		$this->assertEquals ( 2, CCidadao::staticGetVersion ( 'ZY' ) );
		$this->assertEquals ( 676, CCidadao::staticGetVersion ( 'AA' ) );
	}
	public function testConstructorException() {
		$this->expectException ( InvalidArgumentException::class );
		new CCidadao ( "35354354", "-5" );
	}
	public function testConstructor() {
		$ncc = new CCidadao ( "045212244ZZ7" );
		
		$this->assertEquals ( 4521224, $ncc->getNum () );
		
		$this->assertEquals ( 7, $ncc->getVCD () );
		$this->assertEquals ( 4, $ncc->getCCD () );
		$this->assertEquals ( 1, $ncc->getVersion () );
		$ncc->next ();
		$this->assertEquals ( 2, $ncc->getVersion () );
		$this->assertEquals ( 9, $ncc->getVCD () );
		$this->assertTrue ( $ncc->equals ( "045212244ZY9" ) );
		$ncc->rewind ();
		$this->assertEquals ( 1, $ncc->getVersion () );
		$this->assertTrue ( $ncc->equals ( "045212244ZZ7" ) );
	}
	public function testIteration() {
		$i = 1;
		foreach ( new CCidadao ( "15366696_ZZ_" ) as $c ) {
			$this->assertEquals ( $i ++, $c->getVersion () );
		}
	}
}
