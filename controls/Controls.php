<?php
// controls/Controls.php
class Controls {
    # Global variables
    private $manualMode = false; // Flag to indicate if manual mode is enabled

    private $currentDataFile = __DIR__ . '/current.json'; // Path to the current data file
    private $dataFile = __DIR__ . '/data.json'; // Path to the data file


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
                    $entryDate->setTimezone(new DateTimeZone('UTC')); // Set timezone to UTC

                    $last_record_time = 1745880781000;
                    $currentDate = new DateTime('@' . $last_record_time/1000); // Convert milliseconds to seconds

                    //$currentDate = new DateTime('now', new DateTimeZone('UTC'));
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
        # Return the most recent MeasurementTime data point
        # Json format: [{"MeasurementTime":1727947632000,"Temperature":30.5,"Moisture":722,"CO2":0,"LightIntensity":802,"Humidity":51.6,"TankLevel":0},...]
        if (file_exists($this->currentDataFile)) {
            $data = file_get_contents($this->currentDataFile);
            
            header('Content-Type: application/json');
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'File not found.']);
        }
    }

    public function setLight() {
        $state = $_GET['state'] ?? null; // safe access
        
        // Await for 2 seconds
        // sleep(2);

        # Check if state is valid boolean and is not null
        if ($state === null || !in_array($state, ['true', 'false'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or missing state parameter.']);
            return;
        }

        if ($this->manualMode) {
            http_response_code(503);
            echo json_encode(['error' => 'Light state not set. Device is not in manual control mode, it is in Experiment mode']);
            return;
        }

        $state = $state === 'true' ? true : false; // cast to boolean

        $data = file_get_contents($this->currentDataFile);

        # Format of data: {"MeasurementTime":1732879971000,"HeaterDutyCycle":0.0,"LightOn":false,"FanOn":true,"ValveOpen":true}
        $jsonData = json_decode($data, true);

        if ($jsonData !== null) {
            // Update the LightOn state
            $jsonData['LightOn'] = $state;

            // Save the updated data back to the file
            if (file_put_contents($this->currentDataFile, json_encode($jsonData)) !== false) {
                header('Content-Type: application/json');
                echo json_encode(['success' => 'Light state updated successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error saving data.']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error decoding JSON data.']);
        }
    }

    public function setFans() {
        $state = $_GET['state'] ?? null; // safe access

        # Check if state is valid boolean and is not null
        if ($state === null || !in_array($state, ['true', 'false'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or missing state parameter.']);
            return;
        }

        if ($this->manualMode) {
            http_response_code(503);
            echo json_encode(['error' => 'Fan state not set. Device is not in manual control mode, it is in Experiment mode']);
            return;
        }

        $state = $state === 'true' ? true : false; // cast to boolean

        $data = file_get_contents($this->currentDataFile);

        # Format of data: {"MeasurementTime":1732879971000,"HeaterDutyCycle":0.0,"LightOn":false,"FanOn":true,"ValveOpen":true}
        $jsonData = json_decode($data, true);

        if ($jsonData !== null) {
            // Update the FanOn state
            $jsonData['FanOn'] = $state;

            // Save the updated data back to the file
            if (file_put_contents($this->currentDataFile, json_encode($jsonData)) !== false) {
                header('Content-Type: application/json');
                echo json_encode(['success' => 'Fan state updated successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error saving data.']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error decoding JSON data.']);
        }
    }

    public function setValve() {
        $state = $_GET['state'] ?? null; // safe access

        # Check if state is valid boolean and is not null
        if ($state === null || !in_array($state, ['true', 'false'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or missing state parameter.']);
            return;
        }

        if ($this->manualMode) {
            http_response_code(503);
            echo json_encode(['error' => 'Valve state not set. Device is not in manual control mode, it is in Experiment mode']);
            return;
        }

        $state = $state === 'true' ? true : false; // cast to boolean

        $data = file_get_contents($this->currentDataFile);

        # Format of data: {"MeasurementTime":1732879971000,"HeaterDutyCycle":0.0,"LightOn":false,"FanOn":true,"ValveOpen":true}
        $jsonData = json_decode($data, true);

        if ($jsonData !== null) {
            // Update the ValveOpen state
            $jsonData['ValveOpen'] = $state;

            // Save the updated data back to the file
            if (file_put_contents($this->currentDataFile, json_encode($jsonData)) !== false) {
                header('Content-Type: application/json');
                echo json_encode(['success' => 'Valve state updated successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error saving data.']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error decoding JSON data.']);
        }
    }

    public function setHeater() {
        $value = $_GET['value'] ?? null; // safe access

        // http_response_code(503);
        // echo json_encode(['error' => 'Heater state not set. Device is not in manual control mode, it is in Experiment mode']);
        // return;

        # ensure value is a numeric value and is not null and is between 0 and 1
        if ($value === null || !is_numeric($value) || $value < 0 || $value > 1) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or missing value parameter.']);
            return;
        }

        if ($this->manualMode) {
            http_response_code(503);
            echo json_encode(['error' => 'Heater state not set. Device is not in manual control mode, it is in Experiment mode']);
            return;
        }

        $value = (float)$value; // cast to float

        $data = file_get_contents($this->currentDataFile);

        # Format of data: {"MeasurementTime":1732879971000,"HeaterDutyCycle":0.0,"LightOn":false,"FanOn":true,"ValveOpen":true}
        $jsonData = json_decode($data, true);

        if ($jsonData !== null) {
            // Update the HeaterDutyCycle value
            $jsonData['HeaterDutyCycle'] = $value;

            // Save the updated data back to the file
            if (file_put_contents($this->currentDataFile, json_encode($jsonData)) !== false) {
                header('Content-Type: application/json');
                echo json_encode(['success' => 'Heater state updated successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error saving data.']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error decoding JSON data.']);
        }
    }
}
?>