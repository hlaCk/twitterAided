<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 27/10/2019
 * Time: 11:27 AM
 */

namespace App\Utilities;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class ArrayAndObject extends \ArrayObject implements Arrayable, Jsonable, \JsonSerializable {
    const DELETE_KEY    = 0x001;
    const SET_KEY       = 0x002;
    const SET_VALUE     = 0x003;

    public function &__get($name)
    {
        return $this->offsetGet($name);
    }
    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }
    public function __isset($name)
    {
        return $this->offsetExists($name);
    }
    public function __unset($name)
    {
        $this->offsetUnset($name);
    }

    public function __construct($input = array())
    {
        parent::__construct($input instanceof Arrayable ? $input->toArray() : $input, \ArrayObject::STD_PROP_LIST|\ArrayObject::ARRAY_AS_PROPS);
        return $this;
    }

    public function export()
    {
        return $this->objectToArray($this->getArrayCopy());
    }

    public function objectToArray ($object) {
        $o = [];
        foreach ($object as $key => $value) {
            $o[$key] = is_object($value) ? (array) $value: $value;
        }
        return $o;
    }
    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->export();
    }

    public function all()
    {
        return $this->getArrayCopy();
    }
    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource.
     *
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }


    /**
     * Execute a callback over each item .
     *
     * @param  callable  $callback
     *
     * @return mixed
     */
    public function each(callable $callback)
    {
        $old_this = $this->toArray();
        foreach ($old_this as $key => $value) {
            $old_key = $key;
            $return = $callback($value, $key);
            if($return)
                $value = $return;

            if($key != $old_key) {
                unset($old_this[$old_key]);
            }
            $old_this[$key] = $value;
        }
        $this->exchangeArray($old_this);
        return $this;
    }

    /**
     * Change array row, just use it like:
     *      $ArrayAndObject->whenHasKey('KEY', function (&$value, &$key) {
     *          $value = 'NEW VALUE';
     *          $key = 'NEW KEY';
     *          // @ var $this ArrayAndObject
     *          return $this;
     *      })
     *
     *
     * @param string|int                $key
     * @param callable|ArrayAndObject   $callable
     *
     * @return $this|mixed Or What ever your $callable returns
     */
    public function whenHasKey($key, callable $callable) {
        if(array_key_exists($key, $this->toArray())) {
            if(is_callable($callable)) {
                $old_key = $key;
                $return = \Closure::fromCallable($callable)->bindTo($this)( $this[$key], $key );
                if($old_key != $key) {
                    $this[$key] = $this[$old_key];
                    unset($this[$old_key]);
                }
                return $return;
            }
        }

        return $this;
    }

    protected $change_flage = [
        'current'=>ArrayAndObject::SET_VALUE,
        'before'=>null
    ];
    public function setFlag($flag)
    {
        $this->change_flage['before'] = $this->change_flage['current'];
        $this->change_flage['current'] = $flag;
        return $this;
    }
    public function oldFlag()
    {
        if(!is_null($this->change_flage['before'])) {
            $this->change_flage['current'] = $this->change_flage['before'];
            $this->change_flage['before'] = null;
        }
        return $this;
    }

    public function setAsFlagged($key, $value = null, $flag = self::SET_VALUE)
    {
        $this->setFlag($flag);
        $this_key = $key;
        switch ($this->change_flage['current']) {
            case self::SET_KEY:
                $this[$value] = $this[$this_key];
                if($this_key != $value)
                    unset($this[$this_key]);

                break;

            case self::SET_VALUE:
                $this[$key] = $value;
                break;

            case self::DELETE_KEY:
                unset($this[$key]);
                break;
        }
        $this->oldFlag();

        return $this;
    }

    public static function from($array, callable $each = null) {
        $_array = new static($array);
        return $each && is_callable($each) ? $_array->each($each) : $_array;
    }
    public static function fromJson($string, callable $each = null) {
        return static::from(json_decode($string, true), $each);
    }
}
