<?php

$postmanFile = __DIR__ . '/../Gizra-API.postman_collection.json';
$routesFile = __DIR__ . '/../routes.json';

if (!file_exists($postmanFile) || !file_exists($routesFile)) {
    die("Files not found\n");
}

$postmanData = json_decode(file_get_contents($postmanFile), true);
$routesData = json_decode(file_get_contents($routesFile), true);

$postmanEndpoints = [];

function extractEndpoints($item, &$endpoints) {
    if (isset($item['item'])) {
        foreach ($item['item'] as $subItem) {
            extractEndpoints($subItem, $endpoints);
        }
    } else if (isset($item['request'])) {
        $method = strtoupper($item['request']['method']);
        $url = '';
        if (isset($item['request']['url']['raw'])) {
            $url = $item['request']['url']['raw'];
        } else if (isset($item['request']['url']) && is_string($item['request']['url'])) {
            $url = $item['request']['url'];
        }
        
        // Clean url: remove {{base_url}} and query params
        $url = str_replace('{{base_url}}/', '', $url);
        $url = str_replace('{{base_url}}', '', $url);
        $url = explode('?', $url)[0];
        
        // Handle postman path variables like :id -> {id}
        $url = preg_replace('/\:([a-zA-Z0-9_]+)/', '{$1}', $url);
        
        $url = trim($url, '/');
        
        $endpoints[] = $method . ' ' . $url;
    }
}

extractEndpoints($postmanData, $postmanEndpoints);

$postmanEndpoints = array_unique($postmanEndpoints);

$laravelRoutes = [];
foreach ($routesData as $route) {
    // Only API routes
    if (strpos($route['uri'], 'api/') === 0) {
        $methods = explode('|', $route['method']);
        foreach ($methods as $method) {
            if ($method === 'HEAD') continue;
            
            // Clean up optional parameters in laravel routes like {id?} -> {id}
            $uri = str_replace('?}', '}', $route['uri']);
            $laravelRoutes[] = $method . ' ' . $uri;
        }
    }
}

$missingInPostman = array_diff($laravelRoutes, $postmanEndpoints);
$missingInLaravel = array_diff($postmanEndpoints, $laravelRoutes);

echo "Total Laravel API Routes: " . count($laravelRoutes) . "\n";
echo "Total Postman Endpoints: " . count($postmanEndpoints) . "\n";
echo "\n--- Missing in Postman (Found in Laravel but not in Postman) ---\n";
foreach ($missingInPostman as $missing) {
    echo $missing . "\n";
}

echo "\n--- Extra in Postman (Found in Postman but not in Laravel) ---\n";
foreach ($missingInLaravel as $missing) {
    echo $missing . "\n";
}
