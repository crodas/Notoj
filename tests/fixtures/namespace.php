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

    /** @foobar_interface */
    interface foobar_interface {
    }

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

