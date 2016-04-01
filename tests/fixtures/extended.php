<?php

/** @XX */
class xxyyzz
{
}

/**  @weird_alone */
function weirdo() {
}

/** 
 * @Foobar(@Foobar('foobar'), 'foobar') 
 * @Long    this is a very long long long text 
 *          and perhaps
 *          we are talking
 *
 *          It supports multiple paragraphs as well.
 *
 *          More and more texts
 *  @Short  hi there!
 */
class extended extends xxyyzz
{
    /** @inline this is foo */
    function yyy() {}
}

class parentxx extends extended
{
}

/** @Foo */
class xasdasda extends parentxx
{
}

/** @callable */
function something()
{
    return 1;
}

class somethingx
{
    /** @callable_method */
    public function something()
    {
        return 2;
    }

    /** @callable_method_static */
    public static function xxsomething()
    {
        return 3;
    }
}
