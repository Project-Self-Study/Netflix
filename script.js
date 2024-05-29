document.addEventListener("DOMContentLoaded", function() {
    fetchShows();

    function fetchShows() {
        // Fetch general shows first
        const generalRequestBody = {
            type: "getShows",
            return: 20,
            search: {
                language: "English",
                rating_min: 7.0
            },
            sort: "rating",
            order: "DESC"
        };

        fetchShowsFromAPI(generalRequestBody, populateGeneralShows);
    }

    function fetchActionShows() {
        // Fetch action shows
        const actionRequestBody = {
            type: "getShows",
            return: 20,
            search: {
                language: "English",
                rating_min: 8.0,
                genres: "Action"
            },
            sort: "rating",
            order: "DESC"
        };

        fetchShowsFromAPI(actionRequestBody, populateActionShows);
    }

    function fetchThrillerShows() {
        // Fetch thriller shows
        const thrillerRequestBody = {
            type: "getShows",
            return: 20,
            search: {
                language: "English",
                rating_min: 8.0,
                genres: "Thriller"
            },
            sort: "rating",
            order: "DESC"
        };

        fetchShowsFromAPI(thrillerRequestBody, populateThrillerShows);
    }

    function fetchDramaShows() {
        // Fetch drama shows
        const dramaRequestBody = {
            type: "getShows",
            return: 20,
            search: {
                language: "English",
                rating_min: 8.0,
                genres: "Drama"
            },
            sort: "rating",
            order: "DESC"
        };

        fetchShowsFromAPI(dramaRequestBody, populateDramaShows);
    }

    function fetchComedyShows() {
        // Fetch comedy shows
        const comedyRequestBody = {
            type: "getShows",
            return: 20,
            search: {
                language: "English",
                rating_min: 8.0,
                genres: "Comedy"
            },
            sort: "rating",
            order: "DESC"
        };

        fetchShowsFromAPI(comedyRequestBody, populateComedyShows);
    }

    function fetchSciFiShows() {
        // Fetch sci-fi shows
        const sciFiRequestBody = {
            type: "getShows",
            return: 20,
            search: {
                language: "English",
                rating_min: 8.0,
                genres: "Science-Fiction"
            },
            sort: "rating",
            order: "DESC"
        };

        fetchShowsFromAPI(sciFiRequestBody, populateSciFiShows);
    }

    function fetchShowsFromAPI(requestBody, callback) {
        const url = 'Area221_API.php'; // Replace with your API endpoint

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestBody)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                callback(data.data);
            } else {
                console.error("Error fetching shows:", data);
            }
        })
        .catch(error => console.error("Error:", error));
    }

    function populateGeneralShows(shows) {
        const category1 = document.getElementById('category1');
        category1.innerHTML = ''; // Clear existing content

        shows.forEach(show => {
            const showElement = document.createElement('div');
            showElement.classList.add('show');

            showElement.innerHTML = `
                <div class="show-title">
                    <h3>${show.name}</h3>
                </div>
                <div class="show-image-container">
                    <img class="show-image" src="${show.image}" alt="${show.name}">
                </div>
            `;

            category1.appendChild(showElement);
        });

        // After populating general shows, fetch action shows
        fetchActionShows();
    }

    function populateActionShows(shows) {
        const actionCategory = document.getElementById('action-thriller');
        actionCategory.innerHTML = ''; // Clear existing content

        shows.forEach(show => {
            const showElement = document.createElement('div');
            showElement.classList.add('show');

            showElement.innerHTML = `
                <div class="show-title">
                    <h3>${show.name}</h3>
                </div>
                <div class="show-image-container">
                    <img class="show-image" src="${show.image}" alt="${show.name}">
                </div>
            `;

            actionCategory.appendChild(showElement);
        });

        // After populating action shows, fetch thriller shows
        fetchThrillerShows();
    }

    function populateThrillerShows(shows) {
        const thrillerCategory = document.getElementById('thriller');
        thrillerCategory.innerHTML = ''; // Clear existing content

        shows.forEach(show => {
            const showElement = document.createElement('div');
            showElement.classList.add('show');

            showElement.innerHTML = `
                <div class="show-title">
                    <h3>${show.name}</h3>
                </div>
                <div class="show-image-container">
                    <img class="show-image" src="${show.image}" alt="${show.name}">
                </div>
            `;

            thrillerCategory.appendChild(showElement);
        });

        // After populating thriller shows, fetch drama shows
        fetchDramaShows();
    }

    function populateDramaShows(shows) {
        const dramaCategory = document.getElementById('drama');
        dramaCategory.innerHTML = ''; // Clear existing content

        shows.forEach(show => {
            const showElement = document.createElement('div');
            showElement.classList.add('show');

            showElement.innerHTML = `
                <div class="show-title">
                    <h3>${show.name}</h3>
                </div>
                <div class="show-image-container">
                    <img class="show-image" src="${show.image}" alt="${show.name}">
                </div>
            `;

            dramaCategory.appendChild(showElement);
        });

        // After populating drama shows, fetch comedy shows
        fetchComedyShows();
    }

    function populateComedyShows(shows) {
        const comedyCategory = document.getElementById('comedy');
        comedyCategory.innerHTML = ''; // Clear existing content

        shows.forEach(show => {
            const showElement = document.createElement('div');
            showElement.classList.add('show');

            showElement.innerHTML = `
                <div class="show-title">
                    <h3>${show.name}</h3>
                </div>
                <div class="show-image-container">
                    <img class="show-image" src="${show.image}" alt="${show.name}">
                </div>
            `;

            comedyCategory.appendChild(showElement);
        });

        // After populating comedy shows, fetch sci-fi shows
        fetchSciFiShows();
    }

    function populateSciFiShows(shows) {
        const sciFiCategory = document.getElementById('sci-fi');
        sciFiCategory.innerHTML = ''; // Clear existing content

        shows.forEach(show => {
            const showElement = document.createElement('div');
            showElement.classList.add('show');

            showElement.innerHTML = `
                <div class="show-title">
                    <h3>${show.name}</h3>
                </div>
                <div class="show-image-container">
                    <img class="show-image" src="${show.image}" alt="${show.name}">
                </div>
            `;

            sciFiCategory.appendChild(showElement);
        });
    }
});
