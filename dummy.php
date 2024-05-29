<?php
set_time_limit(7200); // Extend time limit to 20 minutes

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
    $endpoint = "https://api.tvmaze.com/shows/$showId?embed[]=episodes&embed[]=cast&embed[]=crew";
    return makeApiRequest($endpoint);
}

function fetchSeasonData($showId) {
    $endpoint = "https://api.tvmaze.com/shows/$showId/seasons";
    return makeApiRequest($endpoint);
}

function fetchCrewData($showId) {
    $endpoint = "https://api.tvmaze.com/shows/$showId/crew";
    return makeApiRequest($endpoint);
}

function makeApiRequest($endpoint) {
    global $conn;
    $startTime = microtime(true);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $endpoint);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    $endTime = microtime(true);
    $requestTime = $endTime - $startTime;

    // Log API request
    $stmtApiRequests = $conn->prepare("INSERT INTO api_requests (endpoint, request_time, status, response) VALUES (?, ?, ?, ?)");
    $stmtApiRequests->bind_param("sdis", $endpoint, $requestTime, $status, $response);
    $stmtApiRequests->execute();
    $stmtApiRequests->close();

    // Cache the response
    $timestamp = date("Y-m-d H:i:s", time()); // Convert Unix timestamp to DATETIME format
    $stmtCache = $conn->prepare("INSERT INTO cache (endpoint, data, timestamp) VALUES (?, ?, ?)");
    $stmtCache->bind_param("sss", $endpoint, $response, $timestamp);
    $stmtCache->execute();
    $stmtCache->close();

    return $response;
}

// Prepare SQL statements for inserting data
$stmtShows = $conn->prepare("INSERT INTO shows (id, name, language, genres, status, runtime, premiered, officialSite, summary, rating, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmtShows->bind_param("issssisssds", $id, $name, $language, $genres, $status, $runtime, $premiered, $officialSite, $summary, $rating, $image);

$stmtSeasons = $conn->prepare("INSERT INTO seasons (show_id, number, episode_count, premiere_date, end_date, summary) VALUES (?, ?, ?, ?, ?, ?)");
$stmtSeasons->bind_param("iiissi", $show_id, $season_number, $episode_count, $premiere_date, $end_date, $summary);

$stmtEpisodes = $conn->prepare("INSERT INTO episodes (show_id, season, number, name, airdate, runtime, summary, rating, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmtEpisodes->bind_param("iiissisds", $show_id, $season, $number, $episode_name, $airdate, $episode_runtime, $episode_summary, $episode_rating, $episode_image);

$stmtPeople = $conn->prepare("INSERT INTO people (id, name, birthday, deathday, gender, country, biography, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmtPeople->bind_param("isssssss", $person_id, $person_name, $birthday, $deathday, $gender, $country, $biography, $person_image);

$stmtCastCredits = $conn->prepare("INSERT INTO cast_credits (person_id, show_id, character_) VALUES (?, ?, ?)");
$stmtCastCredits->bind_param("iis", $person_id, $show_id, $character);

$stmtCrewCredits = $conn->prepare("INSERT INTO crew_credits (person_id, show_id, role) VALUES (?, ?, ?)");
$stmtCrewCredits->bind_param("iis", $person_id, $show_id, $role);

// $stmtGuestCastCredits = $conn->prepare("INSERT INTO guest_cast_credits (person_id, episode_id, character_) VALUES (?, ?, ?)");
// $stmtGuestCastCredits->bind_param("iis", $person_id, $episode_id, $character);

// $stmtGuestCrewCredits = $conn->prepare("INSERT INTO guest_crew_credits (person_id, episode_id, role) VALUES (?, ?, ?)");
// $stmtGuestCrewCredits->bind_param("iis", $person_id, $episode_id, $role);

$stmtUpdates = $conn->prepare("INSERT INTO updates (show_id, timestamp) VALUES (?, ?)");
$stmtUpdates->bind_param("is", $show_id, $timestamp);

for ($showId = 1; $showId <= 5000; $showId++) {
    $showData = fetchShowData($showId);
    $showData = json_decode($showData, true);
    $seasonData = fetchSeasonData($showId);
    $seasonData = json_decode($seasonData, true);
    $crewData = fetchCrewData($showId);
    $crewData = json_decode($crewData, true);

    if (isset($showData['id'])) {
        $id = $showData['id'];
        $name = $showData['name'];
        $language = $showData['language'];
        $genres = implode(',', $showData['genres'] ?? []);
        $status = $showData['status'];
        $runtime = $showData['runtime'];
        $premiered = $showData['premiered'];
        $officialSite = $showData['officialSite'];
        $summary = $showData['summary'];
        $rating = $showData['rating']['average'] ?? 0;
        $image = $showData['image']['original'] ?? '';

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

        // Record update
        $timestamp = date("Y-m-d H:i:s", time());
        try {
            $stmtUpdates->execute();
        } catch (mysqli_sql_exception $e) {
            echo "Error inserting update for show ID $showId: " . $e->getMessage() . "\n";
        }

        // Insert season data
        if (!empty($seasonData)) {
            foreach ($seasonData as $season) {
                $show_id = $id;
                $season_number = $season['number'];
                $episode_count = $season['episodeOrder'] ?? 0;
                $premiere_date = $season['premiereDate'] ?? NULL;
                $end_date = $season['endDate'] ?? NULL;
                $summary = $season['summary'] ?? '';
                try {
                    $stmtSeasons->execute();
                } catch (mysqli_sql_exception $e) {
                    echo "Error inserting season for show ID $showId: " . $e->getMessage() . "\n";
                }
            }
        }

        // Insert episode data
        $episodes = $showData['_embedded']['episodes'] ?? [];
        foreach ($episodes as $episode) {
            $show_id = $id;
            $season = $episode['season'];
            $number = $episode['number'];
            $episode_name = $episode['name'];
            $airdate = $episode['airdate'];
            $episode_runtime = $episode['runtime'];
            $episode_summary = $episode['summary'];
            $episode_rating = $episode['rating']['average'] ?? 0;
            $episode_image = $episode['image']['original'] ?? '';
            try {
                $stmtEpisodes->execute();
            } catch (mysqli_sql_exception $e) {
                echo "Error inserting episode for show ID $showId: " . $e->getMessage() . "\n";
            }

            // Insert guest cast and crew credits
            // $guestCast = $episode['guestCast'] ?? [];
            // $guestCrew = $episode['guestCrew'] ?? [];
            // processGuestPeople($guestCast, 'guest_cast', $episode['id']);
            // processGuestPeople($guestCrew, 'guest_crew', $episode['id']);
        }

        // Insert people, cast and crew data
        $cast = $showData['_embedded']['cast'] ?? [];
        $crew = $crewData ?? [];
        processPeople($cast, 'cast', $id);
        processPeople($crew, 'crew', $id);
    } else {
        echo "No valid data for show ID $showId\n";
    }
}

echo "All data inserted successfully";

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

        echo "Processing person ID: $person_id, Name: $person_name\n";

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
                echo "Inserted person ID: $person_id, Name: $person_name\n";
            } catch (mysqli_sql_exception $e) {
                echo "Error inserting person $person_id: " . $e->getMessage() . "\n";
            }
        } else {
            echo "Person ID $person_id already exists in the database\n";
        }

        if ($type === 'cast') {
            $character = $person['character']['name'];
            try {
                $stmtCastCredits->bind_param("iis", $person_id, $showId, $character);
                $stmtCastCredits->execute();
                echo "Inserted cast credit for person ID: $person_id, Character: $character\n";
            } catch (mysqli_sql_exception $e) {
                echo "Error inserting cast credit for person $person_id: " . $e->getMessage() . "\n";
            }
        } elseif ($type === 'crew') {
            $role = $person['type'];
            try {
                $stmtCrewCredits->bind_param("iis", $person_id, $showId, $role);
                $stmtCrewCredits->execute();
                echo "Inserted crew credit for person ID: $person_id, Role: $role\n";
            } catch (mysqli_sql_exception $e) {
                echo "Error inserting crew credit for person $person_id: " . $e->getMessage() . "\n";
            }
        }
    }
}

