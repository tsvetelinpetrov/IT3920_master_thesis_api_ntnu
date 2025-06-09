<?php
// plant_data/PlantData.php
class PlantData {
    public function getPlantData() {
        $filePath = __DIR__ . '\plant_data.json';

        # Return content of the file
        if (file_exists($filePath)) {
            header('Content-Type: application/json');
            echo file_get_contents($filePath);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'File not found.']);
        }
    }
}
?>