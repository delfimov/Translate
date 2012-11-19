<?php
/**
 * Translate is a part of Nanobanano framework
 *
 * PHP version 5
 *
 * @copyright 2012 Dmitry Elfimov
 * @license   http://www.elfimov.ru/nanobanano/license.txt MIT License
 * @link      http://elfimov.ru/nanobanano
 * 
 * Usage example:
 * $t = new t('/home/www/site', null, 'en', array('ru', 'en', 'de'), array('help.php', 'info.php'));
 * $message = $t('The %s contains a monkey', $location);
 *
 * $t = new t(null, null, 'en', array('ru', 'en', 'de'));
 * $message = $t->t('The %s contains a monkey', $location, $num);
 * $message = $t->choice('The %s contains %d monkey|The %s contains %d monkeys', $num, $location, $num);
 *
 */
 
/*
 * Translate class
 *
 * @package Translate
 * @author  Dmitry Elfimov <elfimov@gmail.com>
 *
 */
 
class Translate
{
    
    public $language = '';
    
    // this language will be used and loaded if we cannot find proper language files
    private $_default = 'en';
    
    // path to language files
    private $_path = null;
    
    // available languages
    private $_available = null; 

    private $_messages = array();
    private $_extra = array();
    
    private $_synonyms = array(
        // uk and us are synonyms for en. 
        // if HTTP_ACCEPT_LANGUAGE is set to 'uk' or 'us' 
        // then 'en' will be used instead.
        'en' => 'uk', 'us',
    );
    
    /**
     * Constructor.
     *
     * @param string $path            Path to language files. 
     *                                By default is "messages" subdirectory 
     *                                in the library class directory.
     * @param string $userLanguage    User's language. 
     *                                If omitted then the best matching value 
     *                                from Accept-Language header will be used.
     * @param string $defaultLanguage Default language (language for t() method)
     * @param array  $available       Available languages. if not set or empty 
     *                                then any language will be accepted.
     * @param array  $extra           Extra language files in language directory. 
     *                                By default only messages.php will be loaded.
     * @param array  $synonyms        Synonims for language codes 
     *
     * @return no value is returned.
     */
    public function __construct(
        $path = null, 
        $userLanguage = null, 
        $defaultLanguage = null, 
        $available = null, 
        $extra = null, 
        $synonyms = null
    ) {
        $this->_path = (isset($path)) ? $path : dirname(__FILE__).'/messages';
        $this->_available = (isset($available)) ? $available : $this->_available;
        $this->_extra = (isset($extra)) ? $extra : $this->_extra;
        $this->_synonyms = (isset($synonyms)) ? $synonyms : $this->_synonyms;
        $this->_default = (isset($defaultLanguage)) ? $defaultLanguage : $this->_default;
        $this->setLanguage($userLanguage);
    }
    
    /**
     * Set user language.
     *
     * @param string $language user's language. 
     *
     * @return no value is returned.
     */
    public function setLanguage($language = null)
    {
        if (empty($language)) {
            $language = $this->_getLanguage();
        }
        if (!in_array($language, $this->_available)) {
            $language = $this->_default;
        }
        $this->_loadMessages($language);
        $this->language = $language;
    }

    /**
     * Loads translation files.
     *
     * @param string $language language to load. 
     *
     * @return no value is returned.
     */
    private function _loadMessages($language)
    {
        if (!isset($this->_messages[$language])) {
            $messages = array();
            if (file_exists($this->_path.'/'.$language.'/messages.php')) {
                $messages = include $this->_path.'/'.$language.'/messages.php';
            }
            foreach ($this->_extra as $filename) {
                if (file_exists($this->_path.'/'.$language.'/'.$filename.'.php')) {
                    $extra = include $this->_path.'/'.$language.'/'.$filename.'.php';
                    $messages = $extra + $messages;
                }
            }
            $this->_messages[$language] = $messages;
        }
    }
    
