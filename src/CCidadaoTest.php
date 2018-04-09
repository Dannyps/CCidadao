<?php


include 'CCidadao.php';

/**
 * CCidadao test case.
 */
class CCidadaoTest extends PHPUnit_Framework_TestCase
{


    public function testTest(){
        $this->assertTrue(1 > 0);
    }
    public function testFail(){
        $this->assertFalse(1 < 0);
    }

    public function testCCD(){
        $this->assertEquals(8, CCidadao::getCCD(1569448));
        $this->assertEquals(1, CCidadao::getCCD(19283745));
    }

    public function testVCD(){
        $this->assertEquals(1, CCidadao::getVCD("153666960ZZ"));
    }

}

