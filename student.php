<?php
//The following lines of code maintain the session from the login screen				
session_start();
$session = $_SESSION['email'];
if($_SESSION['Type'] == "teacher") {
	header("Location: teacher.php");
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
$startdate;
$enddate;

switch($day) {
	case "Monday":
		$startdate = $date;
		$enddate = date('Y-m-d', strtotime($date. ' + 4 days'));
		break;
	
	case "Tuesday":
		$startdate = date('Y-m-d', strtotime($date. ' - 1 days'));
		$enddate = date('Y-m-d', strtotime($date. ' + 3 days'));	
		break;
		
	case "Wednesday":
		$startdate = date('Y-m-d', strtotime($date. ' - 2 days'));
		$enddate = date('Y-m-d', strtotime($date. ' + 2 days'));	
		break;
		
	case "Thursday":
		$startdate = date('Y-m-d', strtotime($date. ' - 3 days'));
		$enddate = date('Y-m-d', strtotime($date. ' + 1 days'));	
		break;
	case "Friday":
		$startdate = date('Y-m-d', strtotime($date. ' - 4 days'));
		$enddate = $date;
		break;
	default:
		echo "error on day switch";
}

$studentsql = "SELECT UserID, FirstName, LastName, SubjectID, PartnerID FROM User WHERE Email like '".$session."';";
$studentquery = mysqli_query($conn, $studentsql) or die('Error on studentquery');
$studentresult = mysqli_fetch_assoc($studentquery);

$studentappointmentsql = "SELECT a.AppointmentID as AppointmentID, a.TeacherID as TeacherID, a.Date as Date, t.FirstName as FirstName, t.LastName as LastName, r.RoomNumber as RoomNumber FROM Appointment as a INNER JOIN User as t ON a.TeacherID = t.UserID INNER JOIN Room as r ON t.UserID = r.TeacherID WHERE StudentID = ".$studentresult['UserID']." AND Date >= '".$startdate."' AND Date <= '".$enddate."' ORDER BY Date ASC;";
$studentappointmentquery = mysqli_query($conn, $studentappointmentsql) or die('Error on studentappointmentquery');
if($studentappointmentquery) {
	while($appointmentrow = mysqli_fetch_assoc($studentappointmentquery)) {
		$appointment = array(
			'AppointmentID' => $appointmentrow['AppointmentID'],
			'TeacherID' => $appointmentrow['TeacherID'],
			'Date' => $appointmentrow['Date'],
			'FirstName' => $appointmentrow['FirstName'],
			'LastName' => $appointmentrow['LastName'],
			'RoomNumber' => $appointmentrow['RoomNumber'],
		);
		$appointments[] = $appointment;
	}
}
else {
	//add error later
}

//Gets rooms
$roomsql = "SELECT RoomID, RoomNumber, Capacity, TeacherID FROM Room";
$roomquery = mysqli_query($conn, $roomsql) or die('Error on roomquery');
if($roomquery) {
	while($roomrow = mysqli_fetch_assoc($roomquery)) {
		$room = array(
			'RoomID' => $appointmentrow['RoomID'],
			'RoomNumber' => $appointmentrow['RoomNumber'],
			'Capacity' => $appointmentrow['Capacity'],
			'TeacherID' => $appointmentrow['TeacherID']
		);
		$rooms[] = $room;
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
		
		<!-- Scripts necessary for the weekly date picker -->
		<script src="https://cdn.jsdelivr.net/momentjs/2.10.6/moment.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
		
		<!-- Custom styles for this template -->
		<link href="navbar-fixed-top.css" rel="stylesheet">
		
		<script>
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
				
				mondays = document.getElementsByclassName("monday");
				for(int i=0; i<mondays.length; i++) {
					mondays[i].value = monday;
				}
				tuesdays = document.getElementsByclassName("tuesday");
				for(int i=0; i<tuesdays.length; i++) {
					tuesdays[i].value = tuesday;
				}
				wednesdays = document.getElementsByclassName("wednesday");
				for(int i=0; i<wednesdays.length; i++) {
					wednesdays[i].value = wednesday;
				}
				thursdays = document.getElementsByclassName("thursday");
				for(int i=0; i<thursdays.length; i++) {
					thursdays[i].value = thursday;
				}
				fridays = document.getElementsByclassName("friday");
				for(int i=0; i<fridays.length; i++) {
					fridays[i].value = friday;
				}
				
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
				$('Monday').data('data-date', monday);
            });
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
					<li class="active"><a data-toggle="tab" href="#home">Schedule</a></li>
					<li><a data-toggle="tab" href="#requests">Requests</a></li>
				</ul>

				<div class="tab-content">
					<div id="home" class="tab-pane fade in active">
						<h3>Schedule</h3>
						<table class="table table-hover">
							<thead>
								<tr>
									<th scope="col">Day</th>
									<th scope="col">Teacher</th>
									<th scope="col">Room</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$daylist = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday");
								$curday = 0;
								$schedulerow = 1;
								foreach($appointments as $appt) {
									echo '<tr>';
									echo '	<th scope="row">'.$daylist[$curday].'</th>';
									switch($appt['TeacherID']) {
										case 0:
											echo '	<td>Overflow int</td>';
											break;
										case "0":
											echo '	<td>Overflow str</td>';
											break;
										default:
											echo '	<td>'.$appt['FirstName'].' '.$appt['LastName'].'</td>';
											break;											
									}
									echo '	<td>'.$appt['RoomNumber'].'</td>';
									echo '</tr>';
									$schedulerow += 1;
									$curday += 1;
								}
								?>
							</tbody>
						</table>
					</div>
					<div id="requests" class="tab-pane fade">
						<h3 style="display:inline-block">Requests - Coming Soon</h3>
					</div>
				</div>
				
			</div>
		</div>
		<!-- /.container -->

	</body>
</html>
