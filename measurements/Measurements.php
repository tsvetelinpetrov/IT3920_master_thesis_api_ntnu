<?php
// measurements/Measurements.php
class Measurements {
    public function getByDays() {
        $num_days = $_GET['num_days'] ?? null; // safe access
        
        if ($num_days === null || !is_numeric($num_days)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or missing num_days parameter.']);
            return;
        }

        $num_days = (int)$num_days; // cast to int
        if ($num_days <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'num_days must be a positive integer.']);
            return;
        }

        $filePath = __DIR__ . '/data.json';

        # Return data for the last num_days days
        # Json format: [{"MeasurementTime":1727947632000,"Temperature":30.5,"Moisture":722,"CO2":0,"LightIntensity":802,"Humidity":51.6,"TankLevel":0},...]
        if (file_exists($filePath)) {
            $data = file_get_contents($filePath);
            $jsonData = json_decode($data, true);

            if ($jsonData !== null) {
                // Filter the data for the last num_days days
                $filteredData = array_filter($jsonData, function($entry) use ($num_days) {
                    $entryDate = new DateTime('@' . ($entry['MeasurementTime'] / 1000)); // Convert milliseconds to seconds
                    // $entryDate->setTimezone(new DateTimeZone('UTC')); // Set timezone to UTC

                    $last_record_time = 1745880781000;
                    //$currentDate = new DateTime('now', new DateTimeZone('UTC'));
                    $currentDate = new DateTime('@' . $last_record_time/1000); // Convert milliseconds to seconds
                    return $entryDate >= $currentDate->sub(new DateInterval("P{$num_days}D"));
                });

                header('Content-Type: application/json');
                echo json_encode(array_values($filteredData)); // Re-index the array
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error decoding JSON data.']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'File not found.']);
        }
    }

    public function getByInterval() {
        $start_time = $_GET['start_time'] ?? null; // safe access
        $end_time = $_GET['end_time'] ?? null; // safe access

        if ($start_time === null || $end_time === null || !is_numeric($start_time) || !is_numeric($end_time)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or missing start_time or end_time parameter.']);
            return;
        }

        $start_time = (int)$start_time; // cast to int
        $end_time = (int)$end_time; // cast to int
        if ($start_time <= 0 || $end_time <= 0 || $start_time >= $end_time) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid time range.']);
            return;
        }

        $filePath = __DIR__ . '/data.json';

        # Return data for the time range between start_time and end_time
        # Json format: [{"MeasurementTime":1727947632000,"Temperature":30.5,"Moisture":722,"CO2":0,"LightIntensity":802,"Humidity":51.6,"TankLevel":0},...]
        if (file_exists($filePath)) {
            $data = file_get_contents($filePath);
            $jsonData = json_decode($data, true);

            if ($jsonData !== null) {
                // Filter the data for the time range
                $filteredData = array_filter($jsonData, function($entry) use ($start_time, $end_time) {
                    return $entry['MeasurementTime'] >= $start_time && $entry['MeasurementTime'] <= $end_time;
                });

                header('Content-Type: application/json');
                echo json_encode(array_values($filteredData)); // Re-index the array
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error decoding JSON data.']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'File not found.']);
        }
    }

    public function getCurrent() {
        $filePath = __DIR__ . '/data.json';

        # Return the most recent MeasurementTime data point
        # Json format: [{"MeasurementTime":1727947632000,"Temperature":30.5,"Moisture":722,"CO2":0,"LightIntensity":802,"Humidity":51.6,"TankLevel":0},...]
        if (file_exists($filePath)) {
            $data = file_get_contents($filePath);
            $jsonData = json_decode($data, true);

            if ($jsonData !== null && count($jsonData) > 0) {
                // Sort the data by MeasurementTime in descending order and get the first entry
                usort($jsonData, function($a, $b) {
                    return $b['MeasurementTime'] <=> $a['MeasurementTime'];
                });

                header('Content-Type: application/json');
                echo json_encode($jsonData[0]); // Return the most recent entry
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error decoding JSON data or no data available.']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'File not found.']);
        }
    }
}
?>