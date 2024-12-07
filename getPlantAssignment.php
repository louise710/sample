<?php
include 'connectddc.php';

$sql = "SELECT * FROM plantpublish";
$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        
        // Publish the data to the respective MQTT topics
        if (isset($row['locationID'])) {
            if($row['locationID'] == 1){
                shell_exec("mosquitto_pub -h 10.10.50.119 -t sisig/basak/phmindata -m '{$row['phmin']}'");
                shell_exec("mosquitto_pub -h 10.10.50.119 -t sisig/basak/tdsmindata -m '{$row['tdsmin']}'");
                shell_exec("mosquitto_pub -h 10.10.50.119 -t sisig/basak/phmaxdata -m '{$row['phmax']}'");
                shell_exec("mosquitto_pub -h 10.10.50.119 -t sisig/basak/tdsmaxdata -m '{$row['tdsmax']}'");
            } elseif($row['locationID'] == 2){
                shell_exec("mosquitto_pub -h 10.10.50.119 -t sisig/sn/phmindata -m '{$row['phmin']}'");
                shell_exec("mosquitto_pub -h 10.10.50.119 -t sisig/sn/tdsmindata -m '{$row['tdsmin']}'");
                shell_exec("mosquitto_pub -h 10.10.50.119 -t sisig/sn/phmaxdata -m '{$row['phmax']}'");
                shell_exec("mosquitto_pub -h 10.10.50.119 -t sisig/sn/tdsmaxdata -m '{$row['tdsmax']}'");
            }
        } else {
            // Log or handle the error that locationID is not set
            echo "Error: locationID is not set in the row data.";
        }
    }
} else {
    echo "0 results";
}

?>
