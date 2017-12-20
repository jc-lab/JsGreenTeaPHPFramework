<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   HttpStatus
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/14
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework;

class HttpStatus
{
    const HTTP_CONTINUE = 100;
    const HTTP_SWITCHING_PROTOCOLS = 101;
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    const HTTP_NO_CONTENT = 204;
    const HTTP_RESET_CONTENT = 205;
    const HTTP_PARTIAL_CONTENT = 206;
    const HTTP_MULTIPLE_CHOICES = 300;
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_MOVED_TEMPORARILY = 302;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_USE_PROXY = 305;
    const HTTP_TEMPORARY_REDIRECT = 307;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_PAYMENT_REQUIRED = 402;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_REQUEST_TIMEOUT = 408;
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    const HTTP_LENGTH_REQUIRED = 411;
    const HTTP_PRECONDITION_FAILED = 412;
    const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_REQUEST_URI_TOO_LONG = 414;
    const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const HTTP_EXPECTATION_FAILED = 417;
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    const HTTP_GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;

    public static function getString($httpcode)
    {
        switch($httpcode)
        {
            case self::HTTP_CONTINUE:
                return "Continue";
            case self::HTTP_SWITCHING_PROTOCOLS:
                return "HTTP_SWITCHING_PROTOCOLS";
            case self::HTTP_OK:
                return "OK";
            case self::HTTP_CREATED:
                return "";
            case self::HTTP_ACCEPTED:
                return "ACCEPTED";
            case self::HTTP_NON_AUTHORITATIVE_INFORMATION:
                return "HTTP_NON_AUTHORITATIVE_INFORMATION";
            case self::HTTP_NO_CONTENT:
                return "HTTP_NO_CONTENT";
            case self::HTTP_RESET_CONTENT:
                return "HTTP_RESET_CONTENT";
            case self::HTTP_PARTIAL_CONTENT:
                return "HTTP_PARTIAL_CONTENT";
            case self::HTTP_MULTIPLE_CHOICES:
                return "HTTP_MULTIPLE_CHOICES";
            case self::HTTP_MOVED_PERMANENTLY:
                return "HTTP_MOVED_PERMANENTLY";
            case self::HTTP_MOVED_TEMPORARILY:
                return "HTTP_MOVED_TEMPORARILY";
            case self::HTTP_FOUND:
                return "Found";
            case self::HTTP_SEE_OTHER:
                return "See Other";
            case self::HTTP_NOT_MODIFIED:
                return "Not modified";
            case self::HTTP_USE_PROXY:
                return "USe proxy";
            case self::HTTP_TEMPORARY_REDIRECT:
                return "TEMPORARY REDIRECT";
            case self::HTTP_BAD_REQUEST:
                return "Bad request";
            case self::HTTP_UNAUTHORIZED:
                return "Unauthorized";
            case self::HTTP_PAYMENT_REQUIRED:
                return "Payment required";
            case self::HTTP_FORBIDDEN:
                return "Forbidden";
            case self::HTTP_NOT_FOUND:
                return "Not found";
            case self::HTTP_METHOD_NOT_ALLOWED:
                return "Method Not Allowed";
            case self::HTTP_NOT_ACCEPTABLE:
                return "Not Acceptable";
            case self::HTTP_PROXY_AUTHENTICATION_REQUIRED:
                return "PROXY AUTHENTICATION REQUIRED";
            case self::HTTP_REQUEST_TIMEOUT:
                return "Request Timeout";
            case self::HTTP_CONFLICT:
                return "Conflict";
            case self::HTTP_GONE:
                return "Gone";
            case self::HTTP_LENGTH_REQUIRED:
                return "Length required";
            case self::HTTP_PRECONDITION_FAILED:
                return "Precondition failed";
            case self::HTTP_REQUEST_ENTITY_TOO_LARGE:
                return "Request entity too large";
            case self::HTTP_REQUEST_URI_TOO_LONG:
                return "Request URI too long";
            case self::HTTP_UNSUPPORTED_MEDIA_TYPE:
                return "Unsupported media type";
            case self::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE:
                return "Requested range not satisfiable";
            case self::HTTP_EXPECTATION_FAILED:
                return "Expectation failed";
            case self::HTTP_INTERNAL_SERVER_ERROR:
                return "Internal Server Error";
            case self::HTTP_NOT_IMPLEMENTED:
                return "Not Implemented";
            case self::HTTP_BAD_GATEWAY:
                return "Bad Gateway";
            case self::HTTP_SERVICE_UNAVAILABLE:
                return "Service Unavailable";
            case self::HTTP_GATEWAY_TIMEOUT:
                return "Gateway Timeout";
            case self::HTTP_VERSION_NOT_SUPPORTED:
                return "Version Not Supported";
            default:
                return "Undefined";
        }
    }
};
