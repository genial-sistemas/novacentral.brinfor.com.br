<?php

if (!function_exists('htmlDataJsonEncode')) {
    function htmlDataJsonEncode($data) {
        return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    }
}
