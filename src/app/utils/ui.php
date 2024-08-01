<?php
function avatarUrl($username)
{
    $firstChar = strtoupper(substr($username, 0, 1));
    $background = '';
    $textColor = '';

    if ($firstChar >= 'A' && $firstChar <= 'G') {
        $background = 'E2E2FD';
        $textColor = '6366F1';
    } elseif ($firstChar >= 'H' && $firstChar <= 'N') {
        $background = 'F8ECC7';
        $textColor = 'F5B800';
    } elseif ($firstChar >= 'O' && $firstChar <= 'U') {
        $background = 'E6FAF5';
        $textColor = '00CC99';
    } elseif ($firstChar >= 'V' && $firstChar <= 'Z') {
        $background = 'F1E6FB';
        $textColor = '6F05D6';
    } else {
        // Handle non-alphabetic characters or other cases
        $background = 'F1E6FB';
        $textColor = '6F05D6';
    }

    return "https://api.dicebear.com/5.x/initials/svg?seed={$username}&backgroundColor={$background}&textColor={$textColor}&fontSize=38";
}
