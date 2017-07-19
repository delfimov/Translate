<?php

namespace DElfimov\Translate\Loader;


interface LoaderInterface
{

    /**
     * Fetches messages.
     *
     * @param string $language language code.
     *
     * @return array Messages
     *
     */
    public function get($language);

    /**
     * Determines whether a language is available.
     *
     * @param string $language language code
     *
     * @return bool
     *
     */
    public function has($language);

}