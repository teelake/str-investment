<?php

declare(strict_types=1);

final class ErrorPage
{
    public static function respond(int $httpCode, string $title, string $message, bool $showHomeLink = true): void
    {
        http_response_code($httpCode);
        header('Content-Type: text/html; charset=UTF-8');

        $bp = '';
        $styles = '/assets/styles.css';
        $logo = '';
        if (class_exists(Request::class)) {
            $bp = Request::basePath();
            $styles = Request::asset('assets/styles.css');
            $logo = Request::asset('assets/images/str-logo.png');
        }

        $home = $bp !== '' ? htmlspecialchars($bp . '/', ENT_QUOTES, 'UTF-8') : '/';
        $login = $bp !== '' ? htmlspecialchars($bp . '/login', ENT_QUOTES, 'UTF-8') : '/login';
        $hTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $hMsg = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1" />';
        echo '<title>' . $hTitle . ' — STR Console</title>';
        if ($logo !== '') {
            echo '<link rel="icon" type="image/png" href="' . htmlspecialchars($logo, ENT_QUOTES, 'UTF-8') . '" />';
        }
        echo '<link rel="preconnect" href="https://fonts.googleapis.com" /><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />';
        echo '<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet" />';
        echo '<link rel="stylesheet" href="' . htmlspecialchars($styles, ENT_QUOTES, 'UTF-8') . '" />';
        echo '<style>body{font-family:\'Plus Jakarta Sans\',system-ui,sans-serif;background:var(--bg,#f4f6f5);color:var(--ink,#111);margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;}';
        echo '.box{max-width:420px;background:var(--card,#fff);border:1px solid var(--line2,rgba(0,0,0,.08));border-radius:16px;padding:28px;box-shadow:0 8px 30px rgba(0,0,0,.06);}';
        echo 'h1{font-size:1.25rem;margin:0 0 10px;font-weight:800;}p{margin:0 0 18px;color:var(--muted,#5c6670);font-size:15px;line-height:1.5;}';
        echo 'a{color:var(--green2,#0f6a4a);font-weight:700;text-decoration:none;}a:hover{text-decoration:underline;}</style></head><body>';
        echo '<div class="box">';
        if ($logo !== '') {
            echo '<img src="' . htmlspecialchars($logo, ENT_QUOTES, 'UTF-8') . '" alt="" width="140" height="40" style="height:40px;width:auto;margin:0 0 16px;display:block;" />';
        }
        echo '<h1>' . $hTitle . '</h1><p>' . $hMsg . '</p>';
        if ($showHomeLink) {
            echo '<p style="margin:0;font-size:14px;"><a href="' . $home . '">Dashboard</a> · <a href="' . $login . '">Sign in</a></p>';
        }
        echo '</div></body></html>';
        exit;
    }
}
