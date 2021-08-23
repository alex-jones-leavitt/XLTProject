<?php
ini_set('display_erros', 1);
error_reporting(E_ALL);
//The following lines of code maintain the session from the login screen				
session_start();
$session = $_SESSION['email'];
if($_SESSION['Type'] == "student") {
	header("Location: student.php");
}
else if($_SESSION['Type'] == "admin") {
	header("Location: admin.php");
}
//redirects to login if there is no session
if($session == NULL){
	header("Location: login.php");
}

date_default_timezone_set('America/Denver');
$date = date('Y-m-d');
$day = date('l', strtotime($date));

//Establishes connection with database and retrieves info for current user


// Create connection
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check connection
if (mysqli_connect_errno()) {
	die("Connection failed: " . mysqli_connect_error());
}
//echo "Connected successfully";

$teachersql = "SELECT UserID, FirstName, LastName, SubjectID, PartnerID FROM User WHERE Email like '".$session."';";
$teacherquery = mysqli_query($conn, $teachersql) or die('Error on teacherquery');
$teacherresult = mysqli_fetch_assoc($teacherquery);
$sectionsql = "SELECT SectionID, Name FROM Section WHERE TeacherID = ".$teacherresult['UserID'].";";
$sectionquery = mysqli_query($conn, $sectionsql) or die(mysqli_error($conn));
//Array that contains all of the sections
$sectionids = [];
if($sectionquery) {
	while ($sectionrow = mysqli_fetch_assoc($sectionquery)) {
		$section = array(
			'SectionID' => $sectionrow['SectionID'],
			'Name' => $sectionrow['Name']
		);
		$sections[] = $section;
		$sectionids[] = $sectionrow['SectionID'];
	}
}

else {
	//add error later
}

$studentidsql = "SELECT StudentID FROM Enrollment WHERE SectionID in (".implode(', ', $sectionids).");";
$studentidquery = mysqli_query($conn, $studentidsql) or die('Error on studentidquery');
//Array that contains all of the student ids
$studentids = [];
while($studentidresult = mysqli_fetch_assoc($studentidquery)) {
	array_push($studentids, $studentidresult['StudentID']);
}

$studentsql = "SELECT UserID, FirstName, LastName, Email, StudentNum, Grade FROM User WHERE UserID in (".implode(', ', $studentids).") ORDER BY LastName;";
$studentquery = mysqli_query($conn, $studentsql) or die('Error on studentquery');
//Array that contains all of the student data
if($studentquery) {
	while ($studentrow = mysqli_fetch_assoc($studentquery)) {
		$student = array(
			'StudentID' => $studentrow['UserID'],
			'FirstName' => $studentrow['FirstName'],
			'LastName' => $studentrow['LastName'],
			'Email' => $studentrow['Email'],
			'StudentNum' => $studentrow['StudentNum'],
			'Grade' => $studentrow['Grade']
		);
		$students[] = $student;
	}
}

$allstudentsql = "SELECT UserID, FirstName, LastName, Email, StudentNum, Grade FROM User WHERE UserType like 'Student' ORDER BY LastName;";
$allstudentquery = mysqli_query($conn, $allstudentsql) or die('Error on allstudentquery');
//Array that contains all of the student data
if($allstudentquery) {
	while ($allstudentrow = mysqli_fetch_assoc($allstudentquery)) {
		$allstudent = array(
			'StudentID' => $allstudentrow['UserID'],
			'FirstName' => $allstudentrow['FirstName'],
			'LastName' => $allstudentrow['LastName'],
			'Email' => $allstudentrow['Email'],
			'StudentNum' => $allstudentrow['StudentNum'],
			'Grade' => $allstudentrow['Grade']
		);
		$allstudents[] = $allstudent;
	}
}


else {
	//add error later
}

$homeroomsql = "SELECT SectionID FROM Section WHERE TeacherID = ".$teacherresult['UserID']." AND Name LIKE '%XLT%';";
$homeroomquery = mysqli_query($conn, $homeroomsql) or die('Error on homeroomquery');
$homeroom = [];
while($homeroomresult = mysqli_fetch_assoc($homeroomquery)) {
	array_push($homeroom, $homeroomresult['SectionID']);
}

$homeroomstudsql = "SELECT StudentID from Enrollment WHERE SectionID IN (".implode(', ', $homeroom).");";
$homeroomstudquery = mysqli_query($conn, $homeroomstudsql) or die(mysqli_error($conn));
$homeroomstuds = [];
while($homeroomstudresult = mysqli_fetch_assoc($homeroomstudquery)) {
	array_push($homeroomstuds, $homeroomstudresult['StudentID']);
}

$homeroomstudentsql = "SELECT UserID, FirstName, LastName, Email, StudentNum, Grade FROM User WHERE UserID in (".implode(', ', $homeroomstuds).") ORDER BY LastName;";
$homeroomstudentquery = mysqli_query($conn, $homeroomstudentsql) or die(mysqli_error($conn));
if($homeroomstudentquery) {
	while ($homeroomstudentrow = mysqli_fetch_assoc($homeroomstudentquery)) {
		$homeroomstudent = array(
			'UserID' => $homeroomstudentrow['UserID'],
			'FirstName' => $homeroomstudentrow['FirstName'],
			'LastName' => $homeroomstudentrow['LastName'],
			'Email' => $homeroomstudentrow['Email'],
			'StudentNum' => $homeroomstudentrow['StudentNum'],
			'Grade' => $homeroomstudentrow['Grade']
		);
		$homeroomstudents[] = $homeroomstudent;
	}
}

else {
	//add error later
}

$teacherappointmentsql = "SELECT AppointmentID, StudentID, Date, RoomID, isOverwritable, Priority FROM Appointment WHERE TeacherID = " .$teacherresult['UserID']. " AND Date >= '".$date."';";
//This query could get too big.... will require testing and probably modification
$teacherappointmentquery = mysqli_query($conn, $teacherappointmentsql) or die('Error on teacherappointmentquery');
//Array that contains all of the appointment data
if($teacherappointmentquery) {
	while($teacherappointmentrow = mysqli_fetch_assoc($teacherappointmentquery)) {
		$teacherappointment = array(
			'AppointmentID' => $teacherappointmentrow['AppointmentID'],
			'StudentID' => $teacherappointmentrow['StudentID'],
			'TeacherID' => $teacherresult['UserID'],
			'Date' => $teacherappointmentrow['Date'],
			'RoomID' => $teacherappointmentrow['RoomID'],
			'isoverwritable' => $teacherappointmentrow['isOverwritable'],
			'Priority' => $teacherappointmentrow['Priority']
		);
		$teacherappointments[] = $teacherappointment;
	}
}

$appointmentsql = "SELECT AppointmentID, StudentID, Date, RoomID, isOverwritable, Priority FROM Appointment WHERE Date >= '" .$date. "';";
//This query could get too big.... will require testing and probably modification
$appointmentquery = mysqli_query($conn, $appointmentsql) or die('Error on appointmentquery');
//Array that contains all of the appointment data
$studtoappt = [];
if($appointmentquery) {
	while($appointmentrow = mysqli_fetch_assoc($appointmentquery)) {
		$appointment = array(
			'AppointmentID' => $appointmentrow['AppointmentID'],
			'StudentID' => $appointmentrow['StudentID'],
			'Date' => $appointmentrow['Date'],
			'RoomID' => $appointmentrow['RoomID'],
			'isoverwritable' => $appointmentrow['isOverwritable'],
			'Priority' => $appointmentrow['Priority']
		);
		$appointments[] = $appointment;
		$studtoappt[$apointmentrow['StudentID']] = array(
			'AppointmentID' => $appointmentrow['AppointmentID'],
			'Date' => $appointmentrow['Date'],
			'RoomID' => $appointmentrow['RoomID'],
			'isOverwritable' => $appointmentrow['isOverwritable'],
			'Priority' => $appointmentrow['Priority']
		);
	}
}

else {
	//add error later
}
//Gets rooms
$roomsql = "SELECT * FROM Room";
$roomquery = mysqli_query($conn, $roomsql) or die('Error on roomquery');
if($roomquery) {
	while($roomrow = mysqli_fetch_assoc($roomquery)) {
		$room = array(
			'RoomID' => $roomrow['RoomID'],
			'RoomNumber' => $roomrow['RoomNumber'],
			'Capacity' => $roomrow['Capacity'],
			'TeacherID' => $roomrow['TeacherID']
		);
		$rooms[] = $room;
	}
}

else {
	//add error later
}

//Gets priority day
$prioritysql = "SELECT p.Day as Day FROM PriorityDays as p join Subject as s on p.SubjectID = s.SubjectID WHERE s.SubjectID = " .$teacherresult['SubjectID']. ";";
$priorityquery = mysqli_query($conn, $prioritysql) or die(mysqli_error($conn));
$priorityday = mysqli_fetch_assoc($priorityquery);
//get no tag days
$notagdatesql = "SELECT Date FROM NoTagDays;";
$notagdatequery = mysqli_query($conn, $notagdatesql) or die(mysqli_error($conn));
//Array that contains all of the no tag days
$notagdates = mysqli_fetch_assoc($notagdatequery);

$notagdaysql = "SELECT Day FROM NoTagDays;";
$notagdayquery = mysqli_query($conn, $notagdaysql) or die(mysqli_error($conn));
//Array that contains all of the no tag days
$notagdays = mysqli_fetch_assoc($notagdayquery);

