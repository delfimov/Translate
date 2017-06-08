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

namespace delfimov;

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
    protected $messages = array();

    /**
     * 'path'      Path to language files.
     *             By default is "messages" subdirectory
     *             in the library class directory.
     *
     * 'language'  User's language.
     *             If omitted then the best matching value
     *             from Accept-Language header will be used.
     *
     * 'default'   Default language (language for t() method)
     *
     * 'available' Available languages. if not set or empty
     *             then any language will be accepted.
     *
     * 'synonyms'  Synonyms for language codes
     */
    protected $options = [
        'path' => null,
        'language' => null,
        'default' => null,
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


    /**
     * Translate constructor.
     *
     * @param array $options same as self::options
     *
     * @return Translate
     */
    public function __construct(Array $options = [])
    {
        $this->setOptions($options);
    }


    /**
     * Set specified options
     *
     * @param array $options options to set
     *
     * @return void
     */
    public function setOptions(Array $options)
    {
        foreach ($options as $key => $value) {
            $this->options[$key] = $value;
        }
        $this->setLanguage($this->detectLanguage());
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
     * Get current language
     *
     * @return string $language language code
     */
    public function getLanguage()
    {
        return $this->language;
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
     * Set messages for language
     *
     * @param string $language messages languages
     * @param array  $messages messages with translations
     *
     * @return void
     */
    public function setMessages($language, Array $messages)
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
            $messages = [];
            if (!empty($language)) {
                $path = $this->getPath();
                if (file_exists($path . '/' . $language)) {
                    $messagesFile = $path . '/' . $language . '/messages.php';
                    if (file_exists($messagesFile)) {
                        $messages = include $messagesFile;
                    }
                }
            }
            $this->messages[$language] = $messages;
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
     * Get filesystem path where messages are stored
     *
     * @return mixed
     */
    protected function getPath()
    {
        if (!isset($this->options['path'])) {
            $this->options['path'] = realpath(__DIR__ . '/..');
        }
        return $this->options['path'];
    }
    
    /**
     * Gets user's accept-laguage and check if it is in available languages list.
     *
     * @todo get 2 or 3 letters language codes
     *
     * @return string 2 letters language code.
     */
    protected function detectLanguage()
    {
        if (!isset($this->options['language'])) {

            $language = $this->options['default'];

            if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                $langs = explode(
                    ';',
                    str_replace(
                        ' ', '', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'])
                    )
                );
                $max = -1;
                foreach ($langs as $key => $lang) {
                    if ($lang != '') {
                        $lang = explode(',', $lang);
                        $short = array();
                        $q = 1;
                        foreach ($lang as $value) {
                            if (strlen($value) == 2) {
                                $short[] = $value;
                            } else if ($value{0} == 'q' && $value{1} == '=') {
                                $q = substr($value, 2);
                            } else {
                                $pos = strrchr($value, '_');
                                if ($pos !== false) {
                                    $short[] = substr($value, 0, -strlen($pos));
                                } else {
                                    $short[] = $value{0} . $value{1};
                                }
                            }
                        }
                        foreach ($short as $shortValue) {
                            if (empty($this->options['available'])
                                || in_array($shortValue, $this->options['available'])
                            ) {
                                if ($max < $q) {
                                    $max = $q;
                                    $langMax = $shortValue;
                                }
                                $last = $shortValue;
                                if ($q == 1) {
                                    break 2;
                                }
                            }
                        }
                    }
                }

                if (isset($langMax)) {
                    $language = $langMax;
                } else if (isset($last)) {
                    $language = $last;
                } else {
                    $language = '';
                }

                if (!empty($language)) {
                    if (!empty($this->options['available'])
                        && !in_array($language, $this->options['available'])
                    ) {
                        $language = $this->options['default'];
                    }
                    if (!empty($this->options['synonyms'][$language])) {
                        $language = $this->options['synonyms'][$language];
                    }
                }
            }

            $this->options['language'] = $language;
        }

        return $this->options['language'];
            
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
     * Wrapper for this->t() (php >= 5.3)
     *
     * @param string $string string to translate.
     * @param array  $args   vsprintf with these arguments will be used if set.
     *
     * @return string translated string.
     */
    public function __invoke($string, $args = null)
    {
        if (!is_array($args)) {
            $args = func_get_args();
            array_shift($args);
        }
        return $this->t($string, $args);
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
            // $args = array_fill(0, count($choices), $x);
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