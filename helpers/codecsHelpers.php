<?php

function htmlDataJsonEncode($value, $specialvars=false) {
    try {
        // return htmlspecialchars(json_encode($value), ENT_QUOTES, 'UTF-8');
        $value = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($specialvars) { $value  = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'); }
        return $value ? $value : '';
    } catch (\Throwable $th) {
        //throw $th;
        return '';
    }
}