//get appointment requests
$apptreqsql = "SELECT StudentID, Date, Status FROM ApptRequest WHERE TeacherID = " .$teacherresult['UserID']. ";";
$apptreqquery = mysqli_query($conn, $apptreqsql) or die('Error on apptreqquery');
//Array that contains all of the appointment request data
if($apptreqquery) {
	while($apptreqrow = mysqli_fetch_assoc($apptreqquery)) {
		$apptreq = array(
			'StudentID' => $appointmentrow['StudentID'],
			'Date' => $appointmentrow['Date'],
			'Status' => $appointmentrow['Status']
		);
		$apptreqs[] = $apptreq;
	}
}

else {
	//add error later
}
//get overwritten appointments
$apptowsql = "SELECT AppointmentOWID, StudentName, Date FROM ApptOverwritten WHERE TeacherID = " .$teacherresult['UserID']. " AND New <> 0";
$apptowquery = mysqli_query($conn, $apptowsql) or die('Error on apptowquery');
//Array that contains all of the overwritten appointments
if($apptowquery) {
	while($apptowrow = mysqli_fetch_assoc($apptowquery)) {
		$apptow = array(
			'AppointmentOWID' => $appointmentrow['AppointmentOWID'],
			'StudentName' => $appointmentrow['StudentName'],
			'Date' => $appointmentrow['Date']
		);
		$apptreqs[] = $apptreq;
	}
}

else {
	//add error later
}

