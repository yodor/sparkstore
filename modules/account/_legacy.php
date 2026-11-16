<?php
/**
 * Old addresses of the account pages (sparkstore/account/*.php - links in mails already sent, bookmarks,
 * search engines): each old file redirects (301) to its module path, the query string kept.
 * The folder is the one of the requested script - the module works from any folder name.
 * @param string $path module path
 * @return never
 */
function AccountLegacyRedirect(string $path) : never
{
    $folder = rtrim(str_replace(chr(92), "/", dirname((string)($_SERVER["SCRIPT_NAME"] ?? "/account/index.php"))), "/");
    $query = (string)($_SERVER["QUERY_STRING"] ?? "");
    header("Location: " . $folder . "/" . $path . (($query !== "") ? "?" . $query : ""), true, 301);
    exit;
}
