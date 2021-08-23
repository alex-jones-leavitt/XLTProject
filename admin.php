<?php
//The following lines of code maintain the session from the login screen				
session_start();
$session = $_SESSION['email'];
if($_SESSION['Type'] == "teacher") {
	header("Location: teacher.php");
}
else if($_SESSION['Type'] == "student") {
	header("Location: student.php");
}
//redirects to login if there is no session
if($session == NULL){
	header("Location: login.php");
}

date_default_timezone_set('America/Denver');
$date = date('Y-m-d');
$day = date('l', strtotime($date));

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Establishes connection with database and retrieves info for current user


// Create connection
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check connection
if (mysqli_connect_errno()) {
	die("Connection failed: " . mysqli_connect_error());
}
//gets teachers
$teachersql = "SELECT UserID, FirstName, LastName, SubjectID, PartnerID FROM User WHERE UserType like 'Teacher' ORDER BY LastName;";
$teacherquery = mysqli_query($conn, $teachersql) or die('Error on teacherquery');
if($teacherquery) {
	while($teacherrow = mysqli_fetch_assoc($teacherquery)) {
		$teacher = array(
			'UserID' => $teacherrow['UserID'],
			'FirstName' => $teacherrow['FirstName'],
			'LastName' => $teacherrow['LastName'],
			'SubjectID' => $teacherrow['SubjectID'],
			'PartnerID' => $teacherrow['PartnerID']
		);
		$teachers[] = $teacher;
		//$teachers[$teacherrow['UserID']] = $teacher
	}
}
else {
	//add error later
}

$subjectsql = "SELECT SubjectID, Name FROM Subject";
$subjectquery = mysqli_query($conn, $subjectsql) or die('Error on subjectquery');
$subjects = [];
$subnameid = [];
if($subjectquery) {
	while($subjectrow = mysqli_fetch_assoc($subjectquery)) {
		$subjects[$subjectrow['SubjectID']] = $subjectrow['Name'];
		$subnameid[$subjectrow['Name']] = $subjectrow['SubjectID'];
	}
}
else {
	//add error later
}

if ($_POST) {
   // Execute code (such as database updates) here.
	foreach($teachers as $t) {
		if(!empty($_POST[$t['UserID'].'-room'])) {
			echo"<script>alert('".$roomnumid[$_POST[$t['UserID'].'-room']]['RoomID']."');</script>";
			$insertteacherroomsql = "UPDATE Room SET TeacherID = ".$t['UserID']." WHERE RoomID = ".$roomnumid[$_POST[$t['UserID'].'-room']]['RoomID'].";";
			mysqli_query($conn, $insertteacherroomsql) or die(mysqli_error($conn));
			//$insertroomteachersql = "UPDATE User SET RoomID = ".$roomnumid[$_POST[$t['UserID']]]['RoomID']." WHERE UserID = ".$t['UserID'].";";
			//mysqli_query($conn, $insertroomteachersql) or die('Error on insertroomteacherquery');
		}
		
		if(!empty($_POST[$t['UserID'].'-subject'])) {
			$insertsubjectteachersql = "UPDATE User SET SubjectID = ".$subnameid[$_POST[$t['UserID'].'-subject']]." WHERE UserID = ".$t['UserID'].";";
			mysqli_query($conn, $insertsubjectteachersql) or die('Error on insertsubjectteacherquery');
		}
	}

	if(isset($_POST['notagday'])) {
		$insertnotagdaysql = "INSERT INTO NoTagDays (Day) VALUES ('".$_POST['notagday']."');";
		mysqli_query($conn, $insertnotagdaysql) or die('Error on insertnotagdayquery');
	}

	if(isset($_POST['notagdate'])) {
		$insertnotagdatesql = "INSERT INTO NoTagDays (Date) VALUES ('".date('Y-m-d', strtotime($_POST['notagdate']))."');";
		mysqli_query($conn, $insertnotagdatesql) or die('Error on insertnotagdatequery');
	}

	foreach($subnameid as $subid) {
		if(isset($_POST[$subid])) {
			$insertsubjectprioritysql = "INSERT INTO PriorityDays (SubjectID, Day) VALUES (".$subid.", '".$_POST[$subid]."');";
			mysqli_query($conn, $insertsubjectprioritysql) or die('Error on insertsubjectpriorityquery');
		}
	}

	if(isset($_POST['newsub'])) {
		$insertsubsql = "INSERT INTO Subject (Name) VALUES ('".$_POST['newsub']."');";
		mysqli_query($conn, $insertsubsql) or die('Error on insertsubquery');
	}

	if(isset($_POST['newroomnum']) && isset($_POST['newroomcap'])) {
		$insertroomsql = "INSERT INTO Room (RoomNumber, Capacity) VALUES (".$_POST['newroomnum'].", ".$_POST['newroomcap'].");";
		mysqli_query($conn, $insertroomsql) or die('Error on insertroomquery');
	}
   // Redirect to this page.
   header("Location: " . $_SERVER['REQUEST_URI']);
   exit();
}


