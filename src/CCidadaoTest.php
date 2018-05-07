<?php

require 'CCidadao.php';

use PHPUnit\Framework\TestCase;

class CCidadaoTest extends TestCase
{
    public function testCCD(){
        $this->assertEquals(8, (new CCidadao("1569448_"))->getCCD());
        $this->assertEquals(1, (new CCidadao("19283745_"))->getCCD());
    }

    public function testVCD(){
        $this->assertEquals(7, (new CCidadao("045212244ZZ"))->getVCD());
        $this->assertEquals(5, (new CCidadao("096273801ZY"))->getVCD());
        $this->assertEquals(2, (new CCidadao("62350080ZZ"))->getVCD());
    }


    public function testGetVersion(){
        $this->assertEquals(1, CCidadao::staticGetVersion('ZZ'));
        $this->assertEquals(2, CCidadao::staticGetVersion('ZY'));
        $this->assertEquals(676, CCidadao::staticGetVersion('AA'));
    }

    public function testConstructorException(){
        $this->expectException(InvalidArgumentException::class);
        $c = new CCidadao("35354354", "-5");
    }

    public function testConstructor(){
        $c = new CCidadao("045212244ZZ7");

        $this->assertEquals(4521224, $c->getNum());

        $this->assertEquals(7, $c->getVCD());
        $this->assertEquals(4, $c->getCCD());
        $this->assertEquals(1, $c->getVersion());
        $c->next();
        $this->assertEquals(2, $c->getVersion());
        $this->assertEquals(9, $c->getVCD());
        $this->assertTrue($c->equals("045212244ZY9"));
        $c->rewind();
        $this->assertEquals(1, $c->getVersion());
        $this->assertTrue($c->equals("045212244ZZ7"));

    }

}
