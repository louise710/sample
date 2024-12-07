<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>SISIG</title>
        <style>
            #editModal .modal-content {
                margin: 5% auto;
                padding: 20px;
                border: 1px solid #888;
            }
            #editModal .modal-content {
                width: 45%;
                height: 78%;
            }

            .close {
                color: #aaa;
                font-size: 28px;
                font-weight: bold;
                display: block;
                text-align: right;
            }

            .close:hover,
            .close:focus {
                color: black;
                text-decoration: none;
                cursor: pointer;
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
                        <!-- <h1 class="mt-4">PLANT INFO</h1> -->
                        <ol class="breadcrumb mb-4">
                            <!-- <li class="breadcrumb-item active">LIST OF PLANTS</li> -->
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                PLANTS
                            </div>
                            <div class="card-body">
                                //for value display of your database
                                <?php
                                include 'connectddc.php';

                                $sql = "SELECT * FROM plantinfo";

                                $stmt = $mysqli->prepare($sql);
                                if ($stmt === false) {
                                    die("Error preparing statement: " . $mysqli->error);
                                }
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result === false) {
                                    die("Error executing query: " . $stmt->error);
                                }

                                // Output table data
                                echo "<table id='datatablesSimple' class='table'>
                                <thead>  
                                    <tr>
                                        <th>Plant ID</th>
                                        <th>Plant Name</th>
                                        <th>Description</th>
                                        <th>Ph Max</th>
                                        <th>Ph Min</th>
                                        <th>TDS Max</th>
                                        <th>TDS Min</th>
                                        <th>Operation</th>
                                    </tr>
                                </thead>
                              <tbody>";
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                            <td>" . $row["plantID"] . "</td>
                                            <td>" . $row["plantname"] . "</td>
                                            <td>" . $row["plantdesc"] . "</td>
                                            <td>" . $row["phmax"] . "</td>
                                            <td>" . $row["phmin"] . "</td>
                                            <td>" . $row["tdsmax"] . "</td>
                                            <td>" . $row["tdsmin"] . "</td>
                                            <td>" . $row["operation"] . "
                                            <button onclick=\"openModal('{$row['plantID']}')\">Update</button></td>
                                          </tr>";
                                }

                                echo "</tbody>
                                        </table>";

                                $stmt->close();
                                $mysqli->close();
                                ?>
                            </div>
                            <div id="editModal" class="modal">
                                <div class="modal-content">
                                    <span class="close" onclick="closeModal()">&times;</span>
                                    <div id="plantupdate"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script>
            
            function openModal(plantID) {
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        document.getElementById("plantupdate").innerHTML = this.responseText;
                        document.getElementById("editModal").style.display = "block";
                    }
                };
                xhttp.open("GET", "plantupdate.php?plantID=" + plantID, true);
                xhttp.send();
            }

            function closeModal() {
                document.getElementById("editModal").style.display = "none";
            }
        </script>
    </body>
</html>
