<?php

namespace common\helpers;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'ICryptoProvider.php';

class CryptoProvider implements ICryptoProvider {

    public function getRandomHexString($length) {
        try {
            return $this->getRandomHexStringFromDevRandom($length);
        } catch (\RuntimeException $e) {
//            trigger_error($e->getMessage() . ' Falling back to internal generator.', E_USER_NOTICE);
            return $this->getRandomHexStringFromMtRand($length);
        }
    }

    protected function getRandomHexStringFromDevRandom($length) {
        static $sources = array('/dev/urandom', '/dev/random');

        foreach ($sources as $source) {
            if (@is_readable($source)) {
                return bin2hex(file_get_contents($source, false, null, -1, $length / 2));
            }
        }

        throw new \RuntimeException('No system source for randomness available.');
    }

    protected function getRandomHexStringFromMtRand($len = 10, $case = false, $table = "0123456789abcedefghjikmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ") {
        $temp = "";
        $counter = 0;

        for ($x = 0; $x < $len; $x++) {
            $counter++;
            if (!$case) {
                $temp .= substr($table, rand(0, strlen($table)), 1);
            } else {
                $char = substr($table, rand(0, strlen($table)), 1);
                $temp .= ((rand(0, 3) == 0) ? strtoupper($char) : $char);
            }
        }
        if (trim(strlen($temp)) == $len) { // sometimes this returns strlen=7 only
//                echo "counter=$counter";
            return $temp;
        }
        return $temp;
    }

//    protected function getRandomHexStringFromMtRand($length) {
//        $hex = null;
//        for ($i = 0; $i < $length; $i++) {
//            $hex .= base_convert(mt_rand(0, 15), 10, 16);
//        }
//        return $hex;
//    }

    public function hash($data, $secret) {
//        return crypt($data, $secret);
//        return password_hash($data, PASSWORD_BCRYPT);
        return hash_hmac('sha512', $data, $secret);
    }

}
