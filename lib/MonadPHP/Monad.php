<?php

namespace MonadPHP;

abstract class Monad {

    protected $value;

    public function __construct($value) {
        $this->value = $value;
    }

    public static function unit($value) {
        if ($value instanceof static) {
            return $value;
        }
        return new static($value);
    }

    // gen's strict compiler warning: https://github.com/ircmaxell/monad-php/pull/4
    // public function bind($function, array $args = array()) {
    //     return $this::unit($this->runCallback($function, $this->value, $args));
    // }

    public function __call($name, $arguments) {
        if ($name == 'bind') {
            $function = array_shift($arguments);
            $args = empty($arguments) ? array() : array_shift($arguments);
            return $this::unit($this->runCallback($function, $this->value, $args));
        }
        throw new \BadMethodCallException('Call to undefined method '.$name);
    }

    public function extract() {
        if ($this->value instanceof self) {
            return $this->value->extract();
        }
        return $this->value;
    }

    protected function runCallback($function, $value, array $args = array()) {
        if ($value instanceof self) {
            return $value->bind($function, $args);
        }
        array_unshift($args, $value);
        return call_user_func_array($function, $args);
    }

}
