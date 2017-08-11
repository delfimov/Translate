<?php
use PHPUnit\Framework\TestCase;
use DElfimov\Translate\Translate;
use DElfimov\Translate\Loader\PhpFilesLoader;

/**
 * @covers DElfimov\Translate\Translate
 */
class TranslateTest extends TestCase
{
    public function testCanBeCreated()
    {
        $translate = $this->getTranslate();
        $this->assertEquals(true, $translate instanceof Translate);
        $translate->setLanguage("en");
        $this->assertEquals(true, $translate->getLanguage() == 'en');
    }


    public function testT()
    {
        $translate = $this->getTranslate();
        $translate->setLanguage("en");
        $this->assertEquals(true, $translate->t('test1') == 'Test 1');
    }

    public function testTArgs()
    {
        $translate = $this->getTranslate();
        $translate->setLanguage("en");
        $this->assertEquals(true, $translate->t('test %s', ['check']) == 'test string check');
        $translate->setLanguage("ru");
        $this->assertEquals(true, $translate->t('test %s', ['check']) == 'тестовая строка check');
    }

    /**
     * @dataProvider numbersProvider
     */
    public function testPlural($n, $correctEN, $correctRU)
    {
        $translate = $this->getTranslate();
        $translate->setLanguage("en");
        $this->assertEquals(true, $translate->plural('%d tests', $n) == $correctEN);
        $translate->setLanguage("ru");
        $this->assertEquals(true, $translate->plural('%d tests', $n) == $correctRU);
    }

    private function getTranslate()
    {
        return new Translate(
            new PhpFilesLoader(__DIR__ . "/messages"),
            [
                "default" => "en",
                "available" => ["en", "ru"],
            ]
        );
    }

    public function numbersProvider()
    {
        return [
            [1,   'Test 1',    '1 тест'   ],
            [2,   'Tests 2',   '2 теста'  ],
            [5,   'Tests 5',   '5 тестов' ],
            [10,  'Tests 10',  '10 тестов'],
            [59,  'Tests 59',  '59 тестов'],
            [101, 'Tests 101', '101 тест' ],
        ];
    }
}
