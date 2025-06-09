<?php
// mesh/Mesh.php
class Mesh {
    public function getLowRes() {
        $filePath = __DIR__ . '\mesh_low_res.obj';

        # Return content of the file
        if (file_exists($filePath)) {
            echo file_get_contents($filePath);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'File not found.']);
        }
    }

    public function getHighRes() {
        $filePath = __DIR__ . '\mesh_high_res.obj';

        # Return content of the file
        if (file_exists($filePath)) {
            echo file_get_contents($filePath);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'File not found.']);
        }
    }
}
?>