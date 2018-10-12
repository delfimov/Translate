<?php
use PHPUnit\Framework\TestCase;
use DElfimov\Translate\Loader\PhpFilesLoader;

/**
 * @covers DElfimov\Translate\Loader\PhpFilesLoader
 */
class PhpFilesLoaderTest extends TestCase
{
    public function testCanBeCreated()
    {
        $loader = $this->getLoader();
        $this->assertEquals(true, $loader instanceof PhpFilesLoader);
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
        $this->assertEquals(true, $loader->has('test1'));
        $this->assertEquals(false, $loader->has('someone'));
        $loader->setLanguage('ru');
        $this->assertEquals(true, $loader->has('test1'));
    }

    public function testGet()
    {
        $loader = $this->getLoader();
        $loader->setLanguage('en');
        $this->assertEquals(true, $loader->get('test1') == 'Test 1');
        $loader->setLanguage('ru');
        $this->assertEquals(true, $loader->get('test1') == 'Тест 1');
    }

    private function getLoader()
    {
        return new PhpFilesLoader(__DIR__ . "/messages");
    }

}
