<?php


namespace DElfimov\Translate\Loader;

/**
 * Class PhpFilesLoader
 * @package DElfimov\Translate\Loader
 */
class PhpFilesLoader implements LoaderInterface
{

    /**
     * @var
     */
    protected $path;

    /**
     * @var array
     */
    protected $messages = [];


    /**
     * PhpFilesLoader constructor.
     * @param $path Path to language files.
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
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