if ($_POST) {
   // Execute code (such as database updates) here.
   
   $error = false;
   
   if(isset($_POST['currenttagsdate'])) {
	   $_SESSION['CurrentTagDate'] = $_POST['currenttagsdate'];
   }
   
	//echo "<script>alert('".$_POST['mondate']."');</script>";
	$notification = "";
	$priority = $priorityday['Day'];
	//get monday appointments
	$monapptsql = "SELECT u.UserID as UserID FROM User as u INNER JOIN Appointment as a ON u.UserID = a.StudentID WHERE Date = '".$_POST['mondate']."';";
	$monapptquery = mysqli_query($conn, $monapptsql) or die('Error on monapptsql');
	$monappts = [];
	while($monapptresult = mysqli_fetch_assoc($monapptquery)) {
		array_push($monappts, $monapptresult['UserID']);
	}
	//get monday priority appointments
	$monapptsql = "SELECT u.UserID as UserID FROM User as u INNER JOIN Appointment as a ON u.UserID = a.StudentID WHERE Date = '".$_POST['mondate']."' AND Priority like 'True';";
	$monapptquery = mysqli_query($conn, $monapptsql) or die('Error on monapptsql');
	$monapptsp = [];
	while($monapptresult = mysqli_fetch_assoc($monapptquery)) {
		array_push($monapptsp, $monapptresult['UserID']);
	}
	//get monday overwritable appointments
	$monapptsql = "SELECT u.UserID as UserID FROM User as u INNER JOIN Appointment as a ON u.UserID = a.StudentID WHERE Date = '".$_POST['mondate']."' AND Priority like 'False' AND isOverwritable like 'True';";
	$monapptquery = mysqli_query($conn, $monapptsql) or die('Error on monapptsql');
	$monapptso = [];
	while($monapptresult = mysqli_fetch_assoc($monapptquery)) {
		array_push($monapptso, $monapptresult['UserID']);
	}
	//get tuesday appointments
	$tueapptsql = "SELECT u.UserID as UserID FROM User as u INNER JOIN Appointment as a ON u.UserID = a.StudentID WHERE Date = '".$_POST['tuesdate']."';";
	$tueapptquery = mysqli_query($conn, $tueapptsql) or die('Error on tueapptsql');
	$tueappts = [];
	while($tueapptresult = mysqli_fetch_assoc($tueapptquery)) {
		array_push($tueappts, $tueapptresult['UserID']);
	}
	//get tuesday priority appointments
	$tueapptsql = "SELECT u.UserID as UserID FROM User as u INNER JOIN Appointment as a ON u.UserID = a.StudentID WHERE Date = '".$_POST['tuesdate']."' AND Priority like 'True';";
	$tueapptquery = mysqli_query($conn, $tueapptsql) or die('Error on tueapptsql');
	$tueapptsp = [];
	while($tueapptresult = mysqli_fetch_assoc($tueapptquery)) {
		array_push($tueapptsp, $tueapptresult['UserID']);
	}
	//get tuesday overwritable appointments
	$tueapptsql = "SELECT u.UserID as UserID FROM User as u INNER JOIN Appointment as a ON u.UserID = a.StudentID WHERE Date = '".$_POST['tuesdate']."' AND Priority like 'False' AND isOverwritable like 'True';";
	$tueapptquery = mysqli_query($conn, $tueapptsql) or die('Error on tueapptsql');
	$tueapptsp = [];
	while($tueapptresult = mysqli_fetch_assoc($tueapptquery)) {
		array_push($tueapptsp, $tueapptresult['UserID']);
	}
	//get wednesday appointments
	$wedapptsql = "SELECT u.UserID as UserID FROM User as u INNER JOIN Appointment as a ON u.UserID = a.StudentID WHERE Date = '".$_POST['wednesdate']."';";
	$wedapptquery = mysqli_query($conn, $wedapptsql) or die('Error on wedapptsql');
	$wedappts = [];
	while($wedapptresult = mysqli_fetch_assoc($wedapptquery)) {
		array_push($wedappts, $wedapptresult['UserID']);
	}
	//get wednesday priority appointments
	$wedapptsql = "SELECT u.UserID as UserID FROM User as u INNER JOIN Appointment as a ON u.UserID = a.StudentID WHERE Date = '".$_POST['wednesdate']."' AND Priority like 'True';";
	$wedapptquery = mysqli_query($conn, $wedapptsql) or die('Error on wedapptsql');
	$wedapptsp = [];
	while($wedapptresult = mysqli_fetch_assoc($wedapptquery)) {
		array_push($wedapptsp, $wedapptresult['UserID']);
	}
	//get wednesday overwritable appointments
	$wedapptsql = "SELECT u.UserID as UserID FROM User as u INNER JOIN Appointment as a ON u.UserID = a.StudentID WHERE Date = '".$_POST['wednesdate']."' AND Priority like 'False' AND isOverwritable like 'True';";
	$wedapptquery = mysqli_query($conn, $wedapptsql) or die('Error on wedapptsql');
	$wedapptso = [];
	while($wedapptresult = mysqli_fetch_assoc($wedapptquery)) {
		array_push($wedapptso, $wedapptresult['UserID']);
	}
	//get thursday appointments
	$thuapptsql = "SELECT u.UserID as UserID FROM User as u INNER JOIN Appointment as a ON u.UserID = a.StudentID WHERE Date = '".$_POST['thursdate']."';";
	$thuapptquery = mysqli_query($conn, $thuapptsql) or die('Error on thuapptsql');
	$thuappts = [];
	while($thuapptresult = mysqli_fetch_assoc($thuapptquery)) {
		array_push($thuappts, $thuapptresult['UserID']);
	}
	//get thursday priority appointments
	$thuapptsql = "SELECT u.UserID as UserID FROM User as u INNER JOIN Appointment as a ON u.UserID = a.StudentID WHERE Date = '".$_POST['thursdate']."' AND Priority like 'True';";
	$thuapptquery = mysqli_query($conn, $thuapptsql) or die('Error on thuapptsql');
	$thuapptsp = [];
	while($thuapptresult = mysqli_fetch_assoc($thuapptquery)) {
		array_push($thuapptsp, $thuapptresult['UserID']);
	}
	//get thursday overwritable appointments
	$thuapptsql = "SELECT u.UserID as UserID FROM User as u INNER JOIN Appointment as a ON u.UserID = a.StudentID WHERE Date = '".$_POST['thursdate']."' AND Priority like 'False' AND isOverwritable like 'True';";
	$thuapptquery = mysqli_query($conn, $thuapptsql) or die('Error on thuapptsql');
	$thuapptso = [];
	while($thuapptresult = mysqli_fetch_assoc($thuapptquery)) {
		array_push($thuapptso, $thuapptresult['UserID']);
	}
	//get friday appointments
	$friapptsql = "SELECT u.UserID as UserID FROM User as u INNER JOIN Appointment as a ON u.UserID = a.StudentID WHERE Date = '".$_POST['fridate']."';";
	$friapptquery = mysqli_query($conn, $friapptsql) or die('Error on friapptsql');
	$friappts = [];
	while($friapptresult = mysqli_fetch_assoc($friapptquery)) {
		array_push($friappts, $friapptresult['UserID']);
	}
	//get friday priority appointments
	$friapptsql = "SELECT u.UserID as UserID FROM User as u INNER JOIN Appointment as a ON u.UserID = a.StudentID WHERE Date = '".$_POST['fridate']."' AND Priority like 'True';";
	$friapptquery = mysqli_query($conn, $friapptsql) or die('Error on friapptsql');
	$friapptsp = [];
	while($friapptresult = mysqli_fetch_assoc($friapptquery)) {
		array_push($friapptsp, $friapptresult['UserID']);
	}
	//get friday overwritable appointments
	$friapptsql = "SELECT u.UserID as UserID FROM User as u INNER JOIN Appointment as a ON u.UserID = a.StudentID WHERE Date = '".$_POST['fridate']."' AND Priority like 'False' AND isOverwritable like 'True';";
	$friapptquery = mysqli_query($conn, $friapptsql) or die('Error on friapptsql');
	$friapptso = [];
	while($friapptresult = mysqli_fetch_assoc($friapptquery)) {
		array_push($friapptso, $friapptresult['UserID']);
	}
	
	$roomcap;
	$roomid;
	foreach($rooms as $roo) {
		if($roo['TeacherID'] == $teacherresult['UserID']) {
			$roomcap = $roo['Capacity'];
			$roomid = $roo['RoomID'];
			break;
		}
	}
	//logic for submitting attendance
	foreach($allstudents as $studs) {
		if(isset($_POST[$studs['StudentID']])) {
			if(isset($_POST[$studs['StudentID'].'-att'])) {
				$insertattendancesql = "INSERT INTO Attendance (AppointmentID, Attend) VALUES (".$_POST[$studs['StudentID'].'-att'].", '".$_POST[$studs['StudentID']]."');";
				mysqli_query($conn, $insertattendancesql) or die(mysqli_error($conn));
			}
			else {
				//put error here for hidden variable for attendance not being created properly
			}
		}
		//logic for submitting tags on monday
		if(isset($_POST[$studs['StudentID'].'-mon'])) {
			$monfound;
			$monpriorityfound;
			$monoverfound;
			if(isset($_POST[$studs['StudentID'].'-monday'])) { //this is the hidden variable that contains the date.  don't judge me.
				$monfound = in_array($studs['StudentID'], $monappts);
				$monpriorityfound = in_array($studs['StudentID'], $monapptsp);
				$monoverfound = in_array($studs['StudentID'], $monapptso);
				//echo "<script>alert('here');</script>";
				
				if($monfound && !$monpriorityfound && $priority == "Monday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-monday']) {
							$roomcount+=1;
						}
					}
					if(in_array("Monday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Mondays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-monday'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-monday']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-monday']."', ".$roomid.", 'False', 'True');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif($monfound && !$monpriorityfound && $priority != "Monday" && $monoverfound) {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-monday']) {
							$roomcount+=1;
						}
					}
					if(in_array("Monday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Mondays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-monday'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-monday']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-monday']."', ".$roomid.", 'False', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$monfound && !$monpriorityfound && $priority == "Monday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-monday']) {
							$roomcount+=1;
						}
					}
					if(in_array("Monday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Mondays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-monday'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-monday']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-monday']."', ".$roomid.", 'False', 'True');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$monfound && !$monpriorityfound && $priority != "Monday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-monday']) {
							$roomcount+=1;
						}
					}
					if(in_array("Monday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Mondays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-monday'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-monday']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-monday']."', ".$roomid.", 'False', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
				}
			}
			else {
				//put error here for hidden variable for monday not being created properly
			
			}
		}
		
		
		//logic for submitting tags on tuesday
		if(isset($_POST[$studs['StudentID'].'-tue'])) {
			$tuefound;
			$tuepriorityfound;
			$tueoverfound;
			if(isset($_POST[$studs['StudentID'].'-tuesday'])) { //this is the hidden variable that contains the date.  don't judge me.
				$tuefound = in_array($studs['StudentID'], $tueappts);
				$tuepriorityfound = in_array($studs['StudentID'], $tueapptsp);
				$tueoverfound = in_array($studs['StudentID'], $tueapptso);
				
				if($tuefound && !$tuepriorityfound && $priority == "Tuesday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-tuesday']) {
							$roomcount+=1;
						}
					}
					if(in_array("Tuesday", $notagdays)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on Tuesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-tuesday'], $notagdates)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-tuesday']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-tuesday']."', ".$roomid.", 'False', 'True');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
				}
				elseif($tuefound && !$tuepriorityfound && $priority != "Tuesday" && $tueoverfound) {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-tuesday']) {
							$roomcount+=1;
						}
					}
					if(in_array("Tuesday", $notagdays)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on Tuesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-tuesday'], $notagdates)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-tuesday']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-tuesday']."', ".$roomid.", 'False', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
				}
				elseif(!$tuefound && !$tuepriorityfound && $priority == "Tuesday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-tuesday']) {
							$roomcount+=1;
						}
					}
					if(in_array("Tuesday", $notagdays)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on Tuesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-tuesday'], $notagdates)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-tuesday']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-tuesday']."', ".$roomid.", 'False', 'True');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else {
						echo "error";
					}
				}
				elseif(!$tuefound && !$tuepriorityfound && $priority != "Tuesday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-tuesday']) {
							$roomcount+=1;
						}
					}
					if(in_array("Tuesday", $notagdays)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on Tuesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-tuesday'], $notagdates)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-tuesday']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-tuesday']."', ".$roomid.", 'False', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
				}
			}
			else {
				//put error here for hidden variable for monday not being created properly
				echo "outer error";
			}
		}
		
		
		
		
		//logic for submitting tags on wednesday
		if(isset($_POST[$studs['StudentID'].'-wed'])) {
			$wedfound;
			$wedpriorityfound;
			$wedoverfound;
			if(isset($_POST[$studs['StudentID'].'-wednesday'])) { //this is the hidden variable that contains the date.  don't judge me.
				$wedfound = in_array($studs['StudentID'], $wedappts);
				$wedpriorityfound = in_array($studs['StudentID'], $wedapptsp);
				$wedoverfound = in_array($studs['StudentID'], $wedapptso);
				
				if($wedfound && !$wedpriorityfound && $priority == "Wednesday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-wednesday']) {
							$roomcount+=1;
						}
					}
					if(in_array("Wednesday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Wednesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-wednesday'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-wednesday']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-wednesday']."', ".$roomid.", 'False', 'True');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif($wedfound && !$wedpriorityfound && $priority != "Wednesday" && $wedoverfound) {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-wednesday']) {
							$roomcount+=1;
						}
					}
					if(in_array("Wednesday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Wednesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-wednesday'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-wednesday']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-wednesday']."', ".$roomid.", 'False', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$wedfound && !$wedpriorityfound && $priority == "Wednesday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-wednesday']) {
							$roomcount+=1;
						}
					}
					if(in_array("Wednesday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Wednesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-wednesday'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-wednesday']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-wednesday']."', ".$roomid.", 'False', 'True');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$wedfound && !$wedpriorityfound && $priority != "Wednesday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-wednesday']) {
							$roomcount+=1;
						}
					}
					if(in_array("Wednesday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Wednesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-wednesday'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-wednesday']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full ".$roomcount."');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-wednesday']."', ".$roomid.", 'False', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
				}
			}
			else {
				//put error here for hidden variable for wednesday not being created properly
			
			}
		}
		
		
		
		
		//logic for submitting tags on thursday
		if(isset($_POST[$studs['StudentID'].'-thu'])) {
			$thufound;
			$thupriorityfound;
			$thuoverfound;
			if(isset($_POST[$studs['StudentID'].'-thursday'])) { //this is the hidden variable that contains the date.  don't judge me.
				$thufound = in_array($studs['StudentID'], $thuappts);
				$thupriorityfound = in_array($studs['StudentID'], $thuapptsp);
				$thuoverfound = in_array($studs['StudentID'], $thuapptso);
				
				if($thufound && !$thupriorityfound && $priority == "Thursday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-thursday']) {
							$roomcount+=1;
						}
					}
					if(in_array("Thursday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Thursdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-thursday'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-thursday']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-thursday']."', ".$roomid.", 'False', 'True');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif($thufound && !$thupriorityfound && $priority != "Thursday" && $thuoverfound) {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-thursday']) {
							$roomcount+=1;
						}
					}
					if(in_array("Thursday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Thursdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-thursday'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-thursday']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-thursday']."', ".$roomid.", 'False', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$thufound && !$thupriorityfound && $priority == "Thursday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-thursday']) {
							$roomcount+=1;
						}
					}
					if(in_array("Thursday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Thursdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-thursday'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-thursday']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-thursday']."', ".$roomid.", 'False', 'True');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$thufound && !$thupriorityfound && $priority != "Thursday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-thursday']) {
							$roomcount+=1;
						}
					}
					if(in_array("Thursday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Thursdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-thursday'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-thursday']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-thursday']."', ".$roomid.", 'False', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
				}
			}
			else {
				//put error here for hidden variable for thursday not being created properly
			
			}
		}
		
		
		
		
		//logic for submitting tags on friday
		if(isset($_POST[$studs['StudentID'].'-fri'])) {
			$frifound;
			$fripriorityfound;
			$frioverfound;
			if(isset($_POST[$studs['StudentID'].'-friday'])) { //this is the hidden variable that contains the date.  don't judge me.
				$frifound = in_array($studs['StudentID'], $friappts);
				$fripriorityfound = in_array($studs['StudentID'], $friapptsp);
				$frioverfound = in_array($studs['StudentID'], $friapptso);
				
				if($frifound && !$fripriorityfound && $priority == "Friday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-friday']) {
							$roomcount+=1;
						}
					}
					if(in_array("Friday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Fridays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-friday'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-friday']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-friday']."', ".$roomid.", 'False', 'True');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				if($frifound && !$fripriorityfound && $priority != "Friday" && $frioverfound) {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-friday']) {
							$roomcount+=1;
						}
					}
					if(in_array("Friday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Fridays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-friday'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-friday']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-friday']."', ".$roomid.", 'False', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$frifound && !$fripriorityfound && $priority == "Friday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-friday']) {
							$roomcount+=1;
						}
					}
					if(in_array("Friday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Fridays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-friday'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-friday']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-friday']."', ".$roomid.", 'False', 'True');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$frifound && !$fripriorityfound && $priority != "Friday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-friday']) {
							$roomcount+=1;
						}
					}
					if(in_array("Friday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Fridays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-friday'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-friday']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-friday']."', ".$roomid.", 'False', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
				}
			}
			else {
				//put error here for hidden variable for friday not being created properly
			
			}
		}
		
		
		
		
		//logic for submitting all-student tags on monday
		if(isset($_POST[$studs['StudentID'].'-mon-all'])) {
			$monfound;
			$monpriorityfound;
			$monoverfound;
			if(isset($_POST[$studs['StudentID'].'-monday-all'])) { //this is the hidden variable that contains the date.  don't judge me.
				$monfound = in_array($studs['StudentID'], $monappts, true);
				$monpriorityfound = in_array($studs['StudentID'], $monapptsp);
				$monoverfound = in_array($studs['StudentID'], $monapptso);
				
				if($monfound && !$monpriorityfound && $priority == "Monday") {
					echo "<script>alert('1');</script";
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-monday-all']) {
							$roomcount+=1;
						}
					}
					if(in_array("Monday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Mondays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-monday-all'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-monday-all']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday-all']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-monday-all']."', ".$roomid.", 'False', 'True');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif($monfound && !$monpriorityfound && $priority != "Monday" && $monoverfound) {
					echo "<script>alert('2');</script";
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-monday-all']) {
							$roomcount+=1;
						}
					}
					if(in_array("Monday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Mondays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-monday-all'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-monday-all']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday-all']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-monday-all']."', ".$roomid.", 'False', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$monfound && !$monpriorityfound && $priority == "Monday") {
					echo "<script>alert('3');</script";
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-monday-all']) {
							$roomcount+=1;
						}
					}
					if(in_array("Monday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Mondays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-monday-all'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-monday-all']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-monday-all']."', ".$roomid.", 'False', 'True');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$monfound && !$monpriorityfound && $priority != "Monday") {
					echo "<script>alert('4');</script";
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-monday-all']) {
							$roomcount+=1;
						}
					}
					if(in_array("Monday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Mondays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-monday-all'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-monday-all']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-monday-all']."', ".$roomid.", 'False', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
				}
			}
			else {
				//put error here for hidden variable for monday not being created properly
			
			}
		}
		
		
		//logic for submitting all-student tags on tuesday
		if(isset($_POST[$studs['StudentID'].'-tue-all'])) {
			$tuefound;
			$tuepriorityfound;
			$tueoverfound;
			if(isset($_POST[$studs['StudentID'].'-tuesday-all'])) { //this is the hidden variable that contains the date.  don't judge me.
				$tuefound = in_array($studs['StudentID'], $tueappts);
				$tuepriorityfound = in_array($studs['StudentID'], $tueapptsp);
				$tueoverfound = in_array($studs['StudentID'], $tueapptso);
				
				if($tuefound && !$tuepriorityfound && $priority == "Tuesday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-tuesday-all']) {
							$roomcount+=1;
						}
					}
					if(in_array("Tuesday", $notagdays)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on Tuesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-tuesday-all'], $notagdates)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-tuesday-all']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday-all']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-tuesday-all']."', ".$roomid.", 'False', 'True');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
				}
				elseif($tuefound && !$tuepriorityfound && $priority != "Tuesday" && $tueoverfound) {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-tuesday-all']) {
							$roomcount+=1;
						}
					}
					if(in_array("Tuesday", $notagdays)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on Tuesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-tuesday-all'], $notagdates)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-tuesday-all']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday-all']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-tuesday-all']."', ".$roomid.", 'False', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
				}
				elseif(!$tuefound && !$tuepriorityfound && $priority == "Tuesday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-tuesday-all']) {
							$roomcount+=1;
						}
					}
					if(in_array("Tuesday", $notagdays)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on Tuesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-tuesday-all'], $notagdates)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-tuesday-all']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-tuesday-all']."', ".$roomid.", 'False', 'True');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else {
						echo "error";
					}
				}
				elseif(!$tuefound && !$tuepriorityfound && $priority != "Tuesday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-tuesday-all']) {
							$roomcount+=1;
						}
					}
					if(in_array("Tuesday", $notagdays)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on Tuesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-tuesday-all'], $notagdates)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-tuesday-all']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-tuesday-all']."', ".$roomid.", 'False', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
				}
			}
			else {
				//put error here for hidden variable for monday not being created properly
				echo "outer error";
			}
		}
		
		
		
		
		//logic for submitting all-student tags on wednesday
		if(isset($_POST[$studs['StudentID'].'-wed-all'])) {
			$wedfound;
			$wedpriorityfound;
			$wedoverfound;
			if(isset($_POST[$studs['StudentID'].'-wednesday-all'])) { //this is the hidden variable that contains the date.  don't judge me.
				$wedfound = in_array($studs['StudentID'], $wedappts);
				$wedpriorityfound = in_array($studs['StudentID'], $wedapptsp);
				$wedoverfound = in_array($studs['StudentID'], $wedapptso);
				
				if($wedfound && !$wedpriorityfound && $priority == "Wednesday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-wednesday-all']) {
							$roomcount+=1;
						}
					}
					if(in_array("Wednesday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Wednesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-wednesday-all'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-wednesday-all']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday-all']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-wednesday-all']."', ".$roomid.", 'False', 'True');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif($wedfound && !$wedpriorityfound && $priority != "Wednesday" && $wedoverfound) {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-wednesday-all']) {
							$roomcount+=1;
						}
					}
					if(in_array("Wednesday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Wednesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-wednesday-all'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-wednesday-all']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday-all']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-wednesday-all']."', ".$roomid.", 'False', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$wedfound && !$wedpriorityfound && $priority == "Wednesday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-wednesday-all']) {
							$roomcount+=1;
						}
					}
					if(in_array("Wednesday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Wednesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-wednesday-all'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-wednesday-all']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-wednesday-all']."', ".$roomid.", 'False', 'True');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$wedfound && !$wedpriorityfound && $priority != "Wednesday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-wednesday-all']) {
							$roomcount+=1;
						}
					}
					if(in_array("Wednesday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Wednesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-wednesday-all'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-wednesday-all']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full ".$roomcount."');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-wednesday-all']."', ".$roomid.", 'False', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
				}
			}
			else {
				//put error here for hidden variable for wednesday not being created properly
			
			}
		}
		
		
		
		
		//logic for submitting all-student tags on thursday
		if(isset($_POST[$studs['StudentID'].'-thu-all'])) {
			$thufound;
			$thupriorityfound;
			$thuoverfound;
			if(isset($_POST[$studs['StudentID'].'-thursday-all'])) { //this is the hidden variable that contains the date.  don't judge me.
				$thufound = in_array($studs['StudentID'], $thuappts);
				$thupriorityfound = in_array($studs['StudentID'], $thuapptsp);
				$thuoverfound = in_array($studs['StudentID'], $thuapptso);
				
				if($thufound && !$thupriorityfound && $priority == "Thursday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-thursday-all']) {
							$roomcount+=1;
						}
					}
					if(in_array("Thursday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Thursdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-thursday-all'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-thursday-all']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday-all']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-thursday-all']."', ".$roomid.", 'False', 'True');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif($thufound && !$thupriorityfound && $priority != "Thursday" && $thuoverfound) {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-thursday-all']) {
							$roomcount+=1;
						}
					}
					if(in_array("Thursday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Thursdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-thursday-all'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-thursday-all']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday-all']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-thursday-all']."', ".$roomid.", 'False', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$thufound && !$thupriorityfound && $priority == "Thursday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-thursday-all']) {
							$roomcount+=1;
						}
					}
					if(in_array("Thursday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Thursdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-thursday-all'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-thursday-all']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-thursday-all']."', ".$roomid.", 'False', 'True');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$thufound && !$thupriorityfound && $priority != "Thursday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-thursday-all']) {
							$roomcount+=1;
						}
					}
					if(in_array("Thursday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Thursdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-thursday-all'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-thursday-all']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-thursday-all']."', ".$roomid.", 'False', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
				}
			}
			else {
				//put error here for hidden variable for thursday not being created properly
			
			}
		}
		
		
		
		
		//logic for submitting all-student tags on friday
		if(isset($_POST[$studs['StudentID'].'-fri-all'])) {
			$frifound;
			$fripriorityfound;
			$frioverfound;
			if(isset($_POST[$studs['StudentID'].'-friday-all'])) { //this is the hidden variable that contains the date.  don't judge me.
				$frifound = in_array($studs['StudentID'], $friappts);
				$fripriorityfound = in_array($studs['StudentID'], $friapptsp);
				$frioverfound = in_array($studs['StudentID'], $friapptso);
				
				if($frifound && !$fripriorityfound && $priority == "Friday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-friday-all']) {
							$roomcount+=1;
						}
					}
					if(in_array("Friday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Fridays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-friday-all'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-friday-all']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday-all']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-friday-all']."', ".$roomid.", 'False', 'True');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				if($frifound && !$fripriorityfound && $priority != "Friday" && $frioverfound) {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-friday-all']) {
							$roomcount+=1;
						}
					}
					if(in_array("Friday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Fridays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-friday-all'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-friday-all']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday-all']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-friday-all']."', ".$roomid.", 'False', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$frifound && !$fripriorityfound && $priority == "Friday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-friday-all']) {
							$roomcount+=1;
						}
					}
					if(in_array("Friday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Fridays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-friday-all'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-friday-all']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-friday-all']."', ".$roomid.", 'False', 'True');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$frifound && !$fripriorityfound && $priority != "Friday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-friday-all']) {
							$roomcount+=1;
						}
					}
					if(in_array("Friday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Fridays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-friday-all'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-friday-all']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-friday-all']."', ".$roomid.", 'False', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
				}
			}
			else {
				//put error here for hidden variable for friday not being created properly
			
			}
		}
		
		
		
		
		//logic for submitting extra-curricular tags on monday
		if(isset($_POST[$studs['StudentID'].'-mon-ec'])) {
			$monfound;
			$monpriorityfound;
			$monoverfound;
			if(isset($_POST[$studs['StudentID'].'-monday-ec'])) { //this is the hidden variable that contains the date.  don't judge me.
				$monfound = in_array($studs['StudentID'], $monappts);
				$monpriorityfound = in_array($studs['StudentID'], $monapptsp);
				$monoverfound = in_array($studs['StudentID'], $monapptso);
				//echo "<script>alert('here');</script>";
				
				if($monfound && !$monpriorityfound && $priority == "Monday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-monday-ec']) {
							$roomcount+=1;
						}
					}
					if(in_array("Monday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Mondays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-monday-ec'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-monday-ec']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday-ec']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-monday-ec']."', ".$roomid.", 'True', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif($monfound && !$monpriorityfound && $priority != "Monday" && $monoverfound) {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-monday-ec']) {
							$roomcount+=1;
						}
					}
					if(in_array("Monday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Mondays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-monday-ec'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-monday-ec']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday-ec']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-monday-ec']."', ".$roomid.", 'True', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$monfound && !$monpriorityfound && $priority == "Monday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-monday-ec']) {
							$roomcount+=1;
						}
					}
					if(in_array("Monday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Mondays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-monday-ec'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-monday-ec']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-monday-ec']."', ".$roomid.", 'True', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$monfound && !$monpriorityfound && $priority != "Monday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-monday-ec']) {
							$roomcount+=1;
						}
					}
					if(in_array("Monday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Mondays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-monday-ec'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-monday-ec']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-monday-ec']."', ".$roomid.", 'True', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
				}
			}
			else {
				//put error here for hidden variable for monday not being created properly
			
			}
		}
		
		
		//logic for submitting extra-curricular tags on tuesday
		if(isset($_POST[$studs['StudentID'].'-tue-ec'])) {
			$tuefound;
			$tuepriorityfound;
			$tueoverfound;
			if(isset($_POST[$studs['StudentID'].'-tuesday-ec'])) { //this is the hidden variable that contains the date.  don't judge me.
				$tuefound = in_array($studs['StudentID'], $tueappts);
				$tuepriorityfound = in_array($studs['StudentID'], $tueapptsp);
				$tueoverfound = in_array($studs['StudentID'], $tueapptso);
				
				if($tuefound && !$tuepriorityfound && $priority == "Tuesday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-tuesday-ec']) {
							$roomcount+=1;
						}
					}
					if(in_array("Tuesday", $notagdays)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on Tuesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-tuesday-ec'], $notagdates)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-tuesday-ec']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday-ec']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-tuesday-ec']."', ".$roomid.", 'True', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
				}
				elseif($tuefound && !$tuepriorityfound && $priority != "Tuesday" && $tueoverfound) {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-tuesday-ec']) {
							$roomcount+=1;
						}
					}
					if(in_array("Tuesday", $notagdays)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on Tuesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-tuesday-ec'], $notagdates)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-tuesday-ec']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday-ec']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-tuesday-ec']."', ".$roomid.", 'True', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
				}
				elseif(!$tuefound && !$tuepriorityfound && $priority == "Tuesday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-tuesday-ec']) {
							$roomcount+=1;
						}
					}
					if(in_array("Tuesday", $notagdays)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on Tuesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-tuesday-ec'], $notagdates)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-tuesday-ec']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-tuesday-ec']."', ".$roomid.", 'True', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else {
						echo "error";
					}
				}
				elseif(!$tuefound && !$tuepriorityfound && $priority != "Tuesday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-tuesday-ec']) {
							$roomcount+=1;
						}
					}
					if(in_array("Tuesday", $notagdays)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on Tuesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-tuesday-ec'], $notagdates)) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-tuesday-ec']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						$error = true;
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-tuesday-ec']."', ".$roomid.", 'True', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
				}
			}
			else {
				//put error here for hidden variable for monday not being created properly
				echo "outer error";
			}
		}
		
		
		
		
		//logic for submitting extra-curricular tags on wednesday
		if(isset($_POST[$studs['StudentID'].'-wed-ec'])) {
			$wedfound;
			$wedpriorityfound;
			$wedoverfound;
			if(isset($_POST[$studs['StudentID'].'-wednesday-ec'])) { //this is the hidden variable that contains the date.  don't judge me.
				$wedfound = in_array($studs['StudentID'], $wedappts);
				$wedpriorityfound = in_array($studs['StudentID'], $wedapptsp);
				$wedoverfound = in_array($studs['StudentID'], $wedapptso);
				
				if($wedfound && !$wedpriorityfound && $priority == "Wednesday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-wednesday-ec']) {
							$roomcount+=1;
						}
					}
					if(in_array("Wednesday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Wednesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-wednesday-ec'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-wednesday-ec']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday-ec']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-wednesday-ec']."', ".$roomid.", 'True', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif($wedfound && !$wedpriorityfound && $priority != "Wednesday" && $wedoverfound) {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-wednesday-ec']) {
							$roomcount+=1;
						}
					}
					if(in_array("Wednesday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Wednesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-wednesday-ec'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-wednesday-ec']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday-ec']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-wednesday-ec']."', ".$roomid.", 'True', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$wedfound && !$wedpriorityfound && $priority == "Wednesday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-wednesday-ec']) {
							$roomcount+=1;
						}
					}
					if(in_array("Wednesday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Wednesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-wednesday-ec'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-wednesday-ec']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-wednesday-ec']."', ".$roomid.", 'True', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$wedfound && !$wedpriorityfound && $priority != "Wednesday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-wednesday-ec']) {
							$roomcount+=1;
						}
					}
					if(in_array("Wednesday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Wednesdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-wednesday-ec'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-wednesday-ec']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full ".$roomcount."');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-wednesday-ec']."', ".$roomid.", 'True', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
				}
			}
			else {
				//put error here for hidden variable for wednesday not being created properly
			
			}
		}
		
		
		
		
		//logic for submitting extra-curricular tags on thursday
		if(isset($_POST[$studs['StudentID'].'-thu-ec'])) {
			$thufound;
			$thupriorityfound;
			$thuoverfound;
			if(isset($_POST[$studs['StudentID'].'-thursday-ec'])) { //this is the hidden variable that contains the date.  don't judge me.
				$thufound = in_array($studs['StudentID'], $thuappts);
				$thupriorityfound = in_array($studs['StudentID'], $thuapptsp);
				$thuoverfound = in_array($studs['StudentID'], $thuapptso);
				
				if($thufound && !$thupriorityfound && $priority == "Thursday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-thursday-ec']) {
							$roomcount+=1;
						}
					}
					if(in_array("Thursday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Thursdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-thursday-ec'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-thursday-ec']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday-ec']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-thursday-ec']."', ".$roomid.", 'True', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif($thufound && !$thupriorityfound && $priority != "Thursday" && $thuoverfound) {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-thursday-ec']) {
							$roomcount+=1;
						}
					}
					if(in_array("Thursday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Thursdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-thursday-ec'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-thursday-ec']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday-ec']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-thursday-ec']."', ".$roomid.", 'True', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$thufound && !$thupriorityfound && $priority == "Thursday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-thursday-ec']) {
							$roomcount+=1;
						}
					}
					if(in_array("Thursday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Thursdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-thursday-ec'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-thursday-ec']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-thursday-ec']."', ".$roomid.", 'True', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$thufound && !$thupriorityfound && $priority != "Thursday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-thursday-ec']) {
							$roomcount+=1;
						}
					}
					if(in_array("Thursday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Thursdays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-thursday-ec'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-thursday-ec']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-thursday-ec']."', ".$roomid.", 'True', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
				}
			}
			else {
				//put error here for hidden variable for thursday not being created properly
			
			}
		}
		
		
		
		
		//logic for submitting extra-curricular tags on friday
		if(isset($_POST[$studs['StudentID'].'-fri-ec'])) {
			$frifound;
			$fripriorityfound;
			$frioverfound;
			if(isset($_POST[$studs['StudentID'].'-friday-ec'])) { //this is the hidden variable that contains the date.  don't judge me.
				$frifound = in_array($studs['StudentID'], $friappts);
				$fripriorityfound = in_array($studs['StudentID'], $friapptsp);
				$frioverfound = in_array($studs['StudentID'], $friapptso);
				
				if($frifound && !$fripriorityfound && $priority == "Friday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-friday-ec']) {
							$roomcount+=1;
						}
					}
					if(in_array("Friday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Fridays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-friday-ec'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-friday-ec']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday-ec']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-friday-ec']."', ".$roomid.", 'True', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				if($frifound && !$fripriorityfound && $priority != "Friday" && $frioverfound) {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-friday-ec']) {
							$roomcount+=1;
						}
					}
					if(in_array("Friday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Fridays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-friday-ec'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-friday-ec']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$deleteappointmentsql = "DELETE FROM Appointment WHERE StudentID = ".$studs['StudentID']." AND Date = '".$_POST[$studs['StudentID'].'-monday-ec']."';";
						mysqli_query($conn, $deleteappointmentsql) or die(mysqli_error($conn));
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-friday-ec']."', ".$roomid.", 'True', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$frifound && !$fripriorityfound && $priority == "Friday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-friday-ec']) {
							$roomcount+=1;
						}
					}
					if(in_array("Friday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Fridays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-friday-ec'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-friday-ec']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-friday-ec']."', ".$roomid.", 'True', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
					else{
						echo "<script type='text/javascript'>alert('No tagging on one of those days');</script>";
					}
				}
				elseif(!$frifound && !$fripriorityfound && $priority != "Friday") {
					$roomcount=0;
					foreach($teacherappointments as $appt) {
						if($appt['Date'] == $_POST[$studs['StudentID'].'-friday-ec']) {
							$roomcount+=1;
						}
					}
					if(in_array("Friday", $notagdays)) {
						echo "<script type='text/javascript'>alert('Can not tag on Fridays');</script>";
					}
					elseif(in_array($_POST[$studs['StudentID'].'-friday-ec'], $notagdates)) {
						echo "<script type='text/javascript'>alert('Can not tag on ".$_POST[$studs['StudentID'].'-friday-ec']."');</script>";
					}
					elseif($roomcount>=$roomcap && !$notag) {
						echo "<script type='text/javascript'>alert('Can not tag on because room is full');</script>";
					}
					elseif($roomcount<$roomcap) {
						$insertappointmentsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$studs['StudentID'].", ".$teacherresult['UserID'].", '".$_POST[$studs['StudentID'].'-friday-ec']."', ".$roomid.", 'True', 'False');";
						mysqli_query($conn, $insertappointmentsql) or die(mysqli_error($conn));
					}
				}
			}
			else {
				//put error here for hidden variable for friday not being created properly
			
			}
		}
	}
   // Redirect to this page.
   if(!$error) {
	   header("Location: " . $_SERVER['REQUEST_URI']);
	   exit();
   }
}

	
?>

