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

    /**
     * @dataProvider headersProvider
     */
    public function testAcceptLanguage($language, $header)
    {
        $translate = $this->getTranslate();
        $translate->setOptions(
            [
                'accept-language' => $header,
                'available' => ['en', 'ru', 'de', 'zh', 'ja'],
            ]
        );
        $this->assertEquals(true, $translate->getLanguage(true) == $language);
    }

    public function headersProvider()
    {
        return [
            ['zh', 'en-Kata;q=0.1,en_PCN;djfiasjdflsakdjflksajflas,zh_HKG;q=2000,tlh-Latn-US'],
            ['zh', 'ja-Kata;q=0.1,en_PCN;q=0.8,zh_HKG;q=0.9,tlh-Latn-US'],
            ['en', 'ja-Kata;q=0.1,en_PCN;q=1,zh_HKG;q=0.9,tlh-Latn-US'],
            ['en', 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5'],
            ['en', 'en-US,en;q=0.5'],
            ['en', 'da, en-gb;q=0.8, en;q=0.7'],
            ['ru', 'ru,en-US;q=0.8,en;q=0.6'],
            ['de', 'de-CH'],
            ['de', 'de'],
        ];
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
        $translate->setLanguage('en');
        $this->assertEquals(true, $translate->t('test %s', ['check']) == 'test string check');
        $translate->setLanguage('ru');
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
            new PhpFilesLoader(__DIR__ . '/messages'),
            [
                'default' => 'en',
                'available' => ['en', 'ru'],
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
