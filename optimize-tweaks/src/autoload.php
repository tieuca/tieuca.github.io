<?php
/**
 * Autoloader for AZ Settings Plugin.
 * Follows PSR-4 standard.
 */
spl_autoload_register(function ($class) {
    // The order matters. More specific namespaces should come first.
    $prefixes = [
        'OXT\\App\\Modules\\'       => __DIR__ . '/App/Modules/',
        'OXT\\App\\'               => __DIR__ . '/App/',

        'TieuCA\\WPSettings\\Helpers' => __DIR__ . '/Framework/WPSettings/Helpers.php',
        'TieuCA\\WPSettings\\'   => __DIR__ . '/Framework/WPSettings/',
        'Adbar\\'                   => __DIR__ . '/Framework/Adbar/',
    ];

    // Logic to handle single files like Helpers.php first.
    if (isset($prefixes[$class]) && file_exists($prefixes[$class])) {
        require_once $prefixes[$class];
        return;
    }

    // Logic to handle directories (PSR-4).
    foreach ($prefixes as $prefix => $base_dir) {
        // Skip single file entries we already checked.
        if (is_file($base_dir)) {
            continue;
        }

        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) { continue; }
        
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
