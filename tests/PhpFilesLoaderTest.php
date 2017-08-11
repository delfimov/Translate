<?php
use PHPUnit\Framework\TestCase;
USE DElfimov\Translate\Loader\PhpFilesLoader;

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

    public function testHas()
    {
        $loader = $this->getLoader();
        $this->assertEquals(true, $loader->has('ru'));
        $this->assertEquals(true, $loader->has('en'));
    }

    public function testGet()
    {
        $loader = $this->getLoader();
        $messages = $loader->get('ru');
        $this->assertEquals(true, (!empty($messages) && is_array($messages)));
        $messages = $loader->get('en');
        $this->assertEquals(true, (!empty($messages) && is_array($messages)));
    }

    private function getLoader()
    {
        return new PhpFilesLoader(__DIR__ . "/messages");
    }

}
