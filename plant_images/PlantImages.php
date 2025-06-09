<?php
// plant_images/PlantImages.php
class PlantImages {
    public function getNewest() {
        $filePath = __DIR__ . '\newest.jpg';

        # Return content of the file
        if (file_exists($filePath)) {
            header('Content-Type: image/jpeg');
            echo file_get_contents($filePath);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'File not found.']);
        }
    }
}
?>