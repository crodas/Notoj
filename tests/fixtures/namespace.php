<?php

namespace foo\name {
    /** @fooinvalid */
    /** @foobar */
    class foobar {
    }

    /** @xxx */
    function xxx() {
    }
}

namespace {

    /** @foobar */
    final class foobar {
        /** @fooba */
        public $fooba;

        /** @something */
        static protected function something() {}
    }

    function xxx() {
    }
}

