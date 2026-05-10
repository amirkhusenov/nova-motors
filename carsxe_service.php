<?php

require_once 'config.php';

function carsxeIsEnabled(): bool
{
    return defined('CARSXE_API_KEY') && trim((string) CARSXE_API_KEY) !== '';
}

function carsxeGetCarData(array $car): array
{
    $result = [
        'enabled' => carsxeIsEnabled(),
        'images' => [],
        'image_thumbs' => [],
        'specs' => [],
        'errors' => [],
        'match_name' => '',
    ];

    if (!$result['enabled']) {
        return $result;
    }

    $year = (string)($car['year'] ?? '');
    $make = (string)($car['api_make'] ?? '');
    $model = (string)($car['api_model'] ?? '');
    $trim = trim((string)($car['api_trim'] ?? ''));

    if ($year === '' || $make === '' || $model === '') {
        $result['errors'][] = 'Missing year/make/model for CarsXE query.';
        return $result;
    }

    $ymmParams = [
        'year' => $year,
        'make' => $make,
        'model' => $model,
    ];

    if ($trim !== '') {
        $ymmParams['trim'] = $trim;
    }

    $ymmResponse = carsxeRequest('/v1/ymm', $ymmParams);
    if (is_array($ymmResponse) && isset($ymmResponse['bestMatch']) && is_array($ymmResponse['bestMatch'])) {
        $bestMatch = $ymmResponse['bestMatch'];
        $flatSpecs = carsxeFlattenAttributes($bestMatch);
        $result['specs'] = carsxeFilterSpecsForDisplay($flatSpecs);
        $result['match_name'] = (string)($bestMatch['name'] ?? '');
    } else {
        $error = carsxeExtractError($ymmResponse);
        if ($error !== '') {
            $result['errors'][] = 'YMM: ' . $error;
        }
    }

    $imageEntries = carsxeGetCarImageEntries($car, 10);
    if (!empty($imageEntries)) {
        foreach ($imageEntries as $entry) {
            $link = (string)($entry['link'] ?? '');
            $thumb = (string)($entry['thumb'] ?? '');
            if ($link !== '') {
                $result['images'][] = $link;
            }
            if ($thumb !== '') {
                $result['image_thumbs'][] = $thumb;
            }
        }
        $result['images'] = array_values(array_unique($result['images']));
        $result['image_thumbs'] = array_values(array_unique($result['image_thumbs']));
    } else {
        $result['errors'][] = 'Images: not found';
    }

    return $result;
}

function carsxeGetCarImageEntries(array $car, int $limit = 6): array
{
    if (!carsxeIsEnabled()) {
        return [];
    }

    $year = (string)($car['year'] ?? '');
    $make = (string)($car['api_make'] ?? '');
    $model = (string)($car['api_model'] ?? '');
    $trim = trim((string)($car['api_trim'] ?? ''));

    if ($year === '' || $make === '' || $model === '') {
        return [];
    }

    $params = [
        'make' => $make,
        'model' => $model,
        'year' => $year,
        'format' => 'json',
    ];

    if ($trim !== '') {
        $params['trim'] = $trim;
    }

    $response = carsxeRequest('/images', $params);
    return carsxeExtractImageEntries($response, $limit);
}

function carsxeRequest(string $path, array $params): ?array
{
    if (!carsxeIsEnabled()) {
        return null;
    }

    $params['key'] = (string)CARSXE_API_KEY;
    ksort($params);

    $cacheFile = carsxeBuildCacheFile($path, $params);
    $ttl = defined('CARSXE_CACHE_TTL') ? (int)CARSXE_CACHE_TTL : 2592000;
    if ($ttl < 60) {
        $ttl = 60;
    }

    if (is_file($cacheFile)) {
        $mtime = (int)@filemtime($cacheFile);
        if ($mtime > 0 && ($mtime + $ttl) >= time()) {
            $cached = @file_get_contents($cacheFile);
            if (is_string($cached) && $cached !== '') {
                $decoded = json_decode($cached, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }
    }

    $baseUrl = defined('CARSXE_API_BASE_URL') ? (string)CARSXE_API_BASE_URL : 'https://api.carsxe.com';
    $url = rtrim($baseUrl, '/') . $path . '?' . http_build_query($params);
    $timeout = defined('CARSXE_API_TIMEOUT') ? (int)CARSXE_API_TIMEOUT : 10;
    if ($timeout <= 0) {
        $timeout = 10;
    }

    $response = carsxeRequestViaCurl($url, $timeout);
    if ($response === null) {
        $response = carsxeRequestViaStream($url, $timeout);
    }

    if (is_array($response)) {
        carsxeWriteCache($cacheFile, $response);
    }

    return $response;
}

function carsxeRequestViaCurl(string $url, int $timeout): ?array
{
    if (!function_exists('curl_init')) {
        return null;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => $timeout,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ]);

    $body = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!is_string($body) || $body === '' || $httpCode >= 400) {
        return null;
    }

    $decoded = json_decode($body, true);
    return is_array($decoded) ? $decoded : null;
}

