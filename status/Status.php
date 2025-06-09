<?php
// status/Status.php
class Status {
    public function getStatus() {
        header('Content-Type: application/json');
        echo "Connected";
    }
}
?>