$teacherroomsql = "SELECT RoomID, TeacherID FROM Room;";
$teacherroomquery = mysqli_query($conn, $teacherroomsql) or die('Error on teacherroomsql');
$teacherroomresult = [];
if($teacherroomquery) {
	while($teacherroomrow = mysqli_fetch_assoc($teacherroomquery)) {
		$teacherroomresult[$teacherroomrow['TeacherID']] = $teacherroomrow['RoomID'];
	}
}
else {
	//add error later
}

$roomsql = "SELECT RoomID, RoomNumber, Capacity, TeacherID FROM Room;";
$roomquery = mysqli_query($conn, $roomsql) or die('Error on roomquery');
$rooms = [];
$roomnumid = [];
if($roomquery) {
	while($roomrow = mysqli_fetch_assoc($roomquery)) {
		$room = array(
			'RoomNumber' => $roomrow['RoomNumber'],
			'Capacity' => $roomrow['Capacity'],
			'TeacherID' => $roomrow['TeacherID']
		);
		$rooms[$roomrow['RoomID']] = $room;
		
		$moor = array(
			'RoomID' => $roomrow['RoomID'],
			'Capacity' => $roomrow['Capacity'],
			'TeacherID' => $roomrow['TeacherID']
		);
		$roomnumid[$roomrow['RoomNumber']] = $moor;
	}
}

else {
	//add error later
}

$emptyroomsql = "SELECT * FROM Room WHERE TeacherID IS NULL;";
$emptyroomquery = mysqli_query($conn, $emptyroomsql) or die('Error on emptyroomquery');
$emptyrooms = [];
if($emptyroomquery) {
	while($emptyroomrow = mysqli_fetch_assoc($emptyroomquery)) {
		$emptyroom = array(
			'RoomID' => $emptyroomrow['RoomID'],
			'RoomNumber' => $emptyroomrow['RoomNumber'],
			'Capacity' => $emptyroomrow['Capacity']
		);
		$emptyrooms[] = $emptyroom;
	}
}
else {
	//add error later
}

//get no tag days
$notagdatesql = "SELECT Date FROM NoTagDays;";
$notagdatequery = mysqli_query($conn, $notagdatesql) or die(mysqli_error($conn));
//Array that contains all of the no tag days
$notagdates = mysqli_fetch_assoc($notagdatequery);

$notagdaysql = "SELECT Day FROM NoTagDays;";
$notagdayquery = mysqli_query($conn, $notagdaysql) or die(mysqli_error($conn));
//Array that contains all of the no tag days
$notagdays = mysqli_fetch_assoc($notagdayquery);



$prioritysql = "SELECT SubjectID, Day FROM PriorityDays";
$priorityquery = mysqli_query($conn, $prioritysql) or die('Error on priorityquery');
$priorities = [];
if($priorityquery) {
	while($priorityrow = mysqli_fetch_assoc($priorityquery)) {
		$priorities[$priorityrow['Day']][] = $priorityrow['SubjectID'];
	}
}
else {
	//add error later
}

$studentsql = "SELECT UserID, FirstName, LastName, Email, StudentNum, Grade FROM User;";
$studentquery = mysqli_query($conn, $studentsql) or die('Error on studentquery');
//Array that contains all of the student data
$students = [];
if($studentquery) {
	while ($studentrow = mysqli_fetch_assoc($studentquery)) {
		$student = array(
			'FirstName' => $studentrow['FirstName'],
			'LastName' => $studentrow['LastName'],
			'Email' => $studentrow['Email'],
			'StudentNum' => $studentrow['StudentNum'],
			'Grade' => $studentrow['Grade']
		);
		$students[$studentrow['UserID']] = $student;
	}
}

else {
	//add error later
}

