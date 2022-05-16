<?php

function join_paths(string ...$parts): string
{
    if (sizeof($parts) === 0) {
        return "";
    }
    $prefix = $parts[0] === DIRECTORY_SEPARATOR ? DIRECTORY_SEPARATOR : "";
    $processed = array_filter(
        array_map(function ($part) {
            return rtrim($part, DIRECTORY_SEPARATOR);
        }, $parts),
        function ($part) {
            return !empty($part);
        }
    );
    return $prefix . implode(DIRECTORY_SEPARATOR, $processed);
}
