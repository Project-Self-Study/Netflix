document.addEventListener("DOMContentLoaded", function() {
    fetchShows();

    function fetchShows() {
        const url = 'Area221_API.php'; // Replace with your API endpoint
        const requestBody = {
            type: "getShows",
            return: 20,
            search: {
                language: "English",
                rating_min: 7.0
            },
            sort: "rating",
            order: "DESC"
        };

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
                populateShows(data.data);
            } else {
                console.error("Error fetching shows:", data);
            }
        })
        .catch(error => console.error("Error:", error));
    }

    function populateShows(shows) {
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
                <!--
                <div class="show-description">
                    <p>${show.summary}</p>
                </div>
                -->
            `;

            category1.appendChild(showElement);
        });
    }
});
