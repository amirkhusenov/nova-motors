<?php

function getCarsCatalog(): array
{
    $baseCatalog = [
        1 => ['id' => 1, 'name' => 'Toyota Camry', 'year' => 2023, 'api_make' => 'Toyota', 'api_model' => 'Camry', 'api_trim' => '', 'type' => 'Седан', 'price_per_day' => 5200, 'fuel' => '60L', 'transmission' => 'AT', 'seats' => '5', 'description' => 'Комфортный бизнес-седан для поездок по городу и трассе.', 'image' => 'img/bg.jpg', 'images' => []],
        2 => ['id' => 2, 'name' => 'Honda Civic', 'year' => 2022, 'api_make' => 'Honda', 'api_model' => 'Civic', 'api_trim' => '', 'type' => 'Седан', 'price_per_day' => 4700, 'fuel' => '47L', 'transmission' => 'CVT', 'seats' => '5', 'description' => 'Сбалансированный седан на каждый день с плавной управляемостью и хорошей экономичностью.', 'image' => 'img/bg.jpg', 'images' => []],
        3 => ['id' => 3, 'name' => 'BMW 3 Series', 'year' => 2022, 'api_make' => 'BMW', 'api_model' => '3 Series', 'api_trim' => '', 'type' => 'Седан', 'price_per_day' => 6900, 'fuel' => '59L', 'transmission' => 'AT', 'seats' => '5', 'description' => 'Премиальный спортивный седан с точным рулевым управлением и динамичным характером.', 'image' => 'img/bg.jpg', 'images' => []],
        4 => ['id' => 4, 'name' => 'Mercedes-Benz C-Class', 'year' => 2023, 'api_make' => 'Mercedes-Benz', 'api_model' => 'C-Class', 'api_trim' => '', 'type' => 'Седан', 'price_per_day' => 7200, 'fuel' => '66L', 'transmission' => 'AT', 'seats' => '5', 'description' => 'Премиальный седан с акцентом на комфорт, тишину и плавность хода.', 'image' => 'img/bg.jpg', 'images' => []],
        5 => ['id' => 5, 'name' => 'Audi A6', 'year' => 2022, 'api_make' => 'Audi', 'api_model' => 'A6', 'api_trim' => '', 'type' => 'Седан', 'price_per_day' => 7400, 'fuel' => '73L', 'transmission' => 'AT', 'seats' => '5', 'description' => 'Представительский седан с утончённым салоном и уверенным поведением на трассе.', 'image' => 'img/bg.jpg', 'images' => []],
        6 => ['id' => 6, 'name' => 'Lexus RX', 'year' => 2023, 'api_make' => 'Lexus', 'api_model' => 'RX', 'api_trim' => '', 'type' => 'Внедорожник', 'price_per_day' => 7600, 'fuel' => '67L', 'transmission' => 'AT', 'seats' => '5', 'description' => 'Премиальный внедорожник с высоким уровнем комфорта и практичным салоном на каждый день.', 'image' => 'img/bg.jpg', 'images' => []],
        7 => ['id' => 7, 'name' => 'Porsche 911', 'year' => 2022, 'api_make' => 'Porsche', 'api_model' => '911', 'api_trim' => '', 'type' => 'Спорткар', 'price_per_day' => 10800, 'fuel' => '64L', 'transmission' => 'AT', 'seats' => '4', 'description' => 'Легендарный спорткар с быстрым разгоном и точной обратной связью.', 'image' => 'img/bg.jpg', 'images' => []],
        8 => ['id' => 8, 'name' => 'Tesla Model 3', 'year' => 2023, 'api_make' => 'Tesla', 'api_model' => 'Model 3', 'api_trim' => '', 'type' => 'Электромобиль', 'price_per_day' => 6800, 'fuel' => 'EV', 'transmission' => 'Single-speed', 'seats' => '5', 'description' => 'Электрический седан с мгновенным откликом и минималистичным интерьером.', 'image' => 'img/bg.jpg', 'images' => []],
        9 => ['id' => 9, 'name' => 'Nissan GT-R', 'year' => 2021, 'api_make' => 'Nissan', 'api_model' => 'GT-R', 'api_trim' => '', 'type' => 'Спорткар', 'price_per_day' => 9400, 'fuel' => '74L', 'transmission' => 'DCT', 'seats' => '4', 'description' => 'Полноприводное купе высокой производительности, созданное для скорости и сцепления.', 'image' => 'img/bg.jpg', 'images' => []],
        10 => ['id' => 10, 'name' => 'Ford Mustang', 'year' => 2022, 'api_make' => 'Ford', 'api_model' => 'Mustang', 'api_trim' => '', 'type' => 'Спорткар', 'price_per_day' => 7300, 'fuel' => '61L', 'transmission' => 'AT', 'seats' => '4', 'description' => 'Классическое американское купе с выразительным дизайном и мощным двигателем.', 'image' => 'img/bg.jpg', 'images' => []],
        11 => ['id' => 11, 'name' => 'Chevrolet Camaro', 'year' => 2022, 'api_make' => 'Chevrolet', 'api_model' => 'Camaro', 'api_trim' => '', 'type' => 'Спорткар', 'price_per_day' => 7100, 'fuel' => '72L', 'transmission' => 'AT', 'seats' => '4', 'description' => 'Маслкар-купе с агрессивной внешностью и отзывчивой управляемостью.', 'image' => 'img/bg.jpg', 'images' => []],
        12 => ['id' => 12, 'name' => 'Dodge Challenger', 'year' => 2021, 'api_make' => 'Dodge', 'api_model' => 'Challenger', 'api_trim' => '', 'type' => 'Маслкар', 'price_per_day' => 7600, 'fuel' => '70L', 'transmission' => 'AT', 'seats' => '5', 'description' => 'Крупный маслкар с ярким стилем и мощным характером.', 'image' => 'img/bg.jpg', 'images' => []],
        13 => ['id' => 13, 'name' => 'Hyundai Elantra', 'year' => 2023, 'api_make' => 'Hyundai', 'api_model' => 'Elantra', 'api_trim' => '', 'type' => 'Седан', 'price_per_day' => 4200, 'fuel' => '47L', 'transmission' => 'AT', 'seats' => '5', 'description' => 'Практичный компактный седан с хорошим комфортом и современным дизайном.', 'image' => 'img/bg.jpg', 'images' => []],
        14 => ['id' => 14, 'name' => 'Kia K5', 'year' => 2023, 'api_make' => 'Kia', 'api_model' => 'K5', 'api_trim' => '', 'type' => 'Седан', 'price_per_day' => 5000, 'fuel' => '60L', 'transmission' => 'AT', 'seats' => '5', 'description' => 'Стильный седан среднего класса с просторным салоном и плавным ходом.', 'image' => 'img/bg.jpg', 'images' => []],
        15 => ['id' => 15, 'name' => 'Volkswagen Golf GTI', 'year' => 2022, 'api_make' => 'Volkswagen', 'api_model' => 'Golf GTI', 'api_trim' => '', 'type' => 'Хэтчбек', 'price_per_day' => 6200, 'fuel' => '50L', 'transmission' => 'DCT', 'seats' => '5', 'description' => 'Заряженный хэтчбек с практичным кузовом и спортивным поведением.', 'image' => 'img/bg.jpg', 'images' => []],
        16 => ['id' => 16, 'name' => 'Subaru WRX', 'year' => 2022, 'api_make' => 'Subaru', 'api_model' => 'WRX', 'api_trim' => '', 'type' => 'Спорткар', 'price_per_day' => 6400, 'fuel' => '63L', 'transmission' => 'MT', 'seats' => '5', 'description' => 'Полноприводный спортивный седан с уверенным контролем на дороге.', 'image' => 'img/bg.jpg', 'images' => []],
        17 => ['id' => 17, 'name' => 'Mazda CX-5', 'year' => 2023, 'api_make' => 'Mazda', 'api_model' => 'CX-5', 'api_trim' => '', 'type' => 'Внедорожник', 'price_per_day' => 6100, 'fuel' => '58L', 'transmission' => 'AT', 'seats' => '5', 'description' => 'Компактный внедорожник с сбалансированным комфортом и качественным салоном.', 'image' => 'img/bg.jpg', 'images' => []],
        18 => ['id' => 18, 'name' => 'Jeep Grand Cherokee', 'year' => 2022, 'api_make' => 'Jeep', 'api_model' => 'Grand Cherokee', 'api_trim' => '', 'type' => 'Внедорожник', 'price_per_day' => 7600, 'fuel' => '87L', 'transmission' => 'AT', 'seats' => '5', 'description' => 'Крупный внедорожник для дальних поездок и разных дорожных условий.', 'image' => 'img/bg.jpg', 'images' => []],
        19 => ['id' => 19, 'name' => 'Range Rover Evoque', 'year' => 2022, 'api_make' => 'Land Rover', 'api_model' => 'Range Rover Evoque', 'api_trim' => '', 'type' => 'Внедорожник', 'price_per_day' => 7900, 'fuel' => '67L', 'transmission' => 'AT', 'seats' => '5', 'description' => 'Премиальный компактный внедорожник с современным стилем и уютным салоном.', 'image' => 'img/bg.jpg', 'images' => []],
        20 => ['id' => 20, 'name' => 'Rolls-Royce Ghost', 'year' => 2022, 'api_make' => 'Rolls-Royce', 'api_model' => 'Ghost', 'api_trim' => '', 'type' => 'Люкс', 'price_per_day' => 15000, 'fuel' => '83L', 'transmission' => 'AT', 'seats' => '5', 'description' => 'Флагманский люксовый седан для особых мероприятий и представительских поездок.', 'image' => 'img/bg.jpg', 'images' => []],
    ];

    return carsMergeCatalogWithAdminOverrides($baseCatalog);
}

