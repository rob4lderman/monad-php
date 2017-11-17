<?php

namespace MonadPHP;

class StackMonad extends Monad {

    const unit = "MonadPHP\StackMonad::unit";

    protected function runCallback($function, $value, array $args = array()) {

        //
        // If a bound function ever returns a monad, it will be stored in 
        // this monad's $value object.  This effectively converts this monad
        // from a StackMonad to the returned monad.  We accomplish this
        // by checking here and calling bind on the previously returned monad, 
        // instead of running the StackMonad logic.
        //
        if ($value instanceof Monad) {
            return $value->bind($function, $args);
        }

        $symbols = isset( $args[0] ) ? $args[0] : [];
        $rv_symbol = isset( $args[1] ) ? $args[1] : NULL;
        return $this->stackBind( $function, $symbols, $rv_symbol );
    }

    /**
     * Used internall by stackPopFn and stackBindFn.
     *
     * Calls the given $fn, passing the given $arg_symbols as pulled from the given $stack.
     * i.e. $fn( $stack[ $arg_symbols[0] ], $stack[ $arg_symbols[1] ], ... )
     *
     * @return the rv from $fn
     */
    public function stackBindHelper( $stack, $fn, $arg_symbols ) {
        switch ( count($arg_symbols) ) {
        case 0: return $fn( $stack );
        case 1: return $fn( $stack[ $arg_symbols[0] ] );
        case 2: return $fn( $stack[ $arg_symbols[0] ], $stack[ $arg_symbols[1] ] );
        case 3: return $fn( $stack[ $arg_symbols[0] ], $stack[ $arg_symbols[1] ], $stack[ $arg_symbols[2] ] );
        case 4: return $fn( $stack[ $arg_symbols[0] ], $stack[ $arg_symbols[1] ], $stack[ $arg_symbols[2] ], $stack[ $arg_symbols[3] ] );
        case 5: return $fn( $stack[ $arg_symbols[0] ], $stack[ $arg_symbols[1] ], $stack[ $arg_symbols[2] ], $stack[ $arg_symbols[3] ], $stack[ $arg_symbols[4] ] );
        default: throw new Exception('Too many args');
        }
    }

    /**
     * @return a function that takes a $stack (stack monad) and calls the given $fn,
     *         passing in the given $arg_symbols from the $stack.
     *         The rv from $fn is then added to $stack at $rv_symbol.
     *         The function returns the $stack.
     */
    public function stackBind( $fn, $arg_symbols, $rv_symbol = NULL) {
        $rv = $this->stackBindHelper( $this->value, $fn, $arg_symbols );

        //
        // If the bound function returns a Monad, then return the Monad.
        // This will place it in the $value object of the next StackMonad
        // that's created.
        //
        if ( $rv instanceof Monad ) {
            return $rv;
        }

        if ( !!! empty( $rv_symbol ) ) {
            $this->value[ $rv_symbol ] = $rv;
        }

        return $this->value;
    }


}
