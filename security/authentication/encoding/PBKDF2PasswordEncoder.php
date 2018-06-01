<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   PBKDF2PasswordEncoder
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/27
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\security\authentication\encoding;

class PBKDF2PasswordEncoder extends BasePasswordEncoder implements PasswordEncoder
{
    private $m_algorithm = "sha256";
    private $m_hashlength;
    private $m_iterations;
    private $m_secret;
    private $m_saltlength;

    public function __construct($secret = "", $iterations = 1000, $hashLength = 32, $saltLength = 8)
    {
        $this->m_secret = $secret;
        $this->m_iterations = intval($iterations);
        $this->m_hashlength = intval($hashLength);
        $this->m_saltlength = intval($saltLength);
    }

    public function setAlgorithm($algo)
    {
        $this->m_algorithm = $algo;
    }

    public function setIterations($iterations)
    {
        $this->m_iterations = intval($iterations);
    }

    public function setHashLength($value)
    {
        $this->m_hashlength = intval($value);
    }

    public function setSaltLength($value)
    {
        $this->m_saltlength = intal($value);
    }

    private function _realEncode($algorithm, $secret, $salt, $iterations, $hashlength, $rawPassword)
    {
        if(strlen($secret) > 0)
            $realsalt = $salt.$secret;
        else
            $realsalt = $salt;

        if(!in_array($algorithm, hash_algos(), true))
            trigger_error('PBKDF2 ERROR: Invalid hash algorithm.', E_USER_ERROR);
        if($iterations <= 0 || $hashlength <= 0)
            trigger_error('PBKDF2 ERROR: Invalid parameters.', E_USER_ERROR);

        if (function_exists("hash_pbkdf2")) {
            return hash_pbkdf2($algorithm, $rawPassword, $realsalt, $iterations, $hashlength, true);
        }

        $hash_length = strlen(hash($algorithm, "", true));
        $block_count = ceil($hashlength / $hash_length);

        $output = "";
        for($i = 1; $i <= $block_count; $i++) {
            // $i encoded as 4 bytes, big endian.
            $last = $realsalt . pack("N", $i);
            // first iteration
            $last = $xorsum = hash_hmac($algorithm, $last, $rawPassword, true);
            // perform the other $count - 1 iterations
            for ($j = 1; $j < $iterations; $j++) {
                $xorsum ^= ($last = hash_hmac($algorithm, $last, $rawPassword, true));
            }
            $output .= $xorsum;
        }

        return substr($output, 0, $hashlength);
    }

    public function encode($rawPassword)
    {
        $algorithm = strtolower($this->m_algorithm);
        $saltprefix = self::getSaltPrefix($algorithm).pack('v', $this->m_iterations);
        $salt = $saltprefix.openssl_random_pseudo_bytes($this->m_saltlength - strlen($saltprefix));
        if(strlen($this->m_secret) > 0)
            $realsalt = $salt.$this->m_secret;
        else
            $realsalt = $salt;
        return $salt.self::_realEncode($this->m_algorithm, $this->m_secret, $salt, $this->m_iterations, $this->m_hashlength, $rawPassword);
    }

    public function matches($rawPassword, $encodedPassword)
    {
        $encoded_salt = substr($encodedPassword, 0, $this->m_saltlength);
        $encoded_hash = substr($encodedPassword, $this->m_saltlength);
        $computedPassword = $this->_realEncode($this->m_algorithm, $this->m_secret, $encoded_salt, $this->m_iterations, $this->m_hashlength, $rawPassword);
        if(($computedPassword === false) || ($computedPassword === NULL))
            return false;
        return ($computedPassword == $encoded_hash);
    }
}