function carsxeRequestViaStream(string $url, int $timeout): ?array
{
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Accept: application/json\r\n",
            'timeout' => $timeout,
        ],
    ]);

    $body = @file_get_contents($url, false, $context);
    if (!is_string($body) || $body === '') {
        return null;
    }

    $decoded = json_decode($body, true);
    return is_array($decoded) ? $decoded : null;
}

function carsxeBuildCacheFile(string $path, array $params): string
{
    $dir = __DIR__ . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'carsxe';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    $key = $path . '?' . http_build_query($params);
    $fileName = sha1($key) . '.json';
    return $dir . DIRECTORY_SEPARATOR . $fileName;
}

function carsxeWriteCache(string $cacheFile, array $data): void
{
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (is_string($json) && $json !== '') {
        @file_put_contents($cacheFile, $json);
    }
}

function carsxeExtractImageEntries(?array $response, int $limit = 6): array
{
    if (!is_array($response) || !isset($response['images']) || !is_array($response['images'])) {
        return [];
    }

    $entries = [];
    foreach ($response['images'] as $image) {
        if (!is_array($image)) {
            continue;
        }

        $link = (string)($image['link'] ?? '');
        $thumb = (string)($image['thumbnailLink'] ?? '');

        if ($link === '' && $thumb === '') {
            continue;
        }

        if ($link !== '' && !filter_var($link, FILTER_VALIDATE_URL)) {
            $link = '';
        }

        if ($thumb !== '' && !filter_var($thumb, FILTER_VALIDATE_URL)) {
            $thumb = '';
        }

        if ($link === '' && $thumb === '') {
            continue;
        }

        if ($thumb === '') {
            $thumb = $link;
        }

        if ($link === '') {
            $link = $thumb;
        }

        $entries[] = [
            'link' => $link,
            'thumb' => $thumb,
        ];

        if (count($entries) >= $limit) {
            break;
        }
    }

    return $entries;
}

function carsxeExtractImageLinks(?array $response): array
{
    $entries = carsxeExtractImageEntries($response, 50);
    $links = [];
    foreach ($entries as $entry) {
        $link = (string)($entry['link'] ?? '');
        if ($link !== '') {
            $links[] = $link;
        }
    }
    return array_values(array_unique($links));
}

function carsxeFilterSpecsForDisplay(array $flatSpecs): array
{
    if ($flatSpecs === []) {
        return [];
    }

    $labelMap = [
        'make' => 'Марка',
        'model' => 'Модель',
        'year' => 'Год',
        'name' => 'Комплектация',
        'body type' => 'Тип кузова',
        'vehicle type' => 'Тип авто',
        'drive type' => 'Привод',
        'drivetrain' => 'Привод',
        'transmission' => 'Коробка',
        'fuel type' => 'Топливо',
        'engine' => 'Двигатель',
        'engine size' => 'Объем двигателя',
        'cylinders' => 'Цилиндры',
        'horsepower' => 'Мощность (л.с.)',
        'torque' => 'Крутящий момент',
        'city mpg' => 'Расход город (mpg)',
        'highway mpg' => 'Расход трасса (mpg)',
        'combined mpg' => 'Расход смешанный (mpg)',
        'total seating' => 'Количество мест',
        'doors' => 'Количество дверей',
        'base msrp' => 'Базовая цена (₽)',
    ];

    $priorityPatterns = [
        'make',
        'model',
        'year',
        'name',
        'body type',
        'vehicle type',
        'drive type',
        'drivetrain',
        'transmission',
        'fuel type',
        'engine',
        'engine size',
        'cylinders',
        'horsepower',
        'torque',
        'city mpg',
        'highway mpg',
        'combined mpg',
        'total seating',
        'doors',
        'base msrp',
    ];

    $filtered = [];

    foreach ($priorityPatterns as $pattern) {
        foreach ($flatSpecs as $label => $value) {
            if (carsxeShouldHideSpec($label, $value)) {
                continue;
            }

            $labelNorm = strtolower(trim((string)$label));
            if ($labelNorm === '' || strpos($labelNorm, $pattern) === false) {
                continue;
            }

            $displayLabel = $labelMap[$pattern] ?? $label;
            $displayValue = (string)$value;
            if ($pattern === 'base msrp') {
                $displayValue = carsxeFormatRubFromUsd($displayValue);
            }

            if (!isset($filtered[$displayLabel])) {
                $filtered[$displayLabel] = $displayValue;
            }
        }
    }

    if (!empty($filtered)) {
        return $filtered;
    }

    foreach ($flatSpecs as $label => $value) {
        if (carsxeShouldHideSpec($label, $value)) {
            continue;
        }
        $filtered[(string)$label] = (string)$value;
        if (count($filtered) >= 20) {
            break;
        }
    }

    return $filtered;
}