function getCarsList(): array
{
    return array_values(getCarsCatalog());
}

function getCarById(int $id): ?array
{
    $cars = getCarsCatalog();
    return $cars[$id] ?? null;
}

function createCatalogCar(array $input)
{
    $cars = getCarsCatalog();
    $newId = empty($cars) ? 1 : (max(array_keys($cars)) + 1);
    $car = carsBuildCatalogCar($newId, $input);

    if ($car === null) {
        return false;
    }

    $overrides = getCarsAdminOverrides();
    $overrides['upserts'][(string)$newId] = $car;
    unset($overrides['deleted_ids'][(string)$newId]);

    if (!saveCarsAdminOverrides($overrides)) {
        return false;
    }

    return $newId;
}

function updateCatalogCar(int $id, array $input): bool
{
    if ($id <= 0 || getCarById($id) === null) {
        return false;
    }

    $car = carsBuildCatalogCar($id, $input);
    if ($car === null) {
        return false;
    }

    $overrides = getCarsAdminOverrides();
    $overrides['upserts'][(string)$id] = $car;
    unset($overrides['deleted_ids'][(string)$id]);

    return saveCarsAdminOverrides($overrides);
}

function deleteCatalogCar(int $id): bool
{
    if ($id <= 0 || getCarById($id) === null) {
        return false;
    }

    $overrides = getCarsAdminOverrides();
    unset($overrides['upserts'][(string)$id]);
    $overrides['deleted_ids'][(string)$id] = true;

    return saveCarsAdminOverrides($overrides);
}

