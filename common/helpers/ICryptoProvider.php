<?php

namespace common\helpers;


interface ICryptoProvider {

    public function getRandomHexString($length);
    public function hash($data, $secret);

}