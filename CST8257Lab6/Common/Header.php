<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en" style="position: relative; min-height: 100%;">
<head>
    <title>Online Course Registration</title>
    <meta charset="utf-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Latest Bootstrap 5 CSS link -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body style="padding-top: 50px; margin-bottom: 60px;">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="http://www.algonquincollege.com" style="padding: 10px">
                <img src="Common/img/AC.png" alt="Algonquin College" style="max-width:100%; max-height:100%;" />
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="Index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="CourseSelection.php">Course Selection</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="CurrentRegistration.php">Current Registration</a>
                    </li>
                    <li class="nav-item">
                        <?php if (isset($_SESSION['user'])): ?>
                            <a class="nav-link" href="Logout.php">Log Out</a>
                        <?php else: ?>
                            <a class="nav-link" href="Login.php">Log In</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>  
    </nav>
    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