function carsMergeCatalogWithAdminOverrides(array $baseCatalog): array
{
    $catalog = $baseCatalog;
    $overrides = getCarsAdminOverrides();

    foreach ($overrides['upserts'] as $id => $car) {
        $carId = (int)$id;
        if ($carId <= 0 || !is_array($car)) {
            continue;
        }
        $catalog[$carId] = carsNormalizeCatalogCar($carId, $car);
    }

    foreach ($overrides['deleted_ids'] as $id => $isDeleted) {
        if ($isDeleted) {
            unset($catalog[(int)$id]);
        }
    }

    ksort($catalog, SORT_NUMERIC);
    return $catalog;
}

function carsBuildCatalogCar(int $id, array $input): ?array
{
    $name = trim((string)($input['name'] ?? ''));
    $type = trim((string)($input['type'] ?? ''));
    $year = (int)($input['year'] ?? 0);
    $pricePerDay = (float)($input['price_per_day'] ?? 0);
    $fuel = trim((string)($input['fuel'] ?? ''));
    $transmission = trim((string)($input['transmission'] ?? ''));
    $seats = trim((string)($input['seats'] ?? ''));
    $description = trim((string)($input['description'] ?? ''));
    $image = trim((string)($input['image'] ?? ''));
    $apiMake = trim((string)($input['api_make'] ?? ''));
    $apiModel = trim((string)($input['api_model'] ?? ''));
    $apiTrim = trim((string)($input['api_trim'] ?? ''));

    if ($id <= 0 || $name === '' || $type === '' || $year <= 0 || $pricePerDay <= 0) {
        return null;
    }

    return [
        'id' => $id,
        'name' => $name,
        'year' => $year,
        'api_make' => $apiMake,
        'api_model' => $apiModel,
        'api_trim' => $apiTrim,
        'type' => $type,
        'price_per_day' => $pricePerDay,
        'fuel' => $fuel !== '' ? $fuel : '-',
        'transmission' => $transmission !== '' ? $transmission : '-',
        'seats' => $seats !== '' ? $seats : '-',
        'description' => $description,
        'image' => $image !== '' ? $image : 'img/bg.jpg',
        'images' => [],
    ];
}

