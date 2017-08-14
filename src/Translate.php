<?php
/**
 * Easy to use translate library for multi-language websites
 *
 * PHP version 5
 *
 * @category Translate
 * @package  Translate
 * @author   Dmitry Elfimov <elfimov@gmail.com>
 * @license  MIT License
 * @link     https://github.com/delfimov/Translate/
 */

namespace DElfimov\Translate;

/**
 * Class Translate
 *
 * @category Translate
 * @package  Translate
 * @author   Dmitry Elfimov <elfimov@gmail.com>
 * @license  MIT License
 * @link     https://github.com/delfimov/Translate/
 */
class Translate
{

    /**
     * Current language
     *
     * @var string
     */
    protected $language = '';

    /**
     * Messages with translations
     *
     * @var array
     */
    protected $messages = [];

    /**
     *
     * 'language'  User's language.
     *             If omitted then the best matching value
     *             from Accept-Language header will be used.
     *
     * 'default-language' Default language (language for t() method)
     *
     * 'accept-language' If set, will be used instead of $_SERVER['HTTP_ACCEPT_LANGUAGE']
     *
     * 'available' Available languages. if not set or empty
     *             then any language will be accepted.
     *
     * 'synonyms'  Synonyms for language codes
     */
    protected $options = [
        'language' => null,
        'default-language' => 'en',
        'accept-language' => null,
        'max-languages' => 99,
        'available' => null,
        'synonyms' => [
            'gb' => 'en',
            'us' => 'en',
            'ua' => 'uk',
            'cn' => 'zh',
            'hk' => 'zh',
            'tw' => 'zh',
        ]
        // uk and us are synonyms for en.
        // if HTTP_ACCEPT_LANGUAGE is set to 'gb' or 'us'
        // then 'en' will be used instead.
    ];

    protected $maxLanguages = 99;

    /**
     * @var Loader\PhpFilesLoader
     */
    protected $loader;


    /**
     * Translate constructor.
     *
     * @param Loader\LoaderInterface $loader messages loader
     * @param array $options same as self::options
     *
     * @return Translate
     */
    public function __construct($loader, array $options = [])
    {
        $this->loader = $loader;
        $this->setOptions($options);
    }


    /**
     * Set specified options
     *
     * @param array $options options to set
     *
     * @return void
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $this->options[$key] = $value;
        }
        $this->setLanguage($this->getLanguage());
        $this->setMessages($this->language, $this->getMessages($this->language));
    }

    /**
     * Set language for translations with method t() and plural()
     *
     * @param string $language language code
     *
     * @return void
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Is messages for specified language are loaded
     *
     * @param string $language language code
     *
     * @return bool
     */
    public function isMessages($language)
    {
        return isset($this->messages[$language]);
    }

    /**
     * Are messages for specified language available
     *
     * @param string $language language code
     *
     * @return bool
     */
    public function hasMessages($language)
    {
        return $this->loader->has($language);
    }


    /**
     * Set messages for language
     *
     * @param string $language messages languages
     * @param array  $messages messages with translations
     *
     * @return void
     */
    public function setMessages($language, array $messages)
    {
        $this->messages[$language] = $messages;
    }

    /**
     * Loads translation files.
     *
     * @param string $language language to load
     *
     * @return array
     */
    protected function getMessages($language)
    {
        if (!isset($this->messages[$language])) {
            $this->messages[$language] = $this->loader->get($language);
        }
        return $this->messages[$language];
    }

    /**
     * Get translation of message for language
     *
     * @param string $language language code
     * @param string $string   to translate
     *
     * @return mixed
     */
    protected function getMessage($language, $string)
    {
        if (!$this->isMessages($this->language)) {
            $this->setMessages($this->language, $this->getMessages($this->language));
        }
        if (isset($this->messages[$language][$string])) {
            $string = $this->messages[$language][$string];
        }
        return $string;
    }


    /**
     * Get current language
     *
     * @return string $language language code
     */
    public function getLanguage()
    {
        if (empty($this->language)) {
            if (!empty($this->options['language'])) {
                $acceptLanguage = $this->options['language'];
            } elseif (!empty($this->options['accept-language'])) {
                $acceptLanguage = $this->options['accept-language'];
            } elseif (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            } else {
                $acceptLanguage = $this->options['default-language'];
            }
            $languages = $this->detectLanguages($acceptLanguage);
            $this->language = $this->getBestMatchingLanguage($languages);
        }
        return $this->language;
    }

