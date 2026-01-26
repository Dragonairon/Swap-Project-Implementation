<?php
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function redirect_to(string $relativePathFromBaseUrl): void {
    header('Location: ' . BASE_URL . ltrim($relativePathFromBaseUrl, '/'));
    exit;
}
