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
     * Language code
     *
     * @var string
     */
    protected $language;

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
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @param string $language
     * @return bool
     */
    public function hasLanguage($language)
    {
        return !empty($this->messages[$language]);
    }


    /**
     * Get messages with translations
     * @param string $message
     * @return string
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function get($message)
    {
        if (empty($message) || !is_string($message)) {
            throw new ContainerException('Message must be a string');
        } elseif (empty($this->messages[$this->language]) || empty($this->messages[$this->language][$message])) {
            throw new NotFoundException('Message not found');
        }
        return $this->messages[$this->language][$message];
    }

    /**
     * Is translation for specified message available?
     *
     * @param string $message
     * @return bool
     */
    public function has($message)
    {
        return isset($this->messages[$this->language]) && isset($this->messages[$this->language][$message]);
    }
}