    protected function getBestMatchingLanguage($languages)
    {
        if (!empty($this->options['available'])) {
            foreach ($languages as $langUser) {
                $shortLang = $this->shortLanguageCode($langUser);
                foreach ($this->options['available'] as $langAvailable) {
                    if ($langUser == $langAvailable
                        || (isset($this->options['synonyms'][$langUser])
                            && $this->options['synonyms'][$langUser] == $langAvailable)
                    ) {
                        return $langAvailable;
                    } elseif ($shortLang == $langAvailable
                        || (isset($this->options['synonyms'][$shortLang])
                            && $this->options['synonyms'][$shortLang] == $langAvailable)
                    ) {
                        return $langAvailable;
                    }
                }
            }
        }
        return $this->options['default-language'];
    }

    protected function shortLanguageCode($language)
    {
        if (strlen($language) > 2) {
            $dashPos = strpos($language, '-');
            if ($dashPos > 0) {
                $language = substr($language, 0, $dashPos);
            } else {
                $underscorePos = strpos($language, '_');
                if ($underscorePos > 0) {
                    $language = substr($language, 0, $underscorePos);
                }
            }
        }
        return $language;
    }

    public function detectLanguages($http_accept_language, $resolution = 100)
    {
        $tags = array_map('trim', explode(',', $http_accept_language));
        $languages = [];
        $languagesOrder = [];
        foreach ($tags as $tag) {
            $split = array_map('trim', explode(';', $tag, 2));
            if (empty($split[1])) {
                $q = $resolution;
            } else {
                $qArr = array_map('trim', explode('=', $split[1], 2));
                if (!empty($qArr) && !empty($qArr[1]) && is_numeric($qArr[1])) {
                    $q = floor($qArr[1] * $resolution);
                } else {
                    $q = 0;
                }
            }
            $languages[] = $split[0];
            $languagesOrder[] = $q;
        }
        array_multisort($languagesOrder, SORT_DESC, $languages, SORT_DESC);
        return array_slice($languages, 0, $this->options['max-languages']);
    }


    /**
     * Show translated message
     *
     * @param string $string string to translate.
     * @param array  $args   vsprintf with these arguments will be used if set.
     *
     * @return string translated string.
     */
    public function t($string, $args = null)
    {
        $string = $this->getMessage($this->language, $string);
        if (isset($args)) {
            if (!is_array($args)) {
                $args = func_get_args();
                array_shift($args);
            }
            $string = vsprintf($string, $args);
        }
        return $string;
    }
    