function carsNormalizeCatalogCar(int $id, array $car): array
{
    $normalized = carsBuildCatalogCar($id, $car);
    if ($normalized === null) {
        return [
            'id' => $id,
            'name' => (string)($car['name'] ?? 'Car'),
            'year' => (int)($car['year'] ?? 2024),
            'api_make' => (string)($car['api_make'] ?? ''),
            'api_model' => (string)($car['api_model'] ?? ''),
            'api_trim' => (string)($car['api_trim'] ?? ''),
            'type' => (string)($car['type'] ?? 'Other'),
            'price_per_day' => (float)($car['price_per_day'] ?? 0),
            'fuel' => (string)($car['fuel'] ?? '-'),
            'transmission' => (string)($car['transmission'] ?? '-'),
            'seats' => (string)($car['seats'] ?? '-'),
            'description' => (string)($car['description'] ?? ''),
            'image' => (string)($car['image'] ?? 'img/bg.jpg'),
            'images' => [],
        ];
    }

    return $normalized;
}

function getCarsAdminOverrides(): array
{
    $path = getCarsAdminOverridesPath();
    if (!is_file($path)) {
        return ['upserts' => [], 'deleted_ids' => []];
    }

    $data = require $path;
    if (!is_array($data)) {
        return ['upserts' => [], 'deleted_ids' => []];
    }

    $upserts = isset($data['upserts']) && is_array($data['upserts']) ? $data['upserts'] : [];
    $deletedIdsRaw = isset($data['deleted_ids']) && is_array($data['deleted_ids']) ? $data['deleted_ids'] : [];
    $deletedIds = [];
    foreach ($deletedIdsRaw as $id) {
        $deletedIds[(string)((int)$id)] = true;
    }

    return ['upserts' => $upserts, 'deleted_ids' => $deletedIds];
}

function saveCarsAdminOverrides(array $overrides): bool
{
    $path = getCarsAdminOverridesPath();
    $upserts = isset($overrides['upserts']) && is_array($overrides['upserts']) ? $overrides['upserts'] : [];
    $deletedIds = [];
    if (isset($overrides['deleted_ids']) && is_array($overrides['deleted_ids'])) {
        foreach ($overrides['deleted_ids'] as $id => $isDeleted) {
            if ($isDeleted) {
                $deletedIds[] = (int)$id;
            }
        }
    }

    sort($deletedIds, SORT_NUMERIC);
    ksort($upserts, SORT_NUMERIC);

    $payload = [
        'upserts' => $upserts,
        'deleted_ids' => $deletedIds,
    ];

    $content = "<?php\nreturn " . var_export($payload, true) . ";\n";
    return file_put_contents($path, $content, LOCK_EX) !== false;
}

function getCarsAdminOverridesPath(): string
{
    return __DIR__ . DIRECTORY_SEPARATOR . 'cars_admin_overrides.php';
}

