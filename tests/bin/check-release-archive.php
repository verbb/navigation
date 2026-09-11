<?php

declare(strict_types=1);

$path = $argv[1] ?? '';
$root = dirname(__DIR__, 2);
$zip = new ZipArchive();
if (!$path || $zip->open($path) !== true) {
    throw new RuntimeException('Pass a readable Composer ZIP archive.');
}
$count = 0;
for ($i = 0; $i < $zip->numFiles; $i++) {
    $name = $zip->getNameIndex($i);
    if (!is_string($name) || str_ends_with($name, '/')) continue;
    if (str_starts_with($name, '/') || str_contains($name, '\\')) {
        throw new RuntimeException("Invalid archive path: $name");
    }
    foreach (explode('/', $name) as $part) {
        if (in_array($part, ['..', '.git', '.github', '.ddev', '.cache', 'node_modules', 'vendor', 'tests', 'docs'], true) || str_starts_with($part, '.env') || str_starts_with($part, '.phpunit') || str_starts_with($part, '.pest')) {
            throw new RuntimeException("Development/private path in archive: $name");
        }
    }
    $bytes = $zip->getFromIndex($i);
    if (!is_string($bytes) || !is_file($root . '/' . $name) || hash('sha256', $bytes) !== hash_file('sha256', $root . '/' . $name)) {
        throw new RuntimeException("Archive differs from checkout: $name");
    }
    $count++;
}
foreach (['composer.json', 'src/Navigation.php', 'src/web/assets/cp/dist/manifest.json'] as $required) {
    if ($zip->locateName($required) === false) throw new RuntimeException("Missing runtime file: $required");
}
$zip->close();
echo json_encode(['archive'=>realpath($path), 'sha256'=>hash_file('sha256',$path), 'bytes'=>filesize($path), 'files'=>$count, 'matchesCheckout'=>true], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
