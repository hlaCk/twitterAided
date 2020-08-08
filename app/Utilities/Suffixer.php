<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 7/11/2019
 * Time: 2:44 PM
 */

namespace App\Utilities;


class Suffixer {
    public $string = null;
    public $suffixDelimiter = '::';

    public function __construct($string = null, $delimiter = '::') {
        $this->update(...func_get_args());
    }

    public function update($string = null, $delimiter = '::') {
        if(func_num_args() === 2)
            $this->suffixDelimiter = is_null($delimiter) ? '' : $delimiter;

        if(func_num_args() > 0)
            $this->string = is_null($string) ? $this->string : $string;

        return $this;
    }

    /**
     * Returns Controller name as string, add method name if sent.
     *
     * @param string|null $method
     *
     * @return string
     */
    public function get($method = '') {
        return str_suffix($this->string, $this->suffixDelimiter, $method);
    }

    public static function make($string) {
        return $string instanceof Suffixer ?
                    $string->update(...func_get_args()) :
                    new static(...func_get_args());
    }

    /**
     * @param mixed ...$parameters
     * @return \Closure
     */
    public static function makeer(...$parameters) {
        return function ($string) use (&$parameters) {
            return new Suffixer($string, ...$parameters);
        };
    }

    public function __toString() {
        return $this->get();
    }

    public function __get($name) {
        return $this->get($name);
    }
}
