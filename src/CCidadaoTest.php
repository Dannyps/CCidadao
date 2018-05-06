<?php

require 'CCidadao.php';

use PHPUnit\Framework\TestCase;

class CCidadaoTest extends TestCase
{
    public function testCCD(){
        $this->assertEquals(8, (new CCidadao("1569448_"))->getCCd());
        $this->assertEquals(1, (new CCidadao("19283745_"))->getCCd());
    }

    public function testVCD(){
        $this->assertEquals(1, (new CCidadao("153666960ZZ"))->getVCD());
        $this->assertEquals(3, (new CCidadao("153666960ZY"))->getVCD());
        $this->assertEquals(5, (new CCidadao("096273801ZY"))->getVCD());
        $this->assertEquals(2, (new CCidadao("62350080ZZ"))->getVCD());
        $this->assertEquals(4, (new CCidadao("000000000ZZ"))->getVCD());
    }


    public function testGetVersion(){
        $this->assertEquals(1, CCidadao::getVersion('ZZ'));
        $this->assertEquals(2, CCidadao::getVersion('ZY'));
        $this->assertEquals(676, CCidadao::getVersion('AA'));
    }

    public function testConstructor(){
        $this->expectException(InvalidArgumentException::class);
        $c = new CCidadao("35354354", "-5");
        $this->assertEquals(3535435, $c->getNum());
    }

}
