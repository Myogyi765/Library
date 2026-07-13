<?php

if (!function_exists('view')) {
    
    function view(string $template, array $data = []): void
    {
        extract($data);
        
        $file = str_replace('.', '/', $template);
        $viewPath = __DIR__ . '/../../../view/' . $file . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: {$template} (path: {$viewPath})");
        }
        
        require $viewPath;
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to a URL
     */
    function redirect(string $url): void
    {
        if (!str_starts_with($url, 'http')) {
            $url = BASE_URL . $url;
        }
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('urlencode')) {
    /**
     * Alias for PHP's urlencode
     */
    function urlencode(string $string): string
    {
        return \urlencode($string);
    }
}