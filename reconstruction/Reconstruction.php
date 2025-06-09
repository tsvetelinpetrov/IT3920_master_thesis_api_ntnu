<?php
// reconstruction/Reconstruction.php
class Reconstruction {
    public function getData() {
        $filePath = __DIR__ . '\data.json';

        # Return content of the file
        if (file_exists($filePath)) {
            $data = file_get_contents($filePath);

            header('Content-Type: application/json');
            echo $data;
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'File not found.']);
        }
    }
}
?>