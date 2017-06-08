<?php
use PHPUnit\Framework\TestCase;
use delfimov\Translate;

/**
 * @covers delfimov\Translate
 */

class TranslateTest extends TestCase
{

    public function testCanBeCreated()
    {
        $translate = new Translate();
        $this->assertEquals(true, $translate instanceof Translate);
    }


}