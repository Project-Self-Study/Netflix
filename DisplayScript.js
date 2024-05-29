function displayListings(listings) {
    const container = document.getElementById('show-list-container');  // Changed to new container ID
    container.innerHTML = '';

    listings.forEach((listing) => {
        const showItem = document.createElement('div');  // Changed to new item class
        showItem.classList.add('show-item');  // Changed to new item class

        // Truncate summary if it exceeds the maximum length
        const maxSummaryLength = 200;
        const truncatedSummary = listing.summary.length > maxSummaryLength ? listing.summary.substring(0, maxSummaryLength) + "..." : listing.summary;

        // Populate the show item with data
        showItem.innerHTML = `
            <h3>${listing.name}</h3>
            <img src="${listing.image}" alt="Show Image">
            <p>Language: ${listing.language}</p>
            <p>Genres: ${listing.genres}</p>
            <p>Status: ${listing.status}</p>
            <p>Runtime: ${listing.runtime} minutes</p>
            <p>Premiered: ${listing.premiered}</p>
            <p>Rating: ${listing.rating}</p>
            <p>Summary: <span class="summary">${truncatedSummary}</span> <span class="read-more" onclick="toggleSummary(this, '${listing.summary}', '${truncatedSummary}')">Read more</span></p>
            <a href="${listing.officialSite ? listing.officialSite : '#'}" target="_blank" class="official-site">Official Site</a>
        `;
        container.appendChild(showItem);
    });
}

// Example toggle functions for "Read more" functionality
function toggleSummary(element, fullText, truncatedText) {
    const summarySpan = element.previousSibling;
    if (summarySpan.textContent === truncatedText) {
        summarySpan.textContent = fullText;
        element.textContent = 'Read less';
    } else {
        summarySpan.textContent = truncatedText;
        element.textContent = 'Read more';
    }
}

// Example usage with fetched data
const sampleApiResponse = {
    "status": "success",
    "timestamp": 1717015662,
    "data": [
        {
            "id": 169,
            "name": "Breaking Bad",
            "language": "English",
            "genres": "Drama,Crime,Thriller",
            "status": "Ended",
            "runtime": 60,
            "premiered": "2008-01-20",
            "officialSite": "http://www.amc.com/shows/breaking-bad",
            "summary": "<p><b>Breaking Bad</b> follows protagonist Walter White, a chemistry teacher who lives in New Mexico with his wife and teenage son who has cerebral palsy. White is diagnosed with Stage III cancer and given a prognosis of two years left to live. With a new sense of fearlessness based on his medical prognosis, and a desire to secure his family's financial security, White chooses to enter a dangerous world of drugs and crime and ascends to power in this world. The series explores how a fatal diagnosis such as White's releases a typical man from the daily concerns and constraints of normal society and follows his transformation from mild family man to a kingpin of the drug trade.</p>",
            "rating": 9.2,
            "image": "https://static.tvmaze.com/uploads/images/original_untouched/501/1253519.jpg"
        },
        {
            "id": 180,
            "name": "Firefly",
            "language": "English",
            "genres": "Drama,Adventure,Science-Fiction",
            "status": "Ended",
            "runtime": 60,
            "premiered": "2002-09-20",
            "officialSite": null,
            "summary": "<p>Five hundred years in the future, a renegade crew aboard a small spacecraft tries to survive as they travel the unknown parts of the galaxy and evade warring factions as well as authority agents out to get them.</p>",
            "rating": 9,
            "image": "https://static.tvmaze.com/uploads/images/original_untouched/1/2600.jpg"
        },
        {
            "id": 335,
            "name": "Sherlock",
            "language": "English",
            "genres": "Drama,Crime,Mystery",
            "status": "Ended",
            "runtime": 90,
            "premiered": "2010-07-25",
            "officialSite": "http://www.bbc.co.uk/programmes/b018ttws",
            "summary": "<p>Sherlock Holmes and Dr. John Watson's adventures in 21st Century London. A thrilling, funny, fast-paced contemporary reimagining of the Arthur Conan Doyle classic.</p>",
            "rating": 8.9,
            "image": "https://static.tvmaze.com/uploads/images/original_untouched/171/428042.jpg"
        }
    ]
};

// Call the function with the sample response data
displayListings(sampleApiResponse.data);