function carsxeShouldHideSpec(string $label, $value): bool
{
    $labelNorm = strtolower(trim($label));
    $valueNorm = strtolower(trim((string)$value));

    if ($labelNorm === '' || $valueNorm === '' || $valueNorm === '-') {
        return true;
    }

    // Hide noisy color palettes and raw RGB technical values.
    if (strpos($labelNorm, 'rgb') !== false || strpos($labelNorm, 'color /') === 0) {
        return true;
    }

    // Hide mostly technical/service fields that are not useful in UI specs.
    $blocked = ['invoice', 'id', 'code', 'slug', 'url', 'link', 'image', 'thumbnail', 'photo', 'created', 'updated'];
    foreach ($blocked as $needle) {
        if (strpos($labelNorm, $needle) !== false) {
            return true;
        }
    }

    return false;
}

function carsxeFormatRubFromUsd(string $value): string
{
    $numeric = preg_replace('/[^\d.,]/', '', $value);
    if (!is_string($numeric) || $numeric === '') {
        return $value;
    }

    // CarsXE Base MSRP usually comes as integer USD value.
    $usd = (float)str_replace(',', '.', $numeric);
    if ($usd <= 0) {
        return $value;
    }

    $rate = defined('CARSXE_USD_TO_RUB_RATE') ? (float)CARSXE_USD_TO_RUB_RATE : 90.0;
    if ($rate <= 0) {
        $rate = 90.0;
    }

    $rub = (int)round($usd * $rate);
    return number_format($rub, 0, '', '.');
}

function carsxeFlattenAttributes(array $data, string $prefix = ''): array
{
    $flat = [];

    foreach ($data as $key => $value) {
        $label = carsxeHumanizeKey((string)$key);
        $path = $prefix === '' ? $label : $prefix . ' / ' . $label;

        if (is_array($value)) {
            if ($value === []) {
                continue;
            }

            if (carsxeIsScalarList($value)) {
                $flat[$path] = implode(', ', array_map('strval', $value));
                continue;
            }

            if (carsxeIsAssoc($value)) {
                $nested = carsxeFlattenAttributes($value, $path);
                $flat = array_merge($flat, $nested);
                continue;
            }

            foreach ($value as $idx => $item) {
                if (is_array($item)) {
                    $nested = carsxeFlattenAttributes($item, $path . ' #' . ((int)$idx + 1));
                    $flat = array_merge($flat, $nested);
                } elseif (is_scalar($item) || $item === null) {
                    $flat[$path . ' #' . ((int)$idx + 1)] = carsxeScalarToString($item);
                }
            }
            continue;
        }

        if (is_scalar($value) || $value === null) {
            $flat[$path] = carsxeScalarToString($value);
        }
    }

    return $flat;
}

function carsxeExtractError(?array $response): string
{
    if (!is_array($response)) {
        return '';
    }

    $message = (string)($response['message'] ?? $response['error'] ?? '');
    return trim($message);
}

function carsxeHumanizeKey(string $key): string
{
    $key = str_replace(['_', '-'], ' ', trim($key));
    $key = preg_replace('/\s+/', ' ', $key);

    if ($key === null || $key === '') {
        return 'Field';
    }

    if (function_exists('mb_convert_case')) {
        return mb_convert_case($key, MB_CASE_TITLE, 'UTF-8');
    }

    return ucwords(strtolower($key));
}

function carsxeScalarToString($value): string
{
    if ($value === null) {
        return '-';
    }
    if (is_bool($value)) {
        return $value ? 'Yes' : 'No';
    }
    return (string)$value;
}

function carsxeIsAssoc(array $array): bool
{
    if ($array === []) {
        return false;
    }
    return array_keys($array) !== range(0, count($array) - 1);
}

function carsxeIsScalarList(array $array): bool
{
    foreach ($array as $item) {
        if (!(is_scalar($item) || $item === null)) {
            return false;
        }
    }
    return true;
}
