<?php
use PHPUnit\Framework\TestCase;
USE DElfimov\Translate\Loader\PhpArrayLoader;

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
