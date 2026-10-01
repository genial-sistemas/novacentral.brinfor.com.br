<?php
require_once __DIR__ . '/typeHelpers.php';

/**
 * strWordsTruncated function
 *
 * @param string $strVal
 * @param integer $qtdWords
 * @param string $suffixStr
 * @return string
 */
function strWordsTruncated($strVal='', $qtdWords=1, $separator=' ', $suffixStr='') {
    if (!isValidString($strVal) || !isValidInteger($qtdWords)) { return ''; }
    if (!isValidString($separator)) { $separator = ' '; }

    $strOut = '';
    
    $strArr = explode($separator, $strVal);
    if (isValidArray($strArr) && \count($strArr) > $qtdWords) {
        for ($i=0 ; $i < ($qtdWords - 1) ; $i++) { 
            $strOut .= $strArr[$i];
        }
    } else {
        $strOut = $strVal;
    }

    return $strOut;
}