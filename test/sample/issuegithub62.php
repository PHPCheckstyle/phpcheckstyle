<?php

/**
 * customized Exception class for CouchDB errors
 */
class CouchException extends Exception {

    // CouchDB response codes we handle specialized exceptions
    protected static $codeSubtypes = [
        401 => 'CouchUnauthorizedException',
        403 => 'CouchForbiddenException',
        404 => 'CouchNotFoundException',
        417 => 'CouchExpectationException'
    ];

}
