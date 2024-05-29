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

    function fetchRomanceShows() {
        // Fetch romance shows
        const romanceRequestBody = {
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

        fetchShowsFromAPI(romanceRequestBody, populateRomanceShows);
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

        // After populating action shows, fetch romance shows
        fetchRomanceShows();
    }

    function populateRomanceShows(shows) {
        const romanceCategory = document.getElementById('thriller');
        romanceCategory.innerHTML = ''; // Clear existing content

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

            romanceCategory.appendChild(showElement);
        });

        // After populating romance shows, fetch drama shows
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
    }
});
