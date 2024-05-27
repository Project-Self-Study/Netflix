<?php
set_time_limit(600); // Extend time limit to 10 minutes

$servername = "wheatley.cs.up.ac.za";
$username = "u22506773";
$password = "MAIUBAXLVHG5R7XOPBG2RAU65YCINHDB";
$dbname = "u22506773_dummy";

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function fetchShowData($showId) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://api.tvmaze.com/shows/$showId?embed[]=episodes&embed[]=cast&embed[]=crew");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

// Prepare SQL statements for inserting data
$stmtShows = $conn->prepare("INSERT INTO shows (id, name, language, genres, status, runtime, premiered, officialSite, summary, rating, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmtShows->bind_param("issssisssds", $id, $name, $language, $genres, $status, $runtime, $premiered, $officialSite, $summary, $rating, $image);

$stmtEpisodes = $conn->prepare("INSERT INTO episodes (show_id, season, number, name, airdate, runtime, summary, rating, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmtEpisodes->bind_param("iiissisds", $show_id, $season, $number, $episode_name, $airdate, $episode_runtime, $episode_summary, $episode_rating, $episode_image);

$stmtPeople = $conn->prepare("INSERT INTO people (id, name, birthday, deathday, gender, country, biography, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmtCastCredits = $conn->prepare("INSERT INTO cast_credits (person_id, show_id, character_) VALUES (?, ?, ?)");
$stmtCrewCredits = $conn->prepare("INSERT INTO crew_credits (person_id, show_id, role) VALUES (?, ?, ?)");

for ($showId = 1; $showId <= 50; $showId++) {
    $showData = fetchShowData($showId);
    $showData = json_decode($showData, true);

    if (isset($showData['id'])) {
        extract($showData);
        $genres = implode(',', $genres ?? []);
        $rating = $rating['average'] ?? 0;
        $image = $image['original'] ?? '';

        // Check if the show already exists in the shows table
        $checkShowStmt = $conn->prepare("SELECT COUNT(*) FROM shows WHERE id = ?");
        $checkShowStmt->bind_param("i", $id);
        $checkShowStmt->execute();
        $checkShowStmt->bind_result($showCount);
        $checkShowStmt->fetch();
        $checkShowStmt->close();

        if ($showCount == 0) {
            // Show doesn't exist, insert into shows table
            try {
                $stmtShows->execute();
            } catch (mysqli_sql_exception $e) {
                echo "Error inserting show ID $showId: " . $e->getMessage() . "\n";
            }
        } else {
            echo "Show ID $showId already exists in the database\n";
        }

        // Insert cast and crew credits only if the show was successfully inserted or already exists
        $cast = $showData['_embedded']['cast'] ?? [];
        $crew = $showData['_embedded']['crew'] ?? [];

        processPeople($cast, 'cast', $id);
        processPeople($crew, 'crew', $id);

        $episodes = $showData['_embedded']['episodes'] ?? [];
        foreach ($episodes as $episode) {
            extract($episode);
            try {
                $stmtEpisodes->execute();
            } catch (mysqli_sql_exception $e) {
                echo "Error inserting episode for show ID $showId: " . $e->getMessage() . "\n";
            }
        }
    } else {
        echo "No valid data for show ID $showId\n";
    }
}

echo "All data inserted successfully";

// Modified function definition to include $showId and handle duplicates
function processPeople($people, $type, $showId) {
    global $conn, $stmtPeople, $stmtCastCredits, $stmtCrewCredits;
    foreach ($people as $person) {
        $personData = $person['person'];
        $person_id = $personData['id'];
        $person_name = $personData['name'];
        $birthday = $personData['birthday'] ?: NULL;
        $deathday = $personData['deathday'] ?: NULL;
        $gender = $personData['gender'];
        $country = $personData['country']['name'] ?? '';
        $biography = $personData['biography'] ?? '';
        $person_image = $personData['image']['original'] ?? '';

        // Check if the person already exists in the people table
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM people WHERE id = ?");
        $checkStmt->bind_param("i", $person_id);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count == 0) {
            // Person doesn't exist, insert into people table
            try {
                $stmtPeople->bind_param("isssssss", $person_id, $person_name, $birthday, $deathday, $gender, $country, $biography, $person_image);
                $stmtPeople->execute();
            } catch (mysqli_sql_exception $e) {
                echo "Error inserting person $person_id: " . $e->getMessage() . "\n";
            }
        }

        if ($type === 'cast') {
            $character = $person['character']['name'];
            $stmtCastCredits->bind_param("iis", $person_id, $showId, $character);
            $stmtCastCredits->execute();
        } elseif ($type === 'crew') {
            $role = $person['type'];
            $stmtCrewCredits->bind_param("iis", $person_id, $showId, $role);
            $stmtCrewCredits->execute();
        }
    }
}

// Assuming you add this function if specific season details are needed:
function fetchSeasonData($showId) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://api.tvmaze.com/shows/$showId/seasons");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

$stmtSeasons = $conn->prepare("INSERT INTO seasons (show_id, number, episode_count, premiere_date, end_date, summary) VALUES (?, ?, ?, ?, ?, ?)");
$stmtSeasons->bind_param("iiissi", $show_id, $season_number, $episode_count, $premiere_date, $end_date, $summary);

for ($showId = 1; $showId <= 100; $showId++) {
    $showData = fetchShowData($showId);
    $seasonData = fetchSeasonData($showId); // Fetch season data for each show
    $showData = json_decode($showData, true);
    $seasonData = json_decode($seasonData, true); // Decode season data

    if (isset($showData['id'])) {
        extract($showData);
        $genres = implode(',', $genres ?? []);
        $rating = $rating['average'] ?? 0;
        $image = $image['original'] ?? '';

        // Check if the show already exists in the shows table
        $checkShowStmt = $conn->prepare("SELECT COUNT(*) FROM shows WHERE id = ?");
        $checkShowStmt->bind_param("i", $id);
        $checkShowStmt->execute();
        $checkShowStmt->bind_result($showCount);
        $checkShowStmt->fetch();
        $checkShowStmt->close();

        if ($showCount == 0) {
            // Show doesn't exist, insert into shows table
            try {
                $stmtShows->execute();
            } catch (mysqli_sql_exception $e) {
                echo "Error inserting show ID $showId: " . $e->getMessage() . "\n";
            }
        } else {
            echo "Show ID $showId already exists in the database\n";
        }

        if (!empty($seasonData)) {
            foreach ($seasonData as $season) {
                $season_number = $season['number'];
                $episode_count = $season['episodeOrder'] ?? 0; // or similar field
                $premiere_date = $season['premiereDate'] ?? NULL;
                $end_date = $season['endDate'] ?? NULL;
                $summary = $season['summary'] ?? '';
                $stmtSeasons->execute();
            }
        }
    } else {
        echo "No valid data for show ID $showId\n";
    }
}

echo "All data inserted successfully";

$stmtShows->close();
$stmtEpisodes->close();
$stmtPeople->close();
$stmtCastCredits->close();
$stmtCrewCredits->close();
$conn->close();