<?php
// security function: regex whitelist function (I remember I did something like this, go and find it in the SWAP project)
function cleanUp($data) {
    // regex syntax to search_Update: Found it: /^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%^&*])[A-Za-z\d@$!%*?&]{8,}$/
    $pattern = "/[^a-zA-Z0-9\s\.,\!\?]/"; // /[^a-zA-Z0-9]/ format

    // remove "bad" characters/ sanitisation
    $cleanData = preg_replace($pattern, "", $data);

    // trim() removes invisble space at start & end  prevent db err
    return trim($cleanData);
}
?>