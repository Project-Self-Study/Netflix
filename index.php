<?php
    require_once 'header.php'; // Include header file with session management and theme handling
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
        <h2 class="category-title">Trending shows</h2>
        <div class="category" id="category1">
            <!-- General shows will be dynamically inserted here -->
        </div>

        <h2 class="category-title">Action</h2>
        <div class="category" id="action-thriller">
            <!-- Action shows will be dynamically inserted here -->
        </div>
        <h2 class="category-title">Thriller</h2>
        <div class="category" id="thriller">
            <!-- romance shows will be dynamically inserted here -->
        </div>
        <h2 class="category-title">Drama</h2>
        <div class="category" id="drama">
            <!-- drama shows will be dynamically inserted here -->
        </div>
        <h2 class="category-title">Comedy</h2>
        <div class="category" id="comedy">
            <!-- comedy shows will be dynamically inserted here -->
        </div>
        <h2 class="category-title">Science-Fiction</h2>
        <div class="category" id="sci-fi">
            <!-- sci-fi shows will be dynamically inserted here -->
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>

<?php
    require_once 'footer.php'; // Include header file with session management and theme handling
?>
