<?php

namespace DElfimov\Translate\Loader;

use Psr\Container\ContainerInterface;

/**
 * Interface LoaderInterface
 * @package DElfimov\Translate\Loader
 */
interface LoaderInterface extends ContainerInterface
{
    /**
     * Determines whether a language is available.
     * @param string $language language code
     * @return bool
     */
    public function hasLanguage($language);

    /**
     * Set a language for a messages container
     * @param string $language language code
     * @throws ContainerException
     * @return void
     */
    public function setLanguage($language);

    /**
     * Fetches messages.
     * @param string $language language code.
     * @return string translated message
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function get($language);

    /**
     * Determines whether a translation is available.
     * @param string $language language code
     * @return bool
     */
    public function has($language);
}
