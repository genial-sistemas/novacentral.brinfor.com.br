<?php
require_once 'typeHelpers.php';

function arrayHasKey(?array $arr = null, ?string $key): bool
{
    try {
        if (empty($arr) || empty($key)) { return false; }
        return array_key_exists($key, $arr);
    } catch (\Throwable $th) {
        //throw $th;
        return false;
    }
}

function arrayHasKeys($arr, ?array $keyList): bool
{
    if (! isValidArray($arr) || ! isValidArray($keyList)) { return false; }

    $arrKeys = array_keys($arr);
    if (!isValidArray($arrKeys)) { return false; }

    $res = true;
    foreach ($keyList as $key) {
        if (! empty($key) && ! in_array($key, $arrKeys)) {
            return false;
        }
    }

    return $res;
}

function arrayNotIncludeKeys(array $arr, array $keyList): bool
{
    return ! arrayHasKeys($arr, $keyList);
}

function arrayGet($arr = [], string $key = null, mixed $default = null): mixed
{
    if (
        ! isValidArray($arr)
        || (! isValidString($key) && ! isValidInteger($key))
        || ! arrayHasKey($arr, $key)
    ) {
        return $default;
    }

    $value = $arr[$key];

    if (
        ! is_numeric($value)
        && empty($value)
    ) {
        return $default;
    }

    try {
        return $value;
    } catch (\Throwable $th) {
        //throw $th;
        return $default;
    }
}

function getSafeArrayKeyValue($arr, $key, $default = null): mixed
{
    return arrayGet($arr, $key, $default);
}
