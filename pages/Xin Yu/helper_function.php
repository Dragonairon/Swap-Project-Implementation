<?php
// security function: regex whitelist function
function cleanUp($data) {
    
    $pattern = "/[^a-zA-Z0-9\s\.,\!\?]/"; // /[^a-zA-Z0-9]/ format

    // remove "bad" characters/ sanitisation
    $cleanData = preg_replace($pattern, "", $data);

    // trim() removes invisble space at start & end  prevent db err
    return trim($cleanData);
}
?>
