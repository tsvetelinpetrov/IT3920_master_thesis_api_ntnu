<?php
// measurements/Measurements.php
class Current {
    public function getAllCurrent() {
        $filePath = __DIR__ . '/current.json';
        if (file_exists($filePath)) {
            $data = file_get_contents($filePath);
            $jsonData = json_decode($data, true);
            $currentControls = $this->getCurrentControls();
            if ($jsonData !== null) {
                if ($currentControls !== null) {
                    $jsonData['controls'] = $currentControls;
                }

                header('Content-Type: application/json');
                echo json_encode($jsonData);
            } else {
                http_response_code(500);
                echo "Error decoding JSON data.";
            }
        } else {
            http_response_code(404);
            echo "File not found.";
        }
    }

    private function getCurrentControls() {
        $controlsPath = __DIR__ . '/../controls/current.json';
        if (file_exists($controlsPath)) {
            $data = file_get_contents($controlsPath);
            $jsonData = json_decode($data, true);
            if ($jsonData !== null) {
                return $jsonData;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}
?>