function processGuestPeople($people, $type, $episodeId) {
    global $conn, $stmtGuestCastCredits, $stmtGuestCrewCredits;
    foreach ($people as $person) {
        $personData = $person['person'];
        $person_id = $personData['id'];
        $character = $person['character']['name'] ?? '';
        $role = $person['type'] ?? '';

        // if ($type === 'guest_cast') {
        //     try {
        //         $stmtGuestCastCredits->bind_param("iis", $person_id, $episodeId, $character);
        //         $stmtGuestCastCredits->execute();
        //         echo "Inserted guest cast credit for person ID: $person_id, Character: $character\n";
        //     } catch (mysqli_sql_exception $e) {
        //         echo "Error inserting guest cast credit for person $person_id: " . $e->getMessage() . "\n";
        //     }
        // } elseif ($type === 'guest_crew') {
        //     try {
        //         $stmtGuestCrewCredits->bind_param("iis", $person_id, $episodeId, $role);
        //         $stmtGuestCrewCredits->execute();
        //         echo "Inserted guest crew credit for person ID: $person_id, Role: $role\n";
        //     } catch (mysqli_sql_exception $e) {
        //         echo "Error inserting guest crew credit for person $person_id: " . $e->getMessage() . "\n";
        //     }
        // }
    }
}

$stmtShows->close();
$stmtSeasons->close();
$stmtEpisodes->close();
$stmtPeople->close();
$stmtCastCredits->close();
$stmtCrewCredits->close();
$stmtGuestCastCredits->close();
$stmtGuestCrewCredits->close();
$stmtUpdates->close();
$conn->close();
