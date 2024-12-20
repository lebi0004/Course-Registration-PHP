<?php
include_once 'EntityClassLib.php';

// Get PDO connection using Lab5.ini configuration
function getPDO() {
    // Parse the Lab5.ini file to get the database connection details
    $dbConnection = parse_ini_file("Lab5.ini");
    extract($dbConnection);

    // Create a new PDO connection using the extracted information
    $pdo = new PDO($dsn, $scriptUser, $scriptPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    return $pdo;
}


// Function to get a user by Student ID and Password (for login)
function getUserByIdAndPassword($userId, $password) {
    $pdo = getPDO();
    $sql = "SELECT StudentId, Name, Phone, Password FROM Student WHERE StudentId = :userId";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':userId', $userId);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && password_verify($password, $row['Password'])) {
        return new User($row['StudentId'], $row['Name'], $row['Phone'], $row['Password']);
    }
    return null;
}


function addNewUser($userId, $name, $phone, $password)
{
    $pdo = getPDO();
   
    $sql = "INSERT INTO Student VALUES( :studentId, :name, :phone, :password)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['studentId' => $userId, 'name' => $name, 'phone' => $phone, 'password' => $password]);
}


// Function to check if a student already exists by Student ID
function getStudentById($userId) {
    $pdo = getPDO();
    $sql = "SELECT StudentId FROM Student WHERE StudentId = :userId";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':userId', $userId);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all semesters
function getAllSemesters() {
    $pdo = getPDO();
    $sql = "SELECT * FROM Semester";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get registered hours for a user in a semester
function getRegisteredHours($studentId, $semesterCode) {
    $pdo = getPDO();
    $sql = "SELECT SUM(C.WeeklyHours) as RegisteredHours 
            FROM Registration R
            JOIN Course C ON R.CourseCode = C.CourseCode
            WHERE R.StudentId = :studentId AND R.SemesterCode = :semesterCode";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':studentId', $studentId);
    $stmt->bindValue(':semesterCode', $semesterCode);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['RegisteredHours'] : 0;
}

// Get courses available for a semester, excluding those already registered by the user
function getCoursesForSemester($semesterCode, $studentId) {
    $pdo = getPDO();
    $sql = "SELECT Course.CourseCode, Course.Title, Course.WeeklyHours
            FROM Course
            INNER JOIN CourseOffer ON Course.CourseCode = CourseOffer.CourseCode
            WHERE CourseOffer.SemesterCode = :semesterCode
            AND Course.CourseCode NOT IN (
                SELECT CourseCode FROM Registration WHERE StudentId = :studentId AND SemesterCode = :semesterCode
            )";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':semesterCode', $semesterCode);
    $stmt->bindValue(':studentId', $studentId);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get all courses a student has registered for
function getRegisteredCourses($studentId) {
    $pdo = getPDO();
    $sql = "SELECT C.CourseCode, C.Title, C.WeeklyHours, S.SemesterCode, S.Year, S.Term
            FROM Course C
            INNER JOIN Registration R ON C.CourseCode = R.CourseCode
            INNER JOIN Semester S ON R.SemesterCode = S.SemesterCode
            WHERE R.StudentId = :studentId";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':studentId', $studentId);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

