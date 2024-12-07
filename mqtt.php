<?php
$mqttCommand = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/status -m active';
$mqttCommand1 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/phdata -m "m"';
$mqttCommand2 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/tdsdata -m "m"';

//For pub display values
$mqttCommand21 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/phmaxdata -m "m"';
$mqttCommand22 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/tdsmindata -m "m"';
$mqttCommand23 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/phmindata -m "m"';
$mqttCommand24 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/tdsmaxdata -m "m"';

//motors
$mqttCommand3 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/motorphplusstatus -m "m"';
$mqttCommand4 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/motorphminusstatus -m "m"';
$mqttCommand5 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/motorvitastatus -m "m"';
$mqttCommand6 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/motorvitbstatus -m "m"';
$mqttCommand7 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/waterstatus -m "m"';
$mqttCommand8 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/motormixstatus -m "m"';

//manualmotor time
$mqttCommand9 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/manualmotorwatertime -m "m"'; 
$mqttCommand10 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/manualphmotorplustime -m "m"';
$mqttCommand11 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/manualphmotorminustime -m "m"';
$mqttCommand12 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/manualmotorvitatime -m "m"';
$mqttCommand13 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/manualmotorvitbtime -m "m"';
$mqttCommand14 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/manualmotormixtime -m "m"';
//manual activivate
$mqttCommand15 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/manualmotorwater -m "m"'; 
$mqttCommand16 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/manualphmotorplus -m "m"';
$mqttCommand17 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/manualphmotorminus -m "m"';
$mqttCommand18 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/manualmotorvita -m "m"';
$mqttCommand19 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/manualmotorvitb -m "m"';
$mqttCommand20 = 'mosquitto_pub -h 10.10.50.119 -t sisig/basak/manualmotormix -m "m"';

exec($mqttCommand, $output, $returnCode);
exec($mqttCommand1, $output1, $returnCode1);
exec($mqttCommand2, $output2, $returnCode2);

exec($mqttCommand3, $output3, $returnCode3);
exec($mqttCommand4, $output4, $returnCode4);
exec($mqttCommand5, $output5, $returnCode5);
exec($mqttCommand6, $output6, $returnCode6);
exec($mqttCommand7, $output7, $returnCode7);
exec($mqttCommand8, $output8, $returnCode8);

exec($mqttCommand9, $output9, $returnCode9);
exec($mqttCommand10, $output10, $returnCode10);
exec($mqttCommand11, $output11, $returnCode11);
exec($mqttCommand12, $output12, $returnCode12);
exec($mqttCommand13, $output13, $returnCode13);
exec($mqttCommand14, $output14, $returnCode14);

exec($mqttCommand15, $output15, $returnCode15);
exec($mqttCommand16, $output16, $returnCode16);
exec($mqttCommand17, $output17, $returnCode17);
exec($mqttCommand18, $output18, $returnCode18);
exec($mqttCommand19, $output19, $returnCode19);
exec($mqttCommand20, $output20, $returnCode20);


exec($mqttCommand17, $output17, $returnCode21);
exec($mqttCommand18, $output18, $returnCode22);
exec($mqttCommand19, $output19, $returnCode23);
exec($mqttCommand20, $output20, $returnCode24);

if ($returnCode === 0 && $returnCode1 === 0 && $returnCode2 === 0 && $returnCode3 === 0 && $returnCode4 === 0 &&
    $returnCode5 === 0 && $returnCode6 === 0 && $returnCode7 === 0 && $returnCode8 === 0 && $returnCode9 === 0 && $returnCode10 === 0 && $returnCode11 === 0 && $returnCode12 === 0 && $returnCode13 === 0 && $returnCode14 === 0 && $returnCode15 === 0 && $returnCode16 === 0 && $returnCode17 === 0 && $returnCode18 === 0 && $returnCode19 ===0 && $returnCode20 === 0 && $returnCode21 === 0 && $returnCode22 === 0 && $returnCode23 === 0 && $returnCode24 === 0) {
    echo 'MQTT commands executed successfully.';
} else {
    echo 'Error executing MQTT commands.';
}
?>