    /**
     * Gets user's accept laguage and check if it in available languages list.
     *
     * @todo get 2 or 3 letter language codes
     *
     * @return 2 letter language code.
     */
    private function _getLanguage()
    {
        $langs = explode(';', str_replace(' ', '', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'])));
        $max = -1;
        foreach ($langs as $key => $lang) {
            if ($lang!='') {
                $lang = explode(',', $lang);
                $short = array();
                $long = '';
                $q = 1;
                foreach ($lang as $value) {
                    if (strlen($value)==2) {
                        $short[] = $value;
                    } else if ($value{0}=='q' && $value{1}=='=') {
                        $q = substr($value, 2);
                    } else {
                        $pos = strrchr($value, '_');
                        if ($pos!==false) {
                            $short[] = substr($value, 0, -strlen($pos));
                        } else {
                            $short[] = $value{0}.$value{1};
                        }
                    }
                }
                foreach ($short as $shortValue) {
                    if (empty($this->_available) || in_array($shortValue, $this->_available)) {
                        if ($max<$q) {
                            $max = $q;
                            $langMax = $shortValue;
                        }
                        $last = $shortValue;
                        if ($q==1) {
                            break 2;
                        }
                    }
                }
            }
        }
        
        $language = isset($langMax) ? $langMax : (isset($last) ? $last : false);

        if ($language!==false && ($replace = array_search($language, $this->_synonyms))!==false) {
            $language = $replace;
        }

        return $language;
            
    }

    /**
     * Loads translation files.
     *
     * @param string $string string to translate.
     * @param array  $args   vsprintf with these arguments will be used if set.
     *
     * @return translated string.
     */
    public function t($string, $args=null)
    {
        if (!empty($this->_messages[$this->language][$string])) {
            $string = $this->_messages[$this->language][$string];
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
     * Wrapper for this->t() (php >= 5.3)
     *
     * @param string $string string to translate.
     * @param array  $args   vsprintf with these arguments will be used if set.
     *
     * @return translated string.
     */
    public function __invoke($string, $args=null)
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
     * @param string $string string to translate devided with "|" character.
     * @param string $x      plural variable.
     * @param array  $args   vsprintf with these arguments will be used if set (optional).
     *
     * @return translated string.
     */
    public function choice($string, $x, $args=null)
    {
        if (!empty($this->_messages[$this->language][$string])) {
            $string = $this->_messages[$this->language][$string];
        }
        $choices = explode('|', $string);
        $args = isset($args) ? $args : array($x);
        if (isset($args)) {
            if (!is_array($args)) {
                $args = array_slice(func_get_args(), 2);
            }
        } else {
            $args = array_fill(0, count($choices), $x);
        }
        $plural = $this->_pluralChoice($x);
        $string = isset($choices[$plural]) ? $choices[$plural] : $choices[0];
        return vsprintf($choices[$this->_pluralChoice($x)], $args);
    }
    
    
    /**
     * The plural rules are derived from code of the Zend Framework (2010-09-25),
     * which is subject to the new BSD license (http://framework.zend.com/license/new-bsd).
     * Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
     *
     * @param string $x plural variable.
     *
     * @return index of plural form.
     */
    private function _pluralChoice($x)
    {
        switch ($this->language) {
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

        case 'be':
        case 'bs':
        case 'hr':
        case 'ru':
        case 'sr':
        case 'uk':
            return (($x % 10 == 1) && ($x % 100 != 11)) ? 0 : ((($x % 10 >= 2) && ($x % 10 <= 4) && (($x % 100 < 10) || ($x % 100 >= 20))) ? 1 : 2);

        case 'cs':
        case 'sk':
            return ($x == 1) ? 0 : ((($x >= 2) && ($x <= 4)) ? 1 : 2);

        case 'ga':
            return ($x == 1) ? 0 : (($x == 2) ? 1 : 2);

        case 'lt':
            return (($x % 10 == 1) && ($x % 100 != 11)) ? 0 : ((($x % 10 >= 2) && (($x % 100 < 10) || ($x % 100 >= 20))) ? 1 : 2);

        case 'sl':
            return ($x % 100 == 1) ? 0 : (($x % 100 == 2) ? 1 : ((($x % 100 == 3) || ($x % 100 == 4)) ? 2 : 3));

        case 'mk':
            return ($x % 10 == 1) ? 0 : 1;

        case 'mt':
            return ($x == 1) ? 0 : ((($x == 0) || (($x % 100 > 1) && ($x % 100 < 11))) ? 1 : ((($x % 100 > 10) && ($x % 100 < 20)) ? 2 : 3));

        case 'lv':
            return ($x == 0) ? 0 : ((($x % 10 == 1) && ($x % 100 != 11)) ? 1 : 2);

        case 'pl':
            return ($x == 1) ? 0 : ((($x % 10 >= 2) && ($x % 10 <= 4) && (($x % 100 < 12) || ($x % 100 > 14))) ? 1 : 2);

        case 'cy':
            return ($x == 1) ? 0 : (($x == 2) ? 1 : ((($x == 8) || ($x == 11)) ? 2 : 3));

        case 'ro':
            return ($x == 1) ? 0 : ((($x == 0) || (($x % 100 > 0) && ($x % 100 < 20))) ? 1 : 2);

        case 'ar':
            return ($x == 0) ? 0 : (($x == 1) ? 1 : (($x == 2) ? 2 : ((($x >= 3) && ($x <= 10)) ? 3 : ((($x >= 11) && ($x <= 99)) ? 4 : 5))));

        default:
            return 0;
        }
    }
    
}