<!DOCTYPE html>
	<html lang="en">

	<head>

		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="author" content="">
		<link rel="canonical" href="https://getbootstrap.com/docs/3.4/examples/navbar-fixed-top/">

		<title>XLT - CHS</title>

		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.js"></script>
		<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.js"></script>

		<!-- Latest compiled and minified JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
		
		<!-- Scripts necessary for the weekly date picker -->
		<script src="https://cdn.jsdelivr.net/momentjs/2.10.6/moment.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
		
		<!-- Custom styles for this template -->
		<link href="navbar-fixed-top.css" rel="stylesheet">
		
		<script>
		var weekpicker, start_date, end_date, mon, tues, wednes, thurs, fri, monday, tuesday, wednesday, thursday, friday;
			function set_week_picker(date) {
                start_date = new Date(date.getFullYear(), date.getMonth(), date.getDate() - date.getDay());
                end_date = new Date(date.getFullYear(), date.getMonth(), date.getDate() - date.getDay() + 6);
                mon = new Date(date.getFullYear(), date.getMonth(), date.getDate() - date.getDay() + 1);
                tues = new Date(date.getFullYear(), date.getMonth(), date.getDate() - date.getDay() + 2);
                wednes = new Date(date.getFullYear(), date.getMonth(), date.getDate() - date.getDay() + 3);
                thurs = new Date(date.getFullYear(), date.getMonth(), date.getDate() - date.getDay() + 4);
                fri = new Date(date.getFullYear(), date.getMonth(), date.getDate() - date.getDay() + 5);
                weekpicker.datepicker('update', start_date);
                weekpicker.val((start_date.getMonth() + 1) + '/' + start_date.getDate() + '/' + start_date.getFullYear() + ' - ' + (end_date.getMonth() + 1) + '/' + end_date.getDate() + '/' + end_date.getFullYear());
                monday = moment(mon).format("YYYY-MM-DD");
                tuesday = moment(tues).format("YYYY-MM-DD");
                wednesday = moment(wednes).format("YYYY-MM-DD");
                thursday = moment(thurs).format("YYYY-MM-DD");
				friday = moment(fri).format("YYYY-MM-DD");
                document.getElementById("Monday").setAttribute("data-date", monday);
                document.getElementById("Tuesday").setAttribute("data-date", tuesday);
                document.getElementById("Wednesday").setAttribute("data-date", wednesday);
                document.getElementById("Thursday").setAttribute("data-date", thursday);
                document.getElementById("Friday").setAttribute("data-date", friday);
				var mondays = document.getElementsByClassName("monday");
				Array.from(mondays).forEach((monn) => {
					monn.value = monday;
				});
				var tuesdays = document.getElementsByClassName("tuesday");
				Array.from(tuesdays).forEach((tuess) => {
					tuess.value = tuesday;
				});
				var wednesdays = document.getElementsByClassName("wednesday");
				Array.from(wednesdays).forEach((wedd) => {
					wedd.value = wednesday;
				});
				var thursdays = document.getElementsByClassName("thursday");
				Array.from(thursdays).forEach((thurr) => {
					thurr.value = thursday;
				});
				var fridays = document.getElementsByClassName("friday");
				Array.from(fridays).forEach((frii) => {
					frii.value = friday;
				});
		}
            $(document).ready(function() {
                weekpicker = $('.week-picker');
                console.log(weekpicker);
                weekpicker.datepicker({
                    autoclose: true,
                    forceParse: false,
                    container: '#week-picker-wrapper',
                }).on("changeDate", function(e) {
                    set_week_picker(e.date);
                });
                set_week_picker(new Date);
				//$('Monday').data('data-date', monday);
            });
			
			/*$(document).ready(function () {
				$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
					$.fn.dataTable.tables( {visible: true, api: true} ).columns.adjust();
				});
			});*/
			
			/*$(document).ready(function () {
				$('table.table-striped').DataTable( {src: "", paging: false});
				$('.dataTables_length').addClass('bs-select');
			});*/
			
			$(document).ready(function(){
				$("#mystudentinput").on("keyup", function() {
					var value = $(this).val().toLowerCase();
					$("#mystudentstablebody tr").filter(function() {
						$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
					});
				});
			});
			$(document).ready(function(){
				$("#allstudentinput").on("keyup", function() {
					var value = $(this).val().toLowerCase();
					$("#allstudentstablebody tr").filter(function() {
						$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
					});
				});
			});
			$(document).ready(function(){
				$("#ecstudentinput").on("keyup", function() {
					var value = $(this).val().toLowerCase();
					$("#ecstudentstablebody tr").filter(function() {
						$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
					});
				});
			});
			
			function submit_form() {
				document.currenttagsform.submit();
			}
		</script>
		<style type="text/css">
			.datepicker table tr td span.active{
				background: #04c!important;
				border-color: #04c!important;
			}
			.datepicker .datepicker-days tr td.active {
				background: #04c!important;
			}
			#week-picker-wrapper .datepicker .datepicker-days tr td.active~td, #week-picker-wrapper .datepicker .datepicker-days tr td.active {
				color: #fff;
				background-color: #04c;
				border-radius: 0;
			}

			#week-picker-wrapper .datepicker .datepicker-days tr:hover td, #week-picker-wrapper .datepicker table tr td.day:hover, #week-picker-wrapper .datepicker table tr td.focused {
				color: #000!important;
				background: #e5e2e3!important;
				border-radius: 0!important;
			}
			
			body {background-color: #6d272f;}
		</style>
	</head>
	<body style="padding-top:100px">
		<!-- Navigation -->
		<nav class="navbar navbar-default navbar-fixed-top" style="min-height:80px">
			<div class="container">
				<div class="navbar-header">
					<a class="navbar-brand" href="#"> <img src="reds.png" height="300%"> </a>
				</div>
				<div class="navbar-collapse collapse">
					<ul class="nav navbar-nav navbar-right">
						<li>
							<a href="logout.php">
								<button type="button" class="btn btn-default btn-sm" style="vertical-aligh:middle">
									<span class="glyphicon glyphicon-log-out"></span> Log Out
								</button>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</nav>

		<!-- Page Content -->
		<div class="container">
		
			<div class="jumbotron">
		  
				<ul class="nav nav-tabs">
					<li class="active"><a data-toggle="tab" href="#home">Current Tags</a></li>
					<li><a data-toggle="tab" href="#homeroom">Homeroom</a></li>
					<li><a data-toggle="tab" href="#attendance">Attendance</a></li>
					<li><a data-toggle="tab" href="#tagging-ms">Tagging - My Students</a></li>
					<li><a data-toggle="tab" href="#tagging-as">Tagging - All Students</a></li>
					<li><a data-toggle="tab" href="#tagging-ec">Tagging - Extra Curricular</a></li>
					<li><a data-toggle="tab" href="#requests">Requests</a></li>
				</ul>

				<div class="tab-content">
					<div id="home" class="tab-pane fade in active">
						<h3>Current Tags</h3>
						<form method="post" action="" name="currenttagsform">
							<input type="date" name="currenttagsdate" id="currenttagsdate" value="<?php echo $_SESSION['CurrentTagDate'];?>" onchange="submit_form()">
							<table class="table table-hover">
								<thead>
									<tr>
										<th scope="col">Row</th>
										<th scope="col">First Name</th>
										<th scope="col">Last Name</th>
									</tr>
								</thead>
								<tbody>
								<?php
									$currenttagsrow = 1;
									foreach($allstudents as $attstud) {
										foreach($teacherappointments as $appt) {
											if($appt['StudentID']==$attstud['StudentID'] && $appt['TeacherID']==$teacherresult['UserID'] && $appt['Date']==$_SESSION['CurrentTagDate']) {
												echo '<tr>';
												echo '	<th scope="row">'.$currenttagsrow.'</th>';
												echo '	<td>'.$attstud['FirstName'].'</td>';
												echo '	<td>'.$attstud['LastName'].'</td>';
												$currenttagsrow += 1;
											}
										}
									}
									?>
								</tbody>
							</table>
						</form>
					</div>
					<div id="homeroom" class="tab-pane fade">
						<h3>Homeroom</h3>
						<table class="table table-hover">
							<thead>
								<tr>
									<th scope="col">Row</th>
									<th scope="col">First Name</th>
									<th scope="col">Last Name</th>
									<th scope="col">Room Number</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$homeroomrow = 1;
								foreach($homeroomstudents as $homestud) {
									echo '<tr>';
									echo '	<th scope="row">'.$homeroomrow.'</th>';
									echo '	<td>'.$homestud['FirstName'].'</td>';
									echo '	<td>'.$homestud['LastName'].'</td>';
									foreach($appointments as $appt) {
										if($appt['StudentID']==$homestud['UserID'] && $appt['Date']==$date) {
											foreach($rooms as $roo) {
												if($roo['RoomID']==$appt['RoomID']) {
													echo '	<td>'.$roo['RoomNumber'].'</td>';
													break;
												}
											}
										}
									}
									echo '</tr>';
									$homeroomrow += 1;
								}
								?>
							</tbody>
						</table>
					</div>
					<div id="attendance" class="tab-pane fade">
						<h3 style="display:inline-block">Attendance</h3>
						<form method="post" action="">
							<button class="btn btn-primary" type="submit" style="float:right" name="attsub">Submit Attendance</button>
							<table class="table table-hover">
								<thead>
									<tr>
										<th scope="col">Row</th>
										<th scope="col">First Name</th>
										<th scope="col">Last Name</th>
										<th scope="col">Attendance</th>
									</tr>
								</thead>
								<tbody>
								<?php
									$attendancerow = 1;
									foreach($allstudents as $attstud) {
										foreach($teacherappointments as $appt) {
											if($appt['StudentID']==$attstud['StudentID'] && $appt['TeacherID']==$teacherresult['UserID'] && $appt['Date']==$date) {
												echo '<tr>';
												echo '	<th scope="row">'.$attendancerow.'</th>';
												echo '	<td>'.$attstud['FirstName'].'</td>';
												echo '	<td>'.$attstud['LastName'].'</td>';
												echo '	<td>';
												echo '		<select class="selectpicker" name="'.$attstud['StudentID'].'" data-apptid="'.$appt['AppointmentID'].'">';
												echo '			<option value="Present">Present</option>';
												echo '			<option value="Tardy">Tardy</option>';
												echo '			<option value="Absent">Absent</option>';
												echo '		</select>';
												echo '	</td>';
												echo '</tr>';								
												echo '<input type="hidden" name="'.$attstud['StudentID'].'-att" value="'.$appt['AppointmentID'].'">';
												$attendancerow += 1;
											}
										}
									}
									?>
								</tbody>
							</table>
						</form>
					</div>
					<div id="tagging-ms" class="tab-pane fade">
						<h3 style="display:inline-block">Tagging - My Students</h3>
						<form method="post" action="">
							<button class="btn btn-primary" type="submit" style="float:right" name="tagsub">Submit Tags</button>
							<div class="form-group col-md-8 col-md-offset-2" id="week-picker-wrapper">
                                <label for="week" class="control-label">Select Week</label>
                                <div class="input-group">
                                    <input type="text" class="form-control week-picker" placeholder="Select a Week">
                                </div>
                            </div>
							<input type="hidden" id="mondate" name="mondate" class="monday">
							<input type="hidden" id="tuesdate" name="tuesdate" class="tuesday">
							<input type="hidden" id="wednesdate" name="wednesdate" class="wednesday">
							<input type="hidden" id="thursdate" name="thursdate" class="thursday">
							<input type="hidden" id="fridate" name="fridate" class="friday">
							<input class="form-control" id="mystudentinput" type="text" placeholder="Search Students">
							<table id="mystudentstable" class="table table-bordered table-hover" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th scope="col">Row</th>
										<th scope="col">Name</th>
										<?php
											$dayarray = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];
											foreach($dayarray as $dayarr) {
												//if(!in_array($dayarr, $notags)) {
													echo '<th scope="col" id="'.$dayarr.'" data-date="">'.$dayarr.'</th>';
												//}
											}
										?>
									</tr>
								</thead>
								<tbody id="mystudentstablebody">
								<?php
									$tagmsrow = 1;
									foreach($students as $tagstud) {
										//foreach($appointments as $appt) {
											//if($appt['StudentID']==$tagstud['StudentID'] && $appt['TeacherID']!=$teacherresult['UserID'] && $appt['isoverwritable']=="False") {
												echo '<tr>';
												echo '	<th scope="row">'.$tagmsrow.'</th>';
												echo '	<td>'.$tagstud['FirstName'].' '.$tagstud['LastName'].'</td>';
												echo '	<td>';
												echo '		<div class="custom-control custom-checkbox">';
												if(in_array("Monday", $notagdays)) {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-mon" disabled>';
												}
												else {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-mon">';
												}
												echo '			<input type="hidden" name="'.$tagstud['StudentID'].'-monday" class="monday" value="">';
												echo '		</div>';
												echo '	</td>';
												echo '	<td>';
												echo '		<div class="custom-control custom-checkbox">';
												if(in_array("Tuesday", $notagdays)) {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-tue" disabled>';
												}
												else {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-tue">';
												}
												echo '			<input type="hidden" name="'.$tagstud['StudentID'].'-tuesday" class="tuesday" value="">';
												echo '		</div>';
												echo '	</td>';
												echo '	<td>';
												echo '		<div class="custom-control custom-checkbox">';
												if(in_array("Wednesday", $notagdays)) {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-wed" disabled>';
												}
												else {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-wed">';
												}
												echo '			<input type="hidden" name="'.$tagstud['StudentID'].'-wednesday" class="wednesday" value="">';
												echo '		</div>';
												echo '	</td>';
												echo '	<td>';
												echo '		<div class="custom-control custom-checkbox">';
												if(in_array("Thursday", $notagdays)) {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-thu" disabled>';
												}
												else {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-thu">';
												}
												echo '			<input type="hidden" name="'.$tagstud['StudentID'].'-thursday" class="thursday" value="">';
												echo '		</div>';
												echo '	</td>';
												echo '	<td>';
												echo '		<div class="custom-control custom-checkbox">';
												if(in_array("Friday", $notagdays)) {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-fri" disabled>';
												}
												else {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-fri">';
												}
												echo '			<input type="hidden" name="'.$tagstud['StudentID'].'-friday" class="friday" value="">';
												echo '		</div>';
												echo '	</td>';
												echo '</tr>';
												$tagmsrow += 1;
											//}
										//}
									}
									?>
								</tbody>
							</table>
						</form>
					</div>
					<div id="tagging-as" class="tab-pane fade">
						<h3 style="display:inline-block">Tagging - All Students</h3>
						<form method="post" action="">
							<button class="btn btn-primary" type="submit" style="float:right" name="tagsub">Submit Tags</button>
							<div class="form-group col-md-8 col-md-offset-2" id="week-picker-wrapper">
                                <label for="week" class="control-label">Select Week</label>
                                <div class="input-group">
                                    <input type="text" class="form-control week-picker" placeholder="Select a Week">
                                </div>
                            </div>
							<input type="hidden" id="mondate" name="mondate" class="monday">
							<input type="hidden" id="tuesdate" name="tuesdate" class="tuesday">
							<input type="hidden" id="wednesdate" name="wednesdate" class="wednesday">
							<input type="hidden" id="thursdate" name="thursdate" class="thursday">
							<input type="hidden" id="fridate" name="fridate" class="friday">
							<input class="form-control" id="allstudentinput" type="text" placeholder="Search Students">
							<table id="allstudentstable" class="table table-bordered table-hover" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th scope="col">Row</th>
										<th scope="col">Name</th>
										<?php
											$dayarray = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];
											foreach($dayarray as $dayarr) {
												//if(!in_array($dayarr, $notags)) {
													echo '<th scope="col" id="'.$dayarr.'" data-date="">'.$dayarr.'</th>';
												//}
											}
										?>
									</tr>
								</thead>
								<tbody id="allstudentstablebody">
								<?php
									$tagrow = 1;
									foreach($allstudents as $tagstud) {
										//foreach($appointments as $appt) {
											//if($appt['StudentID']==$tagstud['StudentID'] && $appt['TeacherID']!=$teacherresult['UserID'] && $appt['isoverwritable']=="False") {
												echo '<tr>';
												echo '<tr>';
												echo '	<th scope="row">'.$tagrow.'</th>';
												echo '	<td>'.$tagstud['FirstName'].' '.$tagstud['LastName'].'</td>';
												echo '	<td>';
												echo '		<div class="custom-control custom-checkbox">';
												if(in_array("Monday", $notagdays)) {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-mon-all" disabled>';
												}
												else {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-mon-all">';
												}
												echo '			<input type="hidden" name="'.$tagstud['StudentID'].'-monday-all" class="monday" value="">';
												echo '		</div>';
												echo '	</td>';
												echo '	<td>';
												echo '		<div class="custom-control custom-checkbox">';
												if(in_array("Tuesday", $notagdays)) {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-tue-all" disabled>';
												}
												else {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-tue-all">';
												}
												echo '			<input type="hidden" name="'.$tagstud['StudentID'].'-tuesday-all" class="tuesday" value="">';
												echo '		</div>';
												echo '	</td>';
												echo '	<td>';
												echo '		<div class="custom-control custom-checkbox">';
												if(in_array("Wednesday", $notagdays)) {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-wed-all" disabled>';
												}
												else {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-wed-all">';
												}
												echo '			<input type="hidden" name="'.$tagstud['StudentID'].'-wednesday-all" class="wednesday" value="">';
												echo '		</div>';
												echo '	</td>';
												echo '	<td>';
												echo '		<div class="custom-control custom-checkbox">';
												if(in_array("Thursday", $notagdays)) {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-thu-all" disabled>';
												}
												else {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-thu-all">';
												}
												echo '			<input type="hidden" name="'.$tagstud['StudentID'].'-thursday-all" class="thursday" value="">';
												echo '		</div>';
												echo '	</td>';
												echo '	<td>';
												echo '		<div class="custom-control custom-checkbox">';
												if(in_array("Friday", $notagdays)) {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-fri-all" disabled>';
												}
												else {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-fri-all">';
												}
												echo '			<input type="hidden" name="'.$tagstud['StudentID'].'-friday-all" class="friday" value="">';
												echo '		</div>';
												echo '	</td>';
												echo '</tr>';
												$tagrow += 1;
											//}
										//}
									}
									?>
								</tbody>
							</table>
						</form>
					</div>
					<div id="tagging-ec" class="tab-pane fade">
						<h3 style="display:inline-block">Tagging - Extra Curricular</h3>
						<form method="post" action="">
							<button class="btn btn-primary" type="submit" style="float:right" name="tagsub">Submit Tags</button>
							<div class="form-group col-md-8 col-md-offset-2" id="week-picker-wrapper">
                                <label for="week" class="control-label">Select Week</label>
                                <div class="input-group">
                                    <input type="text" class="form-control week-picker" placeholder="Select a Week">
                                </div>
                            </div>
							<input type="hidden" id="mondate" name="mondate" class="monday">
							<input type="hidden" id="tuesdate" name="tuesdate" class="tuesday">
							<input type="hidden" id="wednesdate" name="wednesdate" class="wednesday">
							<input type="hidden" id="thursdate" name="thursdate" class="thursday">
							<input type="hidden" id="fridate" name="fridate" class="friday">
							<input class="form-control" id="ecstudentinput" type="text" placeholder="Search Students">
							<table id="ecstudentstable" class="table table-bordered table-hover" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th scope="col">Row</th>
										<th scope="col">Name</th>
										<?php
											$dayarray = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];
											foreach($dayarray as $dayarr) {
												//if(!in_array($dayarr, $notags)) {
													echo '<th scope="col" id="'.$dayarr.'" data-date="">'.$dayarr.'</th>';
												//}
											}
										?>
									</tr>
								</thead>
								<tbody id="ecstudentstablebody">
								<?php
									$tagrow = 1;
									foreach($allstudents as $tagstud) {
										//foreach($appointments as $appt) {
											//if($appt['StudentID']==$tagstud['StudentID'] && $appt['TeacherID']!=$teacherresult['UserID'] && $appt['isoverwritable']=="False") {
												echo '<tr>';
												echo '<tr>';
												echo '	<th scope="row">'.$tagrow.'</th>';
												echo '	<td>'.$tagstud['FirstName'].' '.$tagstud['LastName'].'</td>';
												echo '	<td>';
												echo '		<div class="custom-control custom-checkbox">';
												if(in_array("Monday", $notagdays)) {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-mon-ec" disabled>';
												}
												else {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-mon-ec">';
												}
												echo '			<input type="hidden" name="'.$tagstud['StudentID'].'-monday-ec" class="monday" value="">';
												echo '		</div>';
												echo '	</td>';
												echo '	<td>';
												echo '		<div class="custom-control custom-checkbox">';
												if(in_array("Tuesday", $notagdays)) {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-tue-ec" disabled>';
												}
												else {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-tue-ec">';
												}
												echo '			<input type="hidden" name="'.$tagstud['StudentID'].'-tuesday-ec" class="tuesday" value="">';
												echo '		</div>';
												echo '	</td>';
												echo '	<td>';
												echo '		<div class="custom-control custom-checkbox">';
												if(in_array("Wednesday", $notagdays)) {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-wed-ec" disabled>';
												}
												else {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-wed-ec">';
												}
												echo '			<input type="hidden" name="'.$tagstud['StudentID'].'-wednesday-ec" class="wednesday" value="">';
												echo '		</div>';
												echo '	</td>';
												echo '	<td>';
												echo '		<div class="custom-control custom-checkbox">';
												if(in_array("Thursday", $notagdays)) {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-thu-ec" disabled>';
												}
												else {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-thu-ec">';
												}
												echo '			<input type="hidden" name="'.$tagstud['StudentID'].'-thursday-ec" class="thursday" value="">';
												echo '		</div>';
												echo '	</td>';
												echo '	<td>';
												echo '		<div class="custom-control custom-checkbox">';
												if(in_array("Friday", $notagdays)) {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-fri-ec" disabled>';
												}
												else {
													echo '			<input type="checkbox" class="custom-control-input" name="'.$tagstud['StudentID'].'-fri-ec">';
												}
												echo '			<input type="hidden" name="'.$tagstud['StudentID'].'-friday-ec" class="friday" value="">';
												echo '		</div>';
												echo '	</td>';
												echo '</tr>';
												$tagrow += 1;
											//}
										//}
									}
									?>
								</tbody>
							</table>
						</form>
					</div>
					<div id="requests" class="tab-pane fade">
						<h3 style="display:inline-block">Tag Requests</h3>
						<form method="post" action="">
							<button class="btn btn-primary" type="submit" style="float:right" name="tagsub">Submit Tags</button>
							<div class="form-group col-md-8 col-md-offset-2" id="week-picker-wrapper">
                                <label for="week" class="control-label">Select Week</label>
                                <div class="input-group">
                                    <input type="text" class="form-control week-picker" placeholder="Select a Week">
                                </div>
                            </div>
							<table class="table table-hover">
								<thead>
									<tr>
										<th scope="col">Name</th>
										<?php
											$dayarray = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];
											foreach($dayarray as $dayarr) {
												//if(!in_array($dayarr, $notags)) {
													echo '<th scope="col" id="'.$dayarr.'" data-date="">'.$dayarr.'</th>';
												//}
											}
										?>
									</tr>
								</thead>
								<tbody>
								<?php
									$reqrow = 1;
									foreach($students as $reqstud) {
										foreach($apptreqs as $apptrequest) {
											if($apptrequest['StudentID']==$reqstud['StudentID'] && $apptrequest['Status']=="Pending") {
												echo '<tr>';
												echo '	<th scope="row">'.$reqrow.'</th>';
												echo '	<td>'.$reqstud['FirstName'].' '.$attstud['LastName'].'</td>';
												echo '	<td>';
												echo '		<div class="custom-copntrol custom-checkbox">';
												echo '			<input type="checkbox" class="custom-control-input" id="'.$tagstud['StudentID'].'-req" name="monday">';
												echo '		</div>';
												echo '	</td>';
												echo '	<td>';
												echo '		<div class="custom-copntrol custom-checkbox">';
												echo '			<input type="checkbox" class="custom-control-input" id="'.$tagstud['StudentID'].'-req" name="tuesday">';
												echo '		</div>';
												echo '	</td>';
												echo '	<td>';
												echo '		<div class="custom-copntrol custom-checkbox">';
												echo '			<input type="checkbox" class="custom-control-input" id="'.$tagstud['StudentID'].'-req" name="wednesday">';
												echo '		</div>';
												echo '	</td>';
												echo '	<td>';
												echo '		<div class="custom-copntrol custom-checkbox">';
												echo '			<input type="checkbox" class="custom-control-input" id="'.$tagstud['StudentID'].'-req" name="thursday">';
												echo '		</div>';
												echo '	</td>';
												echo '	<td>';
												echo '		<div class="custom-copntrol custom-checkbox">';
												echo '			<input type="checkbox" class="custom-control-input" id="'.$tagstud['StudentID'].'-req" name="friday">';
												echo '		</div>';
												echo '	</td>';
												echo '</tr>';
												$reqrow += 1;
											}
										}
									}
									?>
								</tbody>
							</table>
						</form>
					</div>
				</div>
				
			</div>
		</div>
		<!-- /.container -->

	</body>
</html>


 
