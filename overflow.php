<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
//The following lines of code maintain the session from the login screen				
/*session_start();
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
}*/

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

$studentswithappointmentssql = "SELECT u.UserID as UserID FROM User as u INNER JOIN Appointment as a ON u.UserID = a.StudentID WHERE Date = '".$date."';";
$studentswithappointmentsquery = mysqli_query($conn, $studentswithappointmentssql) or die('Error on studentswithappointments');
$studentswithappointments = [];
while($studentswithappointmentsresult = mysqli_fetch_assoc($studentswithappointmentsquery)) {
	array_push($studentswithappointments, $studentswithappointmentsresult['UserID']);
}

$overflowroomssql = "SELECT RoomID, Capacity from Room WHERE TeacherID = 0 ORDER BY RoomNumber;";
$overflowroomsquery = mysqli_query($conn, $overflowroomssql) or die('Error on overflowroomsquery');
if($overflowroomsquery) {
	while($overflowroomsrow = mysqli_fetch_assoc($overflowroomsquery)) {
		$overflowroom = array(
			'RoomID' => $overflowroomsrow['RoomID'],
			'Capacity' => $overflowroomsrow['Capacity'],
			'Current' => 0
		);
		$overflowrooms[] = $overflowroom;
	}
}
else {
	//add error later
}

$roomssql = "SELECT RoomID, Capacity from Room WHERE TeacherID != 0 ORDER BY RoomNumber;";
$roomsquery = mysqli_query($conn, $roomssql) or die('Error on roomsquery');
$rooms = [];
if($roomsquery) {
	while($roomsrow = mysqli_fetch_assoc($roomsquery)) {
		$rooms[$roomsrow['RoomID']] = $roomsrow['Capacity'];
	}
}
else {
	//add error later
}

$studentsnoapptssql = "SELECT UserID FROM User WHERE UserID NOT IN (".implode(', ', $studentswithappointments)."0, 1) AND UserType LIKE 'Student';";
$studentsnoapptsquery = mysqli_query($conn, $studentsnoapptssql) or die(mysqli_error($conn));
$studentsnoappts = [];
while($studentsnoapptsresult = mysqli_fetch_assoc($studentsnoapptsquery)) {
	array_push($studentsnoappts, $studentsnoapptsresult['UserID']);
}

$partnersql = "SELECT UserID, PartnerID from User WHERE UserType LIKE 'Teacher';";
$partnerquery = mysqli_query($conn, $partnersql) or die('Error on partnerquery');
$partners = [];
if($partnerquery) {
	while($partnerresult = mysqli_fetch_assoc($partnerquery)) {
		$partners[$partnerresult['UserID']] = $partnerresult['PartnerID'];
	}
}
else {
	//add error later
}

$apptcountsql = "SELECT * FROM Appointment WHERE Date = '".$date."';";
$apptcountquery = mysqli_query($conn, $apptcountsql) or die('Error on apptcountquery');
$apptcount = [];
if($apptcountquery) {
	while($apptcountresult = mysqli_fetch_assoc($apptcountquery)) {
		if(array_key_exists($apptcountresult['RoomID'], $apptcount)) {
			$apptcount[$apptcountresult['RoomID']] += 1;
		}
		else {
			$apptcount[$apptcountresult['RoomID']] = 1;
		}
		//$apptcount[$apptcountresult['RoomID']] += 1;
	}
}
else {
	//add error later
}

$teacherroomsql = "SELECT RoomID, TeacherID FROM Room WHERE TeacherID != 0";
$teacherroomquery = mysqli_query($conn, $teacherroomsql) or die('Error on teacherroomquery');
$teacherroom = [];
if($teacherroomquery) {
	while($teacherroomresult = mysqli_fetch_assoc($teacherroomquery)) {
		$teacherroom[$teacherroomresult['TeacherID']] = $teacherroomresult['RoomID'];
	}
}
else {
	//add error later
}

$studenthomeroomsql = "SELECT e.StudentID as StudentID, s.TeacherID as TeacherID FROM Section as s INNER JOIN Enrollment as e on s.SectionID = e.SectionID WHERE Name like '%XLT%';";
$studenthomeroomquery = mysqli_query($conn, $studenthomeroomsql) or die('Error on studenthomeroomquery');
$studenthomeroom = [];
if($studenthomeroomquery) {
	while($studenthomeroomrow = mysqli_fetch_assoc($studenthomeroomquery)) {
		$studenthomeroom[$studenthomeroomrow['StudentID']] = $studenthomeroomrow['TeacherID'];
	}
}
else {
	//add error later
}

//still need to get number of already existing appointments for each room (teacher) so we can fill partner teachers to their capacity, should do the same thing with overflow rooms just in case.
//then need to insert teacher ids (0 for overflow) and student ids into appointments with the respective room ids for every student that doesn't have an appointment
//need to get teacher partner id for each student, likely using studentid -> homeroom section -> teacherid -> partnerid ->

foreach($studentsnoappts as $stud) {//$stud here will just be a userid of a student
	if(array_key_exists($stud, $studenthomeroom)) {
		if($apptcount[$teacherroom[$studenthomeroom[$stud]]] < $rooms[$teacherroom[$studenthomeroom[$stud]]]) {
			//insert sql code to create appointment with student and their homeroom teacher
			$insertappthrsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$stud.", ".$studenthomeroom[$stud].", '".$date."', ".$teacherroom[$studenthomeroom[$stud]].", 'True', 'False');";
			$insertappthrquery = mysqli_query($conn, $insertappthrsql) or die('Error on insertappthrquery');
		}
		else if(($apptcount[$teacherroom[$studenthomeroom[$stud]]] >= $rooms[$teacherroom[$studenthomeroom[$stud]]]) && $apptcount[$teacherroom[$partners[$studenthomeroom[$stud]]]] < $rooms[$teacherroom[$partners[$studenthomeroom[$stud]]]]) {
			//insert sql code to create appointment with student and their homeroom teacher's partner
			$insertapptparsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$stud.", ".$partners[$studenthomeroom[$stud]].", '".$date."', ".$teacherroom[$partners[$studenthomeroom[$stud]]].", 'True', 'False');";
			$insertapptparquery = mysqli_query($conn, $insertapptparsql) or die('Error on insertapptparquery');
		}
		else {
			foreach($overflowrooms as $over) {
				if($over['Count'] < $over['RoomCapacity']) {
					//insert sql code to create appt with overflow room
					$insertapptofsql = "INSERT INTO Appointment (StudentID, TeacherID, Date, RoomID, isOverwritable, Priority) VALUES (".$stud.", 0, '".$date."', ".$over['RoomID'].", 'True', 'False');";
					$insertapptofquery = mysqli_query($conn, $insertapptofsql) or die('Error on insertapptofquery');
					$over['Count'] += 1;
					break;
				}
			}
		}
	}
}
?>
