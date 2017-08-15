<?php


namespace DElfimov\Translate\Loader;

/**
 * Class PhpArrayLoader
 * @package DElfimov\Translate\Loader
 */
class PhpArrayLoader implements LoaderInterface
{

    /**
     * Messages container
     *
     * @var array
     */
    protected $messages = [];


    /**
     * PhpFilesLoader constructor.
     * @param array $messages messages array.
     * Example:
     * $messages = [
     *   'en' => [
     *      'some' => 'Some string', 'another' => 'Another string'
     *   ],
     *   'ru' => [
     *      'some' => 'Одна строка', 'another' => 'Другая строка'
     *   ]
     * ];
     * $loader = new PhpArrayLoader($messages);
     */
    public function __construct(array $messages)
    {
        $this->messages = $messages;
    }

    /**
     * Get messages with translations
     *
     * @param string $language
     * @return array
     */
    public function get($language)
    {
        if (empty($language)) {
            return [];
        }
        return empty($this->messages[$language]) ? [] : $this->messages[$language];
    }

    /**
     * Is translation for specified locale avialable?
     *
     * @param string $language
     * @return bool
     */
    public function has($language)
    {
        return isset($this->messages[$language]);
    }
}
