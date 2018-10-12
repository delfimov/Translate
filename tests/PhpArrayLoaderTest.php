<?php
use PHPUnit\Framework\TestCase;
use DElfimov\Translate\Loader\PhpArrayLoader;

/**
 * @covers DElfimov\Translate\Loader\PhpArrayLoader
 */
class PhpArrayLoaderTest extends TestCase
{
    public function testCanBeCreated()
    {
        $loader = $this->getLoader();
        $this->assertEquals(true, $loader instanceof PhpArrayLoader);
    }

    public function testHasLanguage()
    {
        $loader = $this->getLoader();
        $this->assertEquals(true, $loader->hasLanguage('ru'));
        $this->assertEquals(true, $loader->hasLanguage('en'));
        $this->assertEquals(false, $loader->hasLanguage('de'));
    }

    public function testHas()
    {
        $loader = $this->getLoader();
        $loader->setLanguage('en');
        $this->assertEquals(true, $loader->has('some'));
        $this->assertEquals(false, $loader->has('someone'));
        $loader->setLanguage('ru');
        $this->assertEquals(true, $loader->has('another'));
    }

    public function testGet()
    {
        $loader = $this->getLoader();
        $loader->setLanguage('en');
        $this->assertEquals(true, $loader->get('some') == 'Some string');
        $loader->setLanguage('ru');
        $this->assertEquals(true, $loader->get('another') == 'Другая строка');
    }

    private function getLoader()
    {
        return new PhpArrayLoader(
            $messages = [
                'en' => [
                   'some' => 'Some string', 'another' => 'Another string'
                ],
                'ru' => [
                   'some' => 'Одна строка', 'another' => 'Другая строка'
                ]
            ]
        );
    }

}
