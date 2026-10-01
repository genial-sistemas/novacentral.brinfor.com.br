<?php

/**
 * isValidInteger function
 *
 * @param mixed $value
 * @return boolean
 */
function isValidInteger($value) {
    try {
        if (isset($value) && is_numeric($value)) {
            if (is_string($value)) {
                $value = intval($value);
            }
            return is_int($value);
        }
        return false;
        
    } catch (\Throwable $th) {
        //throw $th;
        return false;
    }
}

/**
 * isValidFloat function
 *
 * @param mixed $value
 * @return boolean
 */
function isValidFloat($value) {
    try {
        return isset($value) && is_numeric($value) && is_float($value);
    } catch (\Throwable $th) {
        //throw $th;
        return false;
    }
}

function isValidString($value) {
    try {
        return isset($value) && is_string($value) && strlen($value) > 0;
    } catch (\Throwable $th) {
        //throw $th;
        return false;
    }
}

function isInvalidString($value) {
    return !isValidString($value);
}

function isValidArray($value) {
    try {
        return isset($value) && is_array($value) && count($value) > 0;
    } catch (\Throwable $th) {
        //throw $th;
        return false;
    }
}

function isInvalidArray($value) {
    return !isValidArray($value);
}

function isValidEmail($str) {
    if (!isValidString($str)) { return false; }
    $rx = "/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix";
    return (!preg_match($rx, $str)) ? false : true;
}

function getSafeArrayData($key, $arrData, $default=null) {
    try {
        return (isValidString($key) || isValidInteger($key))
                && isValidArray($arrData) 
                && array_key_exists($key, $arrData)
            ? $arrData[$key]
            : $default;
    } catch (\Throwable $th) {
        //throw $th;
        return $default;
    }
    
}
