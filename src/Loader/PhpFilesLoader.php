<?php

namespace DElfimov\Translate\Loader;

/**
 * Class PhpFilesLoader
 * @package DElfimov\Translate\Loader
 */
class PhpFilesLoader implements LoaderInterface
{
    /**
     * Path to messages with translations
     *
     * @var
     */
    protected $path;

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
     * @param string $path Path to language files.
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @param string $language
     * @throws ContainerException
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        $this->loadMessages();
    }

    /**
     * @param bool $force reload messages from file (optional)
     * @throws ContainerException
     */
    public function loadMessages($force = false)
    {
        if (!isset($this->messages[$this->language]) || $force) {
            if ($this->isLanguageFileExists($this->language)) {
                $this->messages[$this->language] = include $this->path . '/' . $this->language . '/messages.php';
            } else {
                throw new ContainerException(
                    sprintf(
                        'Translations file "%s" for language "%s" not found',
                        $this->path . '/' . $this->language . '/messages.php',
                        $this->language
                    )
                );
            }
        }
    }

    /**
     * Is language file exists
     *
     * @param string $language
     * @return bool
     */
    protected function isLanguageFileExists($language)
    {
        return (
            file_exists($this->path . '/' . $language)
            && file_exists($this->path . '/' . $language . '/messages.php')
        );
    }

    /**
     * @param string $language
     * @return bool
     */
    public function hasLanguage($language)
    {
        return !empty($this->messages[$language]) || $this->isLanguageFileExists($language);
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
        }
        if (empty($this->messages[$this->language]) || empty($this->messages[$this->language][$message])) {
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
