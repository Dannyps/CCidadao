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

}