$appointmentsql = "SELECT AppointmentID, StudentID, Date, RoomID, isOverwritable, Priority FROM Appointment WHERE Date = '" .$date. "';";
//This query could get too big.... will require testing and probably modification
$appointmentquery = mysqli_query($conn, $appointmentsql) or die('Error on appointmentquery');
//Array that contains all of the appointment data
if($appointmentquery) {
	while($appointmentrow = mysqli_fetch_assoc($appointmentquery)) {
		$appointment = array(
			'StudentID' => $appointmentrow['StudentID'],
			'Date' => $appointmentrow['Date'],
			'RoomID' => $appointmentrow['RoomID'],
			'isOverwitable' => $appointmentrow['isOverwritable'],
			'Priority' => $appointmentrow['Priority']
		);
		$appointments[$appointmentrow['AppointmentID']] = $appointment;
	}
}
else {
	//add error later
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

		<!-- Latest compiled and minified JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

		<!-- Stuff for select picker -->
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">

		<!-- Latest compiled and minified JavaScript -->
		<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

		<!-- (Optional) Latest compiled and minified JavaScript translation files -->
		<!--<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/i18n/defaults-*.min.js"></script>-->
		
		<!-- Custom styles for this template -->
	<!--	<link href="navbar-fixed-top.css" rel="stylesheet">-->

		<script>
			$(document).ready(function() {
  				  $('.selectpicker').selectpicker({
         				style: 'btn-default'
     				});
			});

			$(function() {	
				$('select').on('change', function(e) {
					let roomname = this.name + "-room";
					let subname = this.name + "ject";
					var el = document.getElementsByName(name);
					el.value = this.value;
					alert(el.value);
				});
			});
		</script>	
		<style>
			body {background-color:#6d272f;}
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
					<li class="active"><a data-toggle="tab" href="#home">Teachers-Room</a></li>
					<li><a data-toggle="tab" href="#teacherssub">Teachers-Subject</a></li>
					<li><a data-toggle="tab" href="#attendance">Attendance</a></li>
					<li><a data-toggle="tab" href="#tagging">No Tag Days</a></li>
					<li><a data-toggle="tab" href="#priority">Priority Days</a></li>
					<li><a data-toggle="tab" href="#subjects">Subjects</a></li>
					<li><a data-toggle="tab" href="#rooms">Rooms</a></li>
				</ul>

				<div class="tab-content">
					<div id="home" class="tab-pane fade in active">
						<h3 style="display:inline-block">Teachers</h3>
						<form method="post" action="">
							<button class="btn btn-primary" type="submit" style="float:right" name="teach-sub">Submit Changes</button>
							<table class="table table-hover">
								<thead>
									<tr>
										<th scope="col">Row</th>
										<th scope="col">Name</th>
										<th scope="col">Room</th>
									</tr>
								</thead>
								<tbody>
								<?php
									$teacherrownum = 1;
									foreach($teachers as $teach) {
										echo '<tr>';
										echo '	<th scope="row">'.$teacherrownum.'</th>';
										echo '	<td>'.$teach['FirstName'].' '.$teach['LastName'].'</td>';
										if(array_key_exists($teach['UserID'], $teacherroomresult)) {
											echo '	<td>';
											echo '		<select class="selectpicker" disabled>';
											echo '			<option value="">'.$rooms[$teacherroomresult[$teach['UserID']]]['RoomNumber'].'</option>';
											echo '		</select>';
											echo '		<input type="hidden" name="'.$teach['UserID'].'-room">';
											echo '	</td>';
										}
										else {
											echo '	<td>';
											echo '		<select class="selectpicker" data-live-search="true" name="'.$teach['UserID'].'" title="Please select a room">';
											echo '			<option value="" disabled selected>Select Room</option>';
											foreach($emptyrooms as $emproom) {
												echo '			<option value="'.$emproom['RoomNumber'].'">'.$emproom['RoomNumber'].'</option>';
											}
											echo '		</select>';
											echo '		<input type="hidden" name="'.$teach['UserID'].'-room">';
											echo '	</td>';
										}
										echo '</tr>';
										$teacherrownum += 1;
									}
									?>
								</tbody>
							</table>
						</form>
					</div>
					<div id="teacherssub" class="tab-pane fade">
						<h3 style="display:inline-block">Teachers</h3>
						<form method="post" action="">
							<button class="btn btn-primary" type="submit" style="float:right" name="teach-sub">Submit Changes</button>
							<table class="table table-hover">
								<thead>
									<tr>
										<th scope="col">Row</th>
										<th scope="col">Name</th>
										<th scope="col">Subject</th>
									</tr>
								</thead>
								<tbody>
								<?php
									$teacherrownum = 1;
									foreach($teachers as $teach) {
										echo '<tr>';
										echo '	<th scope="row">'.$teacherrownum.'</th>';
										echo '	<td>'.$teach['FirstName'].' '.$teach['LastName'].'</td>';
										if(!is_null($teach['SubjectID'])) {
											echo '	<td>';
											echo '		<select class="selectpicker" disabled>';
											echo '			<option value="">'.$subjects[$teach['SubjectID']].'</option>';
											echo '		</select>';
											echo '		<input type="hidden" name="'.$teach['UserID'].'-subject">';
											echo '	</td>';
										}
										else {
											echo '	<td>';
											echo '		<select class="selectpicker" name="'.$teach['UserID'].'-sub" title="Please select a subject">';
											echo '			<option value="" disabled selected>Select Subject</option>';

											foreach($subjects as $sub) {
												echo '			<option value="'.$sub.'">'.$sub.'</option>';
											}
											echo '		</select>';
											echo '		<input type="hidden" name="'.$teach['UserID'].'-subject">';
											echo '	</td>';
										}
										echo '</tr>';
										$teacherrownum += 1;
									}
									?>
								</tbody>
							</table>
						</form>
					</div>
					<div id="attendance" class="tab-pane fade">
						<h3>Attendance</h3>
						<input type="checkbox" class="custom-control-input" name="attendance-box">
						<p>Are you sure you want to export attendance?</p>
						<button class="btn btn-primary" type="submit" style="float:right" name="attendance-sub">Export Attendance</button>
					</div>
					<div id="tagging" class="tab-pane fade">
						<h3 style="display:inline-block">No Tag Days</h3>
						<form method="post" action="">
							<button class="btn btn-primary" type="submit" style="float:right" name="notag-sub">Submit No Tag Days</button>
							<select class="selectpicker" name="notagday" title="Select a day for no tags">
								<option>Monday</option>
								<option>Tuesday</option>
								<option>Wednesday</option>
								<option>Thursday</option>
								<option>Friday</option>
							</select>
							<div class="form-group row">
								<label for="notag-date-input" class="col-2 col-form-label">Select a date for no tags</label>
								<div class="col-10">
									<input class="form-control" type="date" name="notagdate" id="notag-date-input">
								</div>
							</div>
						</form>
					</div>
					<div id="priority" class="tab-pane fade">
						<h3 style="display:inline-block">Priority Days</h3>
						<form method="post" action="">
							<button class="btn btn-primary" type="submit" style="float:right" name="priority-sub">Submit Priority Days</button>
							<table class="table table-hover">
								<thead>
									<tr>
										<th scope="col">Subject</th>
										<th scope="col">Day</th>
									</tr>
								</thead>
								<tbody>
								<?php
									$priorityrow = 1;
									foreach($subjects as $subj) {
										echo '<tr>';
										echo '	<th scope="row">'.$priorityrow.'</th>';
										echo '	<td>'.$subj.'</td>';
										echo '	<td>';
										echo '		<select class="selectpicker" name="'.$subnameid[$subj].'" title="Select a day for this subject">';
										echo '			<option>Monday</option>';
										echo '			<option>Tuesday</option>';
										echo '			<option>Wednesday</option>';
										echo '			<option>Thursday</option>';
										echo '			<option>Friday</option>';
										echo '		</select>';
										echo '	</td>';
										echo '</tr>';
										$priorityrow += 1;
									}
									?>
								</tbody>
							</table>
						</form>
					</div>
					<div id="subjects" class="tab-pane fade">
						<h3 style="display:inline-block">Subjects</h3>
						<form method="post" action="">
							<div class="form-group">
								<label for="newsubject">New Subject:</label>
								<input type="text" class="form-control" name="newsub" id="newsubject">
							</div>
							<button class="btn btn-primary" type="submit" style="float:right" name="priority-sub">Submit New Subject</button>
							<?php
								foreach($subjects as $subs) {
									echo '<p>'.$subs.'</p>';
									echo '<br>';
								}
							?>
						</form>
					</div>
					<div id="rooms" class="tab-pane fade">
						<h3 style="display:inline-block">Rooms</h3>
						<form method="post" action="">
							<input type="text" class="form-control" name="newroomnum" placeholder="Please enter new room number">
							<input type="text" class="form-control" name="newroomcap" placeholder="Please enter new room capacity">
							<button class="btn btn-primary" type="submit" style="float:right" name="priority-sub">Submit New Room</button>
							<table class="table table-hover">
								<thead>
									<tr>
										<th scope="col">Room Number</th>
										<th scope="col">Capacity</th>
									</tr>
								</thead>
								<tbody>
								<?php
									$roomrow = 1;
									foreach($rooms as $roo) {
										echo '<tr>';
										echo '	<th scope="row">'.$roomrow.'</th>';
										echo '	<td>'.$roo['RoomNumber'].'</td>';
										echo '	<td>'.$roo['Capacity'].'</td>';
										echo '</tr>';
										$roomrow += 1;
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