function getCarsMediaCache(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $cache = [];
    $path = __DIR__ . DIRECTORY_SEPARATOR . 'cars_media_cache.php';
    if (is_file($path)) {
        $data = require $path;
        if (is_array($data)) {
            $cache = $data;
        }
    }

    return $cache;
}

function getCarCardImage(array $car): string
{
    $carId = (int)($car['id'] ?? 0);
    $cache = getCarsMediaCache();
    $localImage = getCarLocalImage($carId);

    if ($localImage !== '') {
        return $localImage;
    }

    if ($carId > 0 && isset($cache[$carId]['card']) && is_string($cache[$carId]['card']) && $cache[$carId]['card'] !== '') {
        $card = carsNormalizeImageUrl($cache[$carId]['card']);
        if ($card !== '' && !carsIsLowQualityImageUrl($card)) {
            return $card;
        }
    }

    if ($carId > 0 && isset($cache[$carId]['gallery']) && is_array($cache[$carId]['gallery']) && !empty($cache[$carId]['gallery'])) {
        $bestFromGallery = carsPickBestImageFromList($cache[$carId]['gallery']);
        if ($bestFromGallery !== '') {
            return $bestFromGallery;
        }
    }

    if (!empty($car['image']) && is_string($car['image'])) {
        return $car['image'];
    }

    return 'img/bg.jpg';
}

function getCarGalleryImages(array $car): array
{
    $carId = (int)($car['id'] ?? 0);
    $cache = getCarsMediaCache();
    $localImage = getCarLocalImage($carId);

    if ($carId > 0 && isset($cache[$carId]['gallery']) && is_array($cache[$carId]['gallery']) && !empty($cache[$carId]['gallery'])) {
        $images = array_values(array_filter($cache[$carId]['gallery'], function ($item) {
            return is_string($item) && $item !== '';
        }));
        $best = carsPickBestImageFromList($images);
        if ($best !== '') {
            $images = array_values(array_unique(array_merge([$best], $images)));
        }
        if ($localImage !== '') {
            $images = array_values(array_unique(array_merge([$localImage], $images)));
        }
        return $images;
    }

    if (isset($car['images']) && is_array($car['images']) && !empty($car['images'])) {
        $images = array_values(array_filter($car['images'], function ($item) {
            return is_string($item) && $item !== '';
        }));
        if ($localImage !== '') {
            $images = array_values(array_unique(array_merge([$localImage], $images)));
        }
        return $images;
    }

    if ($localImage !== '') {
        return [$localImage];
    }

    return [getCarCardImage($car)];
}

function getCarLocalImage(int $carId): string
{
    if ($carId <= 0) {
        return '';
    }

    $relativePath = 'img/cars/car-' . $carId . '.jpg';
    $absolutePath = __DIR__ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'cars' . DIRECTORY_SEPARATOR . 'car-' . $carId . '.jpg';
    return is_file($absolutePath) ? $relativePath : '';
}

function carsNormalizeImageUrl(string $url): string
{
    $url = trim($url);
    if ($url === '') {
        return '';
    }

    // Remove explicit tiny thumbnail patterns from CarsCommerce-style URLs.
    $url = str_replace('/thumbnails/large/', '/large/', $url);
    return $url;
}

function carsIsLowQualityImageUrl(string $url): bool
{
    $url = strtolower(trim($url));
    if ($url === '') {
        return true;
    }

    // Google image-proxy thumbnails are low-quality and unstable.
    if (strpos($url, 'encrypted-tbn') !== false || strpos($url, 'q=tbn:') !== false) {
        return true;
    }

    return false;
}

function carsPickBestImageFromList(array $images): string
{
    $normalized = [];
    foreach ($images as $image) {
        if (!is_string($image)) {
            continue;
        }
        $url = carsNormalizeImageUrl($image);
        if ($url !== '') {
            $normalized[] = $url;
        }
    }

    if (empty($normalized)) {
        return '';
    }

    foreach ($normalized as $url) {
        if (!carsIsLowQualityImageUrl($url)) {
            return $url;
        }
    }

    return $normalized[0];
}

