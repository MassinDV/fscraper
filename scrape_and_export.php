<?php
require_once 'vendor/autoload.php'; // Include Google API Client and simple_html_dom

use simplehtmldom\HtmlWeb;

// Function to scrape URLs from the category page
function scrapeUrlsFromCategory($categoryUrl) {
    $urls = [];

    // Create a new HtmlWeb client
    $client = new HtmlWeb();

    // Get the HTML content of the category page
    $html = $client->load($categoryUrl);

    if (!$html) {
        echo "Failed to load the HTML content from the URL: $categoryUrl\n";
        return $urls;
    }

    // Find all links with the specified structure
    foreach ($html->find('a[href^="/content/"]') as $element) {
        $path = $element->href;
        $fullUrl = "https://forja.ma$path?lang=fr";
        $urls[] = $fullUrl;
    }

    return $urls;
}

// Function to authenticate and append data to Google Sheets
function appendToGoogleSheet($spreadsheetId, $sheetName, $data) {
    // Load credentials from the JSON file
    $client = new Google_Client();
    $client->setApplicationName('Google Sheets API PHP');
    $client->setScopes(Google_Service_Sheets::SPREADSHEETS);
    $client->setAuthConfig('credentials.json'); // Path to your credentials JSON file
    $client->setAccessType('offline');

    // Create Google Sheets service
    $service = new Google_Service_Sheets($client);

    // Prepare the data to append
    $values = [];
    foreach ($data as $url) {
        $values[] = [$url];
    }

    // Define the range (e.g., Sheet1!A:A)
    $range = "$sheetName!A:A";

    // Create the value range object
    $body = new Google_Service_Sheets_ValueRange([
        'values' => $values
    ]);

    // Append the data to the sheet
    $params = [
        'valueInputOption' => 'RAW'
    ];

    $result = $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);

    echo "Data appended to Google Sheet: $spreadsheetId\n";
}

// Define category URLs and their corresponding Google Sheets
$categories = [
    'Drama' => [
        'url' => 'https://forja.ma/category/series?g=serie-drame&contentType=playlist&lang=fr',
        'sheetId' => '17v7DFNFQf9qX7oKPvxAr7kQvQr4IN0CuwyQp1foTVs4',
        'sheetName' => 'Sheet1'
    ],
    'Comedy' => [
        'url' => 'https://forja.ma/category/series?g=comedie-serie&contentType=playlist&lang=fr',
        'sheetId' => '1Q791HvnWfbhWmAGX07ndL86MsJDsejdUWZi7b-MlZEs',
        'sheetName' => 'Sheet1'
    ],
    'Action' => [
        'url' => 'https://forja.ma/category/series?g=action-serie&contentType=playlist&lang=fr',
        'sheetId' => '1dwUhHhwcaLZRKO3UaUcKgpc91GIVSIVO7iopx400rCM',
        'sheetName' => 'Sheet1'
    ],
    'History' => [
        'url' => 'https://forja.ma/category/series?g=histoire-serie&contentType=playlist&lang=fr',
        'sheetId' => '1CjajZeds5Y1avDmLnVQEizpGzRlyuGjShilJVJOzYsI',
        'sheetName' => 'Sheet1'
    ],
    'Documentaries' => [
        'url' => 'https://forja.ma/category/fnqxzgjwehxiksgbfujypbplresdjsiegtngcqwm&lang=fr',
        'sheetId' => '1861tQnmQxdi36zsy5aaKZU-f9emGQp7v9agTrB71Ai4',
        'sheetName' => 'Sheet1'
    ],
    'Kids' => [
        'url' => 'https://forja.ma/category/uvrqdsllaeoaxfporlrbsqehcyffsoufneihoyow&lang=fr',
        'sheetId' => '16R5VH1tJvwzei44ghNCapgCZLiWpvLdWFk8H70eH2mE',
        'sheetName' => 'Sheet1'
    ]
];

// Loop through each category
foreach ($categories as $categoryName => $categoryData) {
    echo "Scraping URLs for category: $categoryName\n";

    // Scrape URLs from the category page
    $urls = scrapeUrlsFromCategory($categoryData['url']);

    if (empty($urls)) {
        echo "No URLs found for category: $categoryName\n";
        continue;
    }

    // Append URLs to the corresponding Google Sheet
    appendToGoogleSheet($categoryData['sheetId'], $categoryData['sheetName'], $urls);
}

echo "All categories processed.\n";
?>
