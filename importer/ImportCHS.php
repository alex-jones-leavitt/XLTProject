<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
//Establishes connection with database and retrieves info for current user


$conn = mysqli_connect($host, $username, $password, $dbname);

// Check connection
if (mysqli_connect_errno()) {
	die("Connection failed: " . mysqli_connect_error());
}

$sectionsql = "SELECT SectionID FROM Section;";
$sectionquery = mysqli_query($conn, $sectionsql) or die('Error on sectionquery');
$sectionresult = [];
if($sectionquery) {
	while($sectionresultrow = mysqli_fetch_assoc($sectionquery)) {
		$sectionresult[] = $sectionresultrow['SectionID'];
	}
}

$usersql = "SELECT UserID FROM User;";
$userquery = mysqli_query($conn, $usersql) or die('Error on userquery');
$userresult = [];
if($userquery) {
	while($userresultrow = mysqli_fetch_assoc($userquery)) {
		$userresult[] = $userresultrow['UserID'];
	}
}

$enrollmentsql = "SELECT SectionID, StudentID FROM Enrollment;";
$enrollmentquery = mysqli_query($conn, $enrollmentsql) or die('Error on enrollmentquery');
$enrollmentresult = [];
if($enrollmentquery) {
	while($enrollmentrow = mysqli_fetch_assoc($enrollmentquery)) {
			$enrollmentresult[$enrollmentrow['StudentID']][] = $enrollmentrow['SectionID']; //Does this actually work?  Trying to have each key go with an array of sectionIDs
	
	}
}
else {
	//add error later
}

$userheader = "FIRST_NAME";
$sectionheader = "SECTION_ID";

$teacherfile = '../chsupload/data/teachers.csv';
$studentfile = '../chsupload/data/students.csv';
$sectionfile = '../chsupload/data/sections.csv';
$enrollmentfile = '../chsupload/data/enrollments.csv';
$passwordjoe = password_hash('jinglin-joe-ingles', PASSWORD_DEFAULT);

if(file_exists($teacherfile)) {
	$teachers = fopen($teacherfile, "r");
	while(($teacher = fgetcsv($teachers, 1000, ",")) != FALSE) {
		if(!in_array($userheader, $teacher)) {
			if(!in_array($teacher[0], $userresult)) {
				$insertteachersql = "INSERT INTO User (UserID, FirstName, LastName, Email, Password, UserType) VALUES ({$teacher[0]}, '{$teacher[2]}', '{$teacher[3]}', '{$teacher[1]}', '".password_hash('jinglin-joe-ingles', PASSWORD_DEFAULT)."', 'Teacher');";
				mysqli_query($conn, $insertteachersql) or die('Unable to insert teacher data');
			}
		}
	}
	fclose($teachers);
	#unlink($teacherfile);
}
//insert admin user if not currently there
if(!in_array('111', $userresult)) {
	$insertadminsql = "INSERT INTO User (UserID, FirstName, LastName, Email, UserType) VALUES (111, 'Admin', 'Admin', 'admin@administrator.chs', 'Admin');";
	mysqli_query($conn, $insertadminsql) or die('Unable to insert admin data');
}
clearstatcache();
if(file_exists($studentfile)) {
	$students = fopen($studentfile, "r");
	while(($student = fgetcsv($students, 1000, ",")) != FALSE) {
		if(!in_array($userheader, $student)) {
			if(!in_array($student[0], $userresult)) {
				$insertstudentsql = "INSERT INTO User (UserID, FirstName, LastName, Email, Password, UserType, StudentNum) VALUES ({$student[0]}, \"{$student[3]}\", \"{$student[2]}\", \"{$student[5]}\", \"{$passwordjoe}\", \"Student\", {$student[1]});";
				mysqli_query($conn, $insertstudentsql) or die(mysqli_error($conn));
			}
		}
	}
	fclose($students);
	#unlink($studentfile);
}
clearstatcache();
if(file_exists($sectionfile)) {
	#echo "found sections";
	$sections = fopen($sectionfile, "r");
	while(($section = fgetcsv($sections, 1000, ",")) != FALSE) {
		if(!in_array($sectionheader, $section)) {
			if(!in_array($section[0], $sectionresult)) {
				$insertsectionsql = "INSERT INTO Section (SectionID, TeacherID, Name) VALUES (".$section[0].", ".$section[1].", '".$section[2]."');";
				mysqli_query($conn, $insertsectionsql) or die('Unable to insert section data');
			}
		}
	}
	fclose($sections);
	#unlink($sectionfile);
}
clearstatcache();
if(file_exists($enrollmentfile)) {
	#echo "found enrollments";
	$enrollments = fopen($enrollmentfile, "r");
	while(($enrollment = fgetcsv($enrollments, 1000, ",")) != FALSE) {
		if(!in_array($sectionheader, $enrollment)) {
			if(!array_key_exists($enrollment[1], $enrollmentresult)) {
				$insertenrollmentsql = "INSERT INTO Enrollment (SectionID, StudentID) VALUES (".$enrollment[0].", ".$enrollment[1].");";
				mysqli_query($conn, $insertenrollmentsql) or die('Unable to insert enrollment data');
			}
			elseif(!in_array($enrollment[0],$enrollmentresult[$enrollment[1]])) {
				$insertenrollmentsql = "INSERT INTO Enrollment (SectionID, StudentID) VALUES (".$enrollment[0].", ".$enrollment[1].");";
				mysqli_query($conn, $insertenrollmentsql) or die('Unable to insert enrollment data');
			}
		}
	}
	fclose($enrollments);
	#unlink($enrollmentfile);
}
?>
