<?php
$servername = "wheatley.cs.up.ac.za";
$username = "u22506773";
$password = "MAIUBAXLVHG5R7XOPBG2RAU65YCINHDB";
$dbname = "u22506773_dummy";
$showId = 1; // ID of the show to fetch

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from TVmaze API
function fetchShowData($showId) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://api.tvmaze.com/shows/$showId");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

$showData = fetchShowData($showId);
$showData = json_decode($showData, true);

// Prepare SQL statement
$stmt = $conn->prepare("INSERT INTO shows (id, name, language, genres, status, runtime, premiered, officialSite, summary, rating, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssisssds", $id, $name, $language, $genres, $status, $runtime, $premiered, $officialSite, $summary, $rating, $image);

// Extract data from JSON
$id = $showData['id'];
$name = $showData['name'];
$language = $showData['language'];
$genres = implode(',', $showData['genres']);
$status = $showData['status'];
$runtime = $showData['runtime'];
$premiered = $showData['premiered'];
$officialSite = $showData['officialSite'];
$summary = $showData['summary'];
$rating = isset($showData['rating']['average']) ? $showData['rating']['average'] : NULL;
$image = isset($showData['image']['original']) ? $showData['image']['original'] : NULL;

// Execute SQL statement
$stmt->execute();
echo "New show record created successfully";

$stmt->close();
$conn->close();
?>
