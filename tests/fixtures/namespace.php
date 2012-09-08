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
        /** @something */
        public function something() {}
    }

    function xxx() {
    }
}

