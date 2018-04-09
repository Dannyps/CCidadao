<?php

require 'CCidadao.php';

use PHPUnit\Framework\TestCase;

class CCidadaoTest extends TestCase
{
    public function testCCD(){
        $this->assertEquals(8, CCidadao::getCCD(1569448));
        $this->assertEquals(1, CCidadao::getCCD(19283745));
    }

    public function testVCD(){
        $this->assertEquals(1, CCidadao::getVCD("153666960ZZ"));
    }

    public function testGetVCD(){
        $this->assertEquals(1, CCidadao::getVCD('153666960ZZ'));
        $this->assertEquals(3, CCidadao::getVCD('153666960ZY'));
        $this->assertEquals(5, CCidadao::getVCD('096273801ZY'));
        $this->assertEquals(2, CCidadao::getVCD('62350080ZZ'));
        $this->assertEquals(4, CCidadao::getVCD('000000000ZZ'));
    }

}