    /**
     * Chooses plural translate based on $x
     *
     * @param string $string string to translate divided with "|" character.
     * @param string $x      plural variable.
     * @param array  $args   vsprintf with these arguments will be used
     *                       if set (optional).
     *
     * @return string translated string.
     */
    public function plural($string, $x, $args = null)
    {
        $string = $this->getMessage($this->language, $string);

        $choices = explode('|', $string);
        $args = isset($args) ? $args : array($x);
        if (isset($args)) {
            if (!is_array($args)) {
                $args = array_slice(func_get_args(), 2);
            }
        } else {
            $args = [$x];
        }

        foreach ($args as &$arg) {
            $arg = $this->t($arg);
        }

        $plural = $this->pluralRule($this->language, $x);
        $string = isset($choices[$plural]) ? $choices[$plural] : $choices[0];
        return vsprintf($string, $args);
    }
    
    
    /**
     * The plural rules are derived from code of the Zend Framework (2010-09-25),
     * which is subject to the new BSD license
     * (http://framework.zend.com/license/new-bsd).
     * Copyright (c) 2005-2010 Zend Technologies USA Inc.
     * (http://www.zend.com)
     *
     * @param string $language language code
     * @param string $x        plural variable
     *
     * @return integer index of plural form rule.
     */
    protected function pluralRule($language, $x)
    {
        switch ($language) {
            case 'bo':
            case 'dz':
            case 'id':
            case 'ja':
            case 'jv':
            case 'ka':
            case 'km':
            case 'kn':
            case 'ko':
            case 'ms':
            case 'th':
            case 'tr':
            case 'vi':
            case 'zh':
                return 0;
                break;

            case 'af':
            case 'az':
            case 'bn':
            case 'bg':
            case 'ca':
            case 'da':
            case 'de':
            case 'el':
            case 'en':
            case 'eo':
            case 'es':
            case 'et':
            case 'eu':
            case 'fa':
            case 'fi':
            case 'fo':
            case 'fur':
            case 'fy':
            case 'gl':
            case 'gu':
            case 'ha':
            case 'he':
            case 'hu':
            case 'is':
            case 'it':
            case 'ku':
            case 'lb':
            case 'ml':
            case 'mn':
            case 'mr':
            case 'nah':
            case 'nb':
            case 'ne':
            case 'nl':
            case 'nn':
            case 'no':
            case 'om':
            case 'or':
            case 'pa':
            case 'pap':
            case 'ps':
            case 'pt':
            case 'so':
            case 'sq':
            case 'sv':
            case 'sw':
            case 'ta':
            case 'te':
            case 'tk':
            case 'ur':
            case 'zu':
                return ($x == 1) ? 0 : 1;
                break;

            case 'am':
            case 'bh':
            case 'fil':
            case 'fr':
            case 'gun':
            case 'hi':
            case 'ln':
            case 'mg':
            case 'nso':
            case 'xbr':
            case 'ti':
            case 'wa':
                return (($x == 0) || ($x == 1)) ? 0 : 1;
                break;

            case 'be':
            case 'bs':
            case 'hr':
            case 'ru':
            case 'sr':
            case 'uk':
                return (
                    ($x % 10 == 1) && ($x % 100 != 11)
                ) ? (
                    0
                ) : (
                    (
                        ($x % 10 >= 2)
                        && ($x % 10 <= 4)
                        && (($x % 100 < 10) || ($x % 100 >= 20))
                    ) ? 1 : 2
                );
                break;

            case 'cs':
            case 'sk':
                return ($x == 1) ? 0 : ((($x >= 2) && ($x <= 4)) ? 1 : 2);
                break;

            case 'ga':
                return ($x == 1) ? 0 : (($x == 2) ? 1 : 2);
                break;

            case 'lt':
                return (
                    ($x % 10 == 1) && ($x % 100 != 11)
                ) ? (
                    0
                ) : (
                    (($x % 10 >= 2) && (($x % 100 < 10) || ($x % 100 >= 20))) ? 1 : 2
                );
                break;

            case 'sl':
                return (
                    $x % 100 == 1
                ) ? (
                    0
                ) : (
                    ($x % 100 == 2) ? 1 : ((($x % 100 == 3) || ($x % 100 == 4)) ? 2 : 3)
                );
                break;

            case 'mk':
                return ($x % 10 == 1) ? 0 : 1;
                break;

            case 'mt':
                return (
                    $x == 1
                ) ? (
                    0
                ) : (
                    (
                        ($x == 0) || (($x % 100 > 1) && ($x % 100 < 11))
                    ) ? (
                        1
                    ) : ((($x % 100 > 10) && ($x % 100 < 20)) ? 2 : 3)
                );
                break;

            case 'lv':
                return ($x == 0) ? 0 : ((($x % 10 == 1) && ($x % 100 != 11)) ? 1 : 2);
                break;

            case 'pl':
                return (
                    $x == 1
                ) ? (
                    0
                ) : (
                    (
                        ($x % 10 >= 2)
                        && ($x % 10 <= 4)
                        && (($x % 100 < 12) || ($x % 100 > 14))
                    ) ? 1 : 2
                );
                break;

            case 'cy':
                return (
                    $x == 1
                ) ? (
                    0
                ) : (($x == 2) ? 1 : ((($x == 8) || ($x == 11)) ? 2 : 3));
                break;

            case 'ro':
                return (
                    $x == 1
                ) ? (
                    0
                ) : ((($x == 0) || (($x % 100 > 0) && ($x % 100 < 20))) ? 1 : 2);
                break;

            case 'ar':
                return (
                    $x == 0
                ) ? (
                    0
                ) : (
                    ($x == 1) ? 1 : (
                        ($x == 2) ? 2 : (
                            (
                                ($x >= 3)
                                && ($x <= 10)
                            ) ? (
                                3
                            ) : (
                                (($x >= 11) && ($x <= 99)) ? 4 : 5
                            )
                        )
                    )
                );
                break;

            default:
                return 0;
                break;
        }
    }
}
