<?php
// index.php

// Get the requested URL path
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim($uri, '/');

// Remove "greenhouse" from the beginning if it's there
$baseFolder = 'greenhouse';
if (strpos($uri, $baseFolder) === 0) {
    $uri = substr($uri, strlen($baseFolder));
    $uri = trim($uri, '/');
}

// Simple route-to-controller mapping
$routes = [
    # Current routes
    'current' => [
        'file' => 'current/Current.php',
        'class' => 'Current',
        'method' => 'getAllCurrent',
    ],

    # Measurements routes
    'measurements/days' => [
        'file' => 'measurements/Measurements.php',
        'class' => 'Measurements',
        'method' => 'getByDays',
    ],
    'measurements/interval' => [
        'file' => 'measurements/Measurements.php',
        'class' => 'Measurements',
        'method' => 'getByInterval',
    ],
    'measurements/current' => [
        'file' => 'measurements/Measurements.php',
        'class' => 'Measurements',
        'method' => 'getCurrent',
    ],

    # Controls routes
    'controls/days' => [
        'file' => 'controls/Controls.php',
        'class' => 'Controls',
        'method' => 'getByDays',
    ],
    'controls/interval' => [
        'file' => 'controls/Controls.php',
        'class' => 'Controls',
        'method' => 'getByInterval',
    ],
    'controls/current' => [
        'file' => 'controls/Controls.php',
        'class' => 'Controls',
        'method' => 'getCurrent',
    ],
    'controls/light' => [
        'file' => 'controls/Controls.php',
        'class' => 'Controls',
        'method' => 'setLight',
    ],
    'controls/fans' => [
        'file' => 'controls/Controls.php',
        'class' => 'Controls',
        'method' => 'setFans',
    ],
    'controls/valve' => [
        'file' => 'controls/Controls.php',
        'class' => 'Controls',
        'method' => 'setValve',
    ],
    'controls/heater' => [
        'file' => 'controls/Controls.php',
        'class' => 'Controls',
        'method' => 'setHeater',
    ],

    # Mesh routes
    'mesh/low_res' => [
        'file' => 'mesh/Mesh.php',
        'class' => 'Mesh',
        'method' => 'getLowRes',
    ],
    'mesh/high_res' => [
        'file' => 'mesh/Mesh.php',
        'class' => 'Mesh',
        'method' => 'getHighRes',
    ],

    # Reconstruction
    'reconstruction' => [
        'file' => 'reconstruction/Reconstruction.php',
        'class' => 'Reconstruction',
        'method' => 'getData',
    ],

    # Status
    'status' => [
        'file' => 'status/Status.php',
        'class' => 'Status',
        'method' => 'getStatus',
    ],

    # Disruptive
    'disruptive/days' => [
        'file' => 'disr/Disr.php',
        'class' => 'Disr',
        'method' => 'getByDays',
    ],

    # Plant data
    'plant_data' => [
        'file' => 'plant_data/PlantData.php',
        'class' => 'PlantData',
        'method' => 'getPlantData',
    ],

    # Plant images
    'image/newest' => [
        'file' => 'plant_images/PlantImages.php',
        'class' => 'PlantImages',
        'method' => 'getNewest',
    ],
];

// Check if route exists
if (isset($routes[$uri])) {
    $route = $routes[$uri];

    // Include the controller file
    if (file_exists($route['file'])) {
        require_once $route['file'];

        // Check if class exists
        if (class_exists($route['class'])) {
            $controller = new $route['class']();

            // Check if method exists
            if (method_exists($controller, $route['method'])) {
                // Call the method
                $controller->{$route['method']}();
            } else {
                http_response_code(404);
                echo "Method not found.";
            }
        } else {
            http_response_code(404);
            echo "Route file not found.";
        }
    } else {
        http_response_code(404);
        echo "Class not found.";
    }
} else {
    http_response_code(404);
    echo "Route not found.";
}
?>