function getCarPriceLabel(array $car): string
{
    if (isset($car['price_label']) && is_string($car['price_label']) && $car['price_label'] !== '') {
        return $car['price_label'];
    }

    $price = (float)($car['price_per_day'] ?? 0);
    return number_format($price, 0, '.', ' ') . ' ₽';
}

function getCarCardSpecs(array $car): array
{
    return [
        'fuel' => carsLocalizeFuelValue((string)($car['fuel'] ?? '-')),
        'transmission' => carsLocalizeTransmissionValue((string)($car['transmission'] ?? '-')),
        'seats' => carsLocalizeSeatsValue((string)($car['seats'] ?? '-')),
    ];
}

function carsLocalizeFuelValue(string $fuel): string
{
    $fuel = trim($fuel);
    if ($fuel === '' || $fuel === '-') {
        return '-';
    }

    if (strtoupper($fuel) === 'EV') {
        return 'Электро';
    }

    return $fuel;
}

function carsLocalizeTransmissionValue(string $transmission): string
{
    $value = strtoupper(trim($transmission));
    $map = [
        'AT' => 'АКПП',
        'MT' => 'МКПП',
        'CVT' => 'Вариатор',
        'DCT' => 'Робот',
        'SINGLE-SPEED' => 'Редуктор',
    ];

    if (isset($map[$value])) {
        return $map[$value];
    }

    return trim($transmission) !== '' ? trim($transmission) : '-';
}

function carsLocalizeSeatsValue(string $seats): string
{
    $seats = trim($seats);
    if ($seats === '' || $seats === '-') {
        return '-';
    }

    return $seats . ' мест';
}

function getCar3DModelUrl(array $car): string
{
    $carId = (int)($car['id'] ?? 0);
    if ($carId <= 0) {
        return '';
    }

    $overrides = getCars3DModelOverrides();
    if (isset($overrides[$carId]) && is_string($overrides[$carId])) {
        $override = trim($overrides[$carId]);
        if (carsIsValidModelSource($override)) {
            return $override;
        }
    }

    $relativePath = 'models/cars/car-' . $carId . '.glb';
    $absolutePath = __DIR__ . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'cars' . DIRECTORY_SEPARATOR . 'car-' . $carId . '.glb';
    if (is_file($absolutePath)) {
        return $relativePath;
    }

    return '';
}

function getCar3DViewerType(string $source): string
{
    $source = trim($source);
    if ($source === '') {
        return '';
    }

    if (preg_match('/\.glb($|\?)/i', $source)) {
        return 'model';
    }

    if (carsBuild3DEmbedUrl($source) !== '') {
        return 'embed';
    }

    return '';
}

function getCar3DEmbedUrl(string $source): string
{
    return carsBuild3DEmbedUrl($source);
}

function getCars3DModelOverrides(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $cache = [];
    $path = __DIR__ . DIRECTORY_SEPARATOR . 'cars_3d_models.php';
    if (!is_file($path)) {
        return $cache;
    }

    $data = require $path;
    if (is_array($data)) {
        $cache = $data;
    }

    return $cache;
}

function carsIsValidModelSource(string $source): bool
{
    $source = trim($source);
    if ($source === '') {
        return false;
    }

    if (filter_var($source, FILTER_VALIDATE_URL)) {
        return true;
    }

    $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $source);
    $absolutePath = __DIR__ . DIRECTORY_SEPARATOR . ltrim($normalized, DIRECTORY_SEPARATOR);
    return is_file($absolutePath);
}

function carsBuild3DEmbedUrl(string $source): string
{
    $source = trim($source);
    if ($source === '') {
        return '';
    }

    if (preg_match('#^https?://sketchfab\.com/models/([a-f0-9]{32})/embed(?:\?.*)?$#i', $source)) {
        return $source;
    }

    if (preg_match('#^https?://sketchfab\.com/3d-models/.+-([a-f0-9]{32})(?:\?.*)?$#i', $source, $m)) {
        return 'https://sketchfab.com/models/' . strtolower($m[1]) . '/embed';
    }

    if (preg_match('#^https?://sketchfab\.com/models/([a-f0-9]{32})(?:\?.*)?$#i', $source, $m)) {
        return 'https://sketchfab.com/models/' . strtolower($m[1]) . '/embed';
    }

    return '';
}

