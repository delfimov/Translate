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
     * PhpFilesLoader constructor.
     * @param string $path Path to language files.
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Get messages with translations from specified path
     *
     * @param string $language
     * @return array
     */
    public function get($language)
    {
        if (empty($language)) {
            return [];
        }
        if (!isset($this->messages[$language])) {
            $messages = [];
            if ($this->has($language)) {
                $messages = include $this->path . '/' . $language . '/messages.php';
            }
            $this->messages[$language] = $messages;
        }
        return $this->messages[$language];
    }

    /**
     * Determines whether a language is available.
     *
     * @param string $language
     * @return bool
     */
    public function has($language)
    {
        return (
            file_exists($this->path . '/' . $language)
            && file_exists($this->path . '/' . $language . '/messages.php')
        );
    }
}
