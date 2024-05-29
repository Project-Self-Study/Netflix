<?php
    require_once 'header.php'; // Include the header file
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index.css"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <title>Document</title>
</head>
<body>
    <div id="container"></div>
    
    <h1 class="header">Stream.Binge.Repeat.Hoop has it all!</h1>

    <div id="categories">
        <h2 class="category-header">Browse shows</h2>
        <div class="category" id="category1">
            <!-- Shows will be dynamically inserted here -->
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
