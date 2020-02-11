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

use \Psr\Log\LoggerInterface;
use \DElfimov\Translate\Loader\LoaderInterface;
use \DElfimov\Translate\Loader\ContainerException;
use \DElfimov\Translate\Loader\NotFoundException;

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
            'cn' => 'zh',
            'hk' => 'zh',
            'tw' => 'zh',
        ]
        // uk and us are synonyms for en.
        // if HTTP_ACCEPT_LANGUAGE is set to 'gb' or 'us'
        // then 'en' will be used instead.
    ];

    /**
     * Messages loader
     *
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * @var LoggerInterface
     */
    protected $logger;


    /**
     * Translate constructor.
     *
     * @param LoaderInterface $loader messages loader
     * @param array $options same as self::options
     * @param LoggerInterface $logger PSR-3 compatible logging library (ex. Monolog)
     */
    public function __construct(LoaderInterface $loader, array $options = [], LoggerInterface $logger = null)
    {
        $this->loader = $loader;
        $this->setOptions($options);
        $this->setLanguage($this->getLanguage(true));
        if (null !== $logger) {
            $this->setLogger($logger);
        }
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param string $message
     */
    public function addLoggerWarning($message)
    {
        if (null !== $this->logger) {
            $this->logger->warning($message);
        }
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
        $this->loader->setLanguage($language);
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
        $message = $string;
        try {
            $message = $this->loader->get($string);
        } catch (NotFoundException $exception) {
            $this->addLoggerWarning(
                sprintf('[translate] language: "%s", message "%s" not found', $language, $string)
            );
        } catch (ContainerException $exception) {
            $this->addLoggerWarning(
                sprintf('[translate] language: "%s", message "%s" loader error', $language, $string)
            );
        }
        return $message;
    }


    /**
     * Get current language
     *
     * @param bool $force force get language
     *
     * @return string $language language code
     */
    public function getLanguage($force = false)
    {
        if (empty($this->language) || $force) {
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

    /**
     * Get best matching locale code based on options
     *
     * @param array $languages detected locales
     *
     * @return mixed
     */
    protected function getBestMatchingLanguage(array $languages)
    {
        if (!empty($this->options['available']) && !empty($languages)) {
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

    /**
     * Returns short (2-3 characters) locale code
     *
     * @param string $language long locale code (ex. en-US, zh_HKG, de-CH, etc.)
     *
     * @return string
     */
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

    /**
     * Detect acceptable locales by accept-language browser header.
     *
     * @param string $acceptLanguage accept-language browser header
     * @param int    $resolution     resolution of locale qualities
     *
     * @return array
     */
    protected function detectLanguages($acceptLanguage, $resolution = 100)
    {
        $tags = array_map('trim', explode(',', $acceptLanguage));
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
     * @param string     $string string to translate.
     * @param array|null $args   vsprintf with these arguments will be used if set.
     *
     * @return string translated string.
     */
    public function t($string, $args = null)
    {
        $original = $string;
        $string   = $this->getMessage($this->language, $string);
        if (true === is_array($string)) {
            return $this->plural($original, 1, $args);
        }
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
     * @param int    $x      plural variable.
     * @param array  $args   vsprintf with these arguments will be used
     *                       if set (optional).
     *
     * @return string translated string.
     */
    public function plural($string, $x, $args = null)
    {
        $string  = $this->getMessage($this->language, $string);
        $choices = $string;
        if (false === is_array($string)) {
            $choices = explode('|', $string);
        }
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
     * https://github.com/zendframework/zf1/blob/master/library/Zend/Translate/Plural.php
     *
     * @param string $locale language code
     * @param int    $x      plural variable
     *
     * @return integer index of plural form rule.
     */
    protected function pluralRule($locale, $x)
    {
        switch ($locale) {
            case 'az':
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
                $index = 0;
                break;

            case 'af':
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
                $index = ($x == 1) ? 0 : 1;
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
                $index = (($x == 0) || ($x == 1)) ? 0 : 1;
                break;

            case 'be':
            case 'bs':
            case 'hr':
            case 'ru':
            case 'sr':
            case 'uk':
                $index = (
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
                $index = ($x == 1) ? 0 : ((($x >= 2) && ($x <= 4)) ? 1 : 2);
                break;

            case 'ga':
                $index = ($x == 1) ? 0 : (($x == 2) ? 1 : 2);
                break;

            case 'lt':
                $index = (
                    ($x % 10 == 1) && ($x % 100 != 11)
                ) ? (
                    0
                ) : (
                    (($x % 10 >= 2) && (($x % 100 < 10) || ($x % 100 >= 20))) ? 1 : 2
                );
                break;

            case 'sl':
                $index = (
                    $x % 100 == 1
                ) ? (
                    0
                ) : (
                    ($x % 100 == 2) ? 1 : ((($x % 100 == 3) || ($x % 100 == 4)) ? 2 : 3)
                );
                break;

            case 'mk':
                $index = ($x % 10 == 1) ? 0 : 1;
                break;

            case 'mt':
                $index = (
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
                $index = ($x == 0) ? 0 : ((($x % 10 == 1) && ($x % 100 != 11)) ? 1 : 2);
                break;

            case 'pl':
                $index = (
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
                $index = (
                    $x == 1
                ) ? (
                    0
                ) : (($x == 2) ? 1 : ((($x == 8) || ($x == 11)) ? 2 : 3));
                break;

            case 'ro':
                $index = (
                    $x == 1
                ) ? (
                    0
                ) : ((($x == 0) || (($x % 100 > 0) && ($x % 100 < 20))) ? 1 : 2);
                break;

            case 'ar':
                $index = (
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
                $index = 0;
                break;
        }
        return $index;
    }
}
