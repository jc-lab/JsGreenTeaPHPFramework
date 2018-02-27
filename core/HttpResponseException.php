<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   HttpResponseException
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2018/02/22
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\core;

use Throwable;

class HttpResponseException extends \Exception
{
    public $response;

    public function __construct($response)
    {
        $previous = null;
        parent::__construct(HttpStatus::getString($response->getStatus()), $response->getStatus(), $previous);
    }
}