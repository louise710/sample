<?php
include 'connectddc.php';
include 'getPlantAssignment.php';
// Initialize variables
$plantName = '';
$phmax = '';
$phmin = '';
$tdsmax = '';
$tdsmin = '';
$locationID = isset($_GET['id']) ? intval($_GET['id']) : null; 
echo "<script>console.log('Initial locationID from GET: " . $locationID . "');</script>";
// Fetch the latest locationplant record
$sqlLocation = 'SELECT * FROM locationplant ORDER BY lpdate DESC, lptime DESC LIMIT 1';
$resultLocation = $mysqli->query($sqlLocation);

if ($rowLocation = $resultLocation->fetch_assoc()) {
    $plantID = $rowLocation['plantID'];

    // Fetch plant info for the selected plantID
    $sqlPlant = "SELECT * FROM plantinfo WHERE plantID = ?";
    $stmt = $mysqli->prepare($sqlPlant);
    if ($stmt) {
        $stmt->bind_param("i", $plantID);
        $stmt->execute();
        $resultPlant = $stmt->get_result();

        if ($plantRow = $resultPlant->fetch_assoc()) {
            $plantName = $plantRow["plantname"];
            $phmax = $plantRow["phmax"];
            $phmin = $plantRow["phmin"];
            $tdsmax = $plantRow["tdsmax"];
            $tdsmin = $plantRow["tdsmin"];

            // Update the plantpublish table with the fetched plant details
            $sqlUpdate = "UPDATE plantpublish 
                          SET plantname = ?, phmin = ?, phmax = ?, tdsmin = ?, tdsmax = ?
                          WHERE locationID = (SELECT locationID FROM locationplant ORDER BY lpdate DESC, lptime DESC LIMIT 1)";
            $stmtUpdate = $mysqli->prepare($sqlUpdate);
            if ($stmtUpdate) {
                $stmtUpdate->bind_param("sdddd", $plantName, $phmin, $phmax, $tdsmin, $tdsmax);
                if ($stmtUpdate->execute()) {
                    echo "Plant publish record updated successfully.<br>";
                } else {
                    echo "Error updating plantpublish record: " . $stmtUpdate->error . "<br>";
                }
                $stmtUpdate->close();
            } else {
                echo "Error preparing update statement: " . $mysqli->error . "<br>";
            }
        } else {
            echo "No plant details found.";
        }
        $stmt->close();
    } else {
        echo "Error: Unable to prepare statement.";
    }
} else {
    echo "No data found.";
}

// Generate plant options for the dropdown
$plantOpt = "<option value='' disabled selected>--Select a plant--</option>";
$sqlAllPlants = "SELECT * FROM plantinfo";
$resultAllPlants = $mysqli->query($sqlAllPlants);

$plantData = [];
if ($resultAllPlants->num_rows > 0) {
    while ($rowPlant = $resultAllPlants->fetch_assoc()) {
        $plantID = $rowPlant["plantID"];
        $plantname = $rowPlant["plantname"];
        $plantOpt .= "<option value='$plantID'>$plantname</option>";
        $plantData[$plantID] = [
            'phmax' => $rowPlant["phmax"],
            'phmin' => $rowPlant["phmin"],
            'tdsmax' => $rowPlant["tdsmax"],
            'tdsmin' => $rowPlant["tdsmin"]
        ];
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <script src="mqttws31.js" type="text/javascript"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- mqtt broker -->
        <script type="text/javascript" language="javascript">
            var mqtt;
            var reconnectTimeout = 2000;
            var host = "10.10.50.119";
            var port = 11884;
            var enable = false;
            var timer;
            function onMessageArrived(msg) {
                var statusButton = document.getElementById("mqttButton");
                if (msg.destinationName === "sisig/basak/status") {
                    if (msg.payloadString === "active") {
                        statusButton.style.backgroundColor = "#04AA6D";
                        statusButton.innerText = "Active";
                        resetTimer();
                    } else {
                        statusButton.style.backgroundColor = "#DC3545";
                        startTimer();
                    }
                }
                // value of PH and TDS inside table
                if (msg.destinationName === "sisig/basak/phdata") {
                    handlePhMessage(msg.payloadString);
                } else if (msg.destinationName === "sisig/basak/tdsdata") {
                    handleTDSMessage(msg.payloadString);
                }
                // handle motors
                if (msg.destinationName === "sisig/basak/motorphplusstatus") {
                    handleMotorStatus("phplus", msg.payloadString);
                } else if (msg.destinationName === "sisig/basak/motorphminusstatus") {
                    handleMotorStatus("phminus", msg.payloadString);
                } else if (msg.destinationName === "sisig/basak/motorvitastatus") {
                    handleMotorStatus("vitA", msg.payloadString);
                } else if (msg.destinationName === "sisig/basak/motorvitbstatus") {
                    handleMotorStatus("vitB", msg.payloadString);
                } else if (msg.destinationName === "sisig/basak/waterstatus") {
                    handleMotorStatus("water", msg.payloadString);
                } else if (msg.destinationName === "sisig/basak/motormixstatus") {
                    handleMotorStatus("mix", msg.payloadString);
                }
            }
            function handlePhMessage(msg) {
                var phButton = document.getElementById("phVal");
                phButton.innerText = msg;
            }
            function handleTDSMessage(msg) {
                var tdsButton = document.getElementById("tdsVal");
                tdsButton.innerText = msg;
            }
//display for ph and tds
            function sendMessage(destination, value) {
                var message = new Paho.MQTT.Message(value);
                message.destinationName = destination;
                mqtt.send(message);
            }
     

            function handleMotorStatus(motorId, msg) {
                var motorButton = document.getElementById(motorId);
                if (msg === "active") {
                    motorButton.style.backgroundColor = "#04AA6D";
                    motorButton.innerText = "Active";
                } else if (msg === "inactive"){
                    motorButton.style.backgroundColor = "#DC3545";
                    motorButton.innerText = "Not Active";
                }
            }

            function onConnect() {
                console.log("Connected ");
                mqtt.subscribe("sisig/basak/status");

                mqtt.subscribe("sisig/basak/phdata");
                mqtt.subscribe("sisig/basak/tdsdata");

                mqtt.subscribe("sisig/basak/phmaxdata");
                mqtt.subscribe("sisig/basak/tdsmindata");
                mqtt.subscribe("sisig/basak/phmindata");
                mqtt.subscribe("sisig/basak/tdsmaxdata");
          

                mqtt.subscribe("sisig/basak/motorphplusstatus");
                mqtt.subscribe("sisig/basak/motorphminusstatus");
                mqtt.subscribe("sisig/basak/motorvitastatus");
                mqtt.subscribe("sisig/basak/motorvitbstatus");
                mqtt.subscribe("sisig/basak/waterstatus");
                mqtt.subscribe("sisig/basak/motormixstatus");

                var messageStatus = new Paho.MQTT.Message("refresh");
                messageStatus.destinationName = "sisig/basak/status";
                mqtt.send(messageStatus);
        
                var messagePh = new Paho.MQTT.Message("refresh");
                messagePh.destinationName = "sisig/basak/phdata";
                mqtt.send(messagePh);
                var messageTDS = new Paho.MQTT.Message("refresh");
                messageTDS.destinationName = "sisig/basak/tdsdata";
                mqtt.send(messageTDS);

                var msgphplus = new Paho.MQTT.Message("refresh");
                msgphplus.destinationName = "sisig/basak/motorphplusstatus";
                mqtt.send(msgphplus);
                var msgphminus = new Paho.MQTT.Message("refresh");
                msgphminus.destinationName = "sisig/basak/motorphminusstatus";
                mqtt.send(msgphminus);
                var msgvita = new Paho.MQTT.Message("refresh");
                msgvita.destinationName = "sisig/basak/motorvitastatus";
                mqtt.send(msgvita);
                var msgvitb = new Paho.MQTT.Message("refresh");
                msgvitb.destinationName = "sisig/basak/motorvitbstatus";
                mqtt.send(msgvitb);
                var msgwater = new Paho.MQTT.Message("refresh");
                msgwater.destinationName = "sisig/basak/waterstatus";
                mqtt.send(msgwater);
                var msgmix = new Paho.MQTT.Message("refresh");
                msgmix.destinationName = "sisig/basak/motormixstatus";
                mqtt.send(msgmix);

                close();
            }

            function MQTTconnect() {
                console.log("Connecting to " + host + " " + port);
                var randomNum = Math.floor(Math.random() * 100);
                mqtt = new Paho.MQTT.Client(host, port, "status".concat(randomNum));
                var options = {
                    timeout: 3,
                    onSuccess: onConnect,
                };

                mqtt.onMessageArrived = onMessageArrived;

                mqtt.connect(options);
            }

            function startTimer() {
                enable = true;
                timer = setTimeout(function() {
                    close();
                }, 10000); // 10seconds
            }

            function resetTimer() {
                clearTimeout(timer);
                startTimer();
            }

            function close() {
                enable = false;
                document.getElementById("mqttButton").innerText = "Not Active";
                document.getElementById("mqttButton").style.backgroundColor = "#DC3545";
            }

        


            window.onload = MQTTconnect;
        </script>

        <title>SISIG</title>
        <style>
            table {
                border-collapse: collapse;
                width: 100%;
                margin-left: 0%;
            }

            th, td {
                padding: 8px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }
            input[type="text"] {
                border: none;
                border-bottom: 1px solid #ccc; 
                box-sizing: border-box; 
                background-color: #00000000;
            }
            .modal-dialog {
                max-width: 800px;
            }  
        </style>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">
    <?php include 'header.php';?>
        <div id="layoutSidenav">
            <?php include 'sidenav.php';?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4"><?php echo $selectedLocationName; ?></h1>
                        <ol class="breadcrumb mb-4">
                            <!-- <li class="breadcrumb-item active">SPACE</li> -->
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <table><td style="text-align: left;"><label for="stat"> Stat: </label>
                                <button style="background-color: #DC3545;" id="mqttButton">Not Active</button></td></table>
                                <script>
                                    MQTTconnect();
                                    </script>
                              
                                    
                            <form method="post">
                                <table>
                                    <tr>
                                        <td><label for="plantname">Plant: </label></td>
                                        <td><input type="text" name="plantname" id="plantname" value="<?php echo htmlspecialchars($plantName); ?>" disabled></td>
                                        <td>
                                            <button type="button" id="openModalBtn" class="btn btn-primary">Select Plant</button>
                                            <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="formModalLabel">Plant Information</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form method="post">
                                                                <table class="table">
                                                                    <tr>
                                                                        <td><label for="plantname">Plant: </label></td>
                                                                        <td>
                                                                            <select id="plantSelect" name="plantID" required onchange="displayPlantData(this.value)">
                                                                                <?php echo $plantOpt; ?>
                                                                            </select>
                                                                        </td> 
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="phmax">Ph Max: </label></td>
                                                                        <td><input type="text" name="phmax" id="phmax" disabled></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="phmin">Ph Min: </label></td>
                                                                        <td><input type="text" name="phmin" id="phmin" disabled></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="tdsmax">TDS Max: </label></td>
                                                                        <td><input type="text" name="tdsmax" id="tdsmax" disabled></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="tdsmin">TDS Min: </label></td>
                                                                        <td><input type="text" name="tdsmin" id="tdsmin" disabled></td>
                                                                    </tr>
                                                                </table>

                                                                <input type="hidden" name="locationID" id="location-id" value="<?= htmlspecialchars($locationID) ?>">
                                                                <button type="submit" id="saveButton" class="btn btn-success">Save</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label for="phmax1">Ph Max: </label></td>
                                        <td><input type="text" name="phmax1" id="phmax1" value="<?php echo htmlspecialchars($phmax); ?>" disabled></td>
                                        <td><label for="phmin1">Ph Min: </label></td>
                                        <td><input type="text" name="phmin" id="phmin1" value="<?php echo htmlspecialchars($phmin); ?>" disabled></td>
                                    </tr>
                                    <tr>
                                        <td><label for="tdsmax1">TDS Max: </label></td>
                                        <td><input type="text" name="tdsmax" id="tdsmax1" value="<?php echo htmlspecialchars($tdsmax); ?>" disabled></td>
                                        <td><label for="tdsmin1">TDS Min: </label></td>
                                        <td><input type="text" name="tdsmin" id="tdsmin1" value="<?php echo htmlspecialchars($tdsmin); ?>" disabled></td>
                                    </tr>
                                </table>
                            </form>

                            </div>

                            <div class="card-body">
                                <?php
include 'connectddc.php';
$sqlphtds = 'SELECT * FROM phtds ORDER BY phtdsdate DESC, phtdstime DESC LIMIT 1';
$result = mysqli_query($mysqli, $sqlphtds);
if ($result && mysqli_num_rows($result) > 0) {
    // Fetch the data
    $row = mysqli_fetch_assoc($result);
    
    $phtdsdate = $row['phtdsdate'];
    $phtdstime = $row['phtdstime'];
    $phvalue = $row['phvalue'];
    $tdsvalue = $row['tdsvalue'];
}

                                // Output table data
                                echo "<table id='datatablesSimple' class='table'>
                                <thead>  
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Ph Value</th>
                                        <th>TDS Value</th>
                                    </tr>
                                    <tr>
                                        <th>$phtdsdate</th>
                                        <th>$phtdstime</th>
                                        <th> 
                                        <button style='background-color: white;' id='phVal'>$phvalue</button>
                                        </th>
                                        <th>
                                            <button style='background-color: white;' id='tdsVal'>$tdsvalue</button>
                                        </th>
                                    </tr>
                                </thead>
                              <tbody>";

                            

                              echo "</tbody>
                                </table>";
                                $mysqli->close();

                                ?>
                        </div>
                    </div>
                         <div class="card-body">
                            <?php
                                echo "<table id='datatablesSimple' class='table'>
                                <thead>  
                                    <tr>
                                        <th>Motors:</th>
                                    </tr>
                                    <tr>
                                        <th> 
                                        <span>Ph+ </span><button style='background-color: #DC3545;' id='phplus'>Not Active</button>
                                        </th>
                                        <th>
                                        <span>Vitamin A </span><button style='background-color: #DC3545;' id='vitA'>Not Active</button>
                                        </th>
                                        <th>
                                        <span>Water </span><button style='background-color: #DC3545;' id='water'>Not Active</button>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th>
                                        <span>Ph- </span><button style='background-color: #DC3545;' id='phminus'>Not Active</button>
                                        </th>
                                        <th>
                                        <span>Vitamin B </span><button style='background-color: #DC3545;' id='vitB'>Not active</button>
                                        </th>
                                        <th>
                                        <span>Mix </span><button style='background-color: #DC3545;' id='mix'>Not Active</button>
                                        </th>
                                    </tr>
                                </thead>
                              <tbody>";
                              ?>
                         </div>
                </main>
            </div>
        </div>
        
    <script>
        document.getElementById('openModalBtn').addEventListener('click', function () {
            var myModal = new bootstrap.Modal(document.getElementById('formModal'), {
                keyboard: false
            });
            myModal.show();
        });

        function displayPlantData(plantID) {
            var plantData = <?php echo json_encode($plantData); ?>;
            if (plantID in plantData) {
                document.getElementById('phmax').value = plantData[plantID]['phmax'];
                document.getElementById('phmin').value = plantData[plantID]['phmin'];
                document.getElementById('tdsmax').value = plantData[plantID]['tdsmax'];
                document.getElementById('tdsmin').value = plantData[plantID]['tdsmin'];
            } else {
                document.getElementById('phmax').value = '';
                document.getElementById('phmin').value = '';
                document.getElementById('tdsmax').value = '';
                document.getElementById('tdsmin').value = '';
            }
        }

        document.getElementById('saveButton').addEventListener('click', function() {
            var plantID = document.getElementById('plantSelect').value;
            var locationID = document.getElementById('location-id').value;

            // Create an AJAX request to save the plantID
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "savePlantLoc.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    alert(xhr.responseText); // Show success message
                    // Update current plant name in PHP session
                    setCurrentPlantName(document.getElementById('plantname').value);
                    // Update current plant table
                    updateCurrentPlantTable();
                }
            };
            xhr.send("plantID=" + encodeURIComponent(plantID) + "&locationID=" + encodeURIComponent(locationID));
        });
    </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    </body>
</html>
