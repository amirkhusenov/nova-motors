<?php

require_once __DIR__ . '/cars_catalog.php';
require_once __DIR__ . '/carsxe_service.php';

if (!carsxeIsEnabled()) {
    fwrite(STDERR, "CarsXE key is empty. Fill CARSXE_API_KEY in config.php\n");
    exit(1);
}

$cars = getCarsList();
$cache = [];
$okCount = 0;

foreach ($cars as $car) {
    $id = (int)($car['id'] ?? 0);
    $name = (string)($car['name'] ?? ('Car #' . $id));

    if ($id <= 0) {
        continue;
    }

    $entries = carsxeGetCarImageEntries($car, 8);
    if (empty($entries)) {
        echo "[WARN] {$id} {$name}: images not found\n";
        continue;
    }

    $card = '';
    $gallery = [];

    foreach ($entries as $entry) {
        $link = (string)($entry['link'] ?? '');
        $thumb = (string)($entry['thumb'] ?? '');

        if ($card === '' && $thumb !== '') {
            $card = $thumb;
        }

        if ($link !== '') {
            $gallery[] = $link;
        }
    }

    $gallery = array_values(array_unique(array_filter($gallery)));

    if ($card === '' && !empty($gallery)) {
        $card = $gallery[0];
    }

    if ($card === '') {
        echo "[WARN] {$id} {$name}: no usable image url\n";
        continue;
    }

    $cache[$id] = [
        'name' => $name,
        'card' => $card,
        'gallery' => $gallery,
    ];

    $okCount++;
    echo "[OK] {$id} {$name}: card + " . count($gallery) . " gallery images\n";
}

$php = "<?php\n\nreturn " . var_export($cache, true) . ";\n";
$target = __DIR__ . '/cars_media_cache.php';
file_put_contents($target, $php);

echo "\nSaved: {$target}\n";
echo "Cars with media: {$okCount}/" . count($cars) . "\n";

