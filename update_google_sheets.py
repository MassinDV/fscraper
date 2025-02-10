import requests
from bs4 import BeautifulSoup
import gspread
from google.oauth2.service_account import Credentials
import schedule
import time

# Google Sheets API setup
SCOPES = ['https://www.googleapis.com/auth/spreadsheets']
SERVICE_ACCOUNT_FILE = 'credentials.json'  # Path to your Google Sheets API credentials file

# Authenticate with Google Sheets
creds = Credentials.from_service_account_file(SERVICE_ACCOUNT_FILE, scopes=SCOPES)
client = gspread.authorize(creds)

# Category URLs and their corresponding Google Sheets
CATEGORIES = {
    "Drama": {
        "url": "https://forja.ma/category/series?g=serie-drame&contentType=playlist&lang=fr",
        "sheet_id": "17v7DFNFQf9qX7oKPvxAr7kQvQr4IN0CuwyQp1foTVs4"
    },
    "Comedy": {
        "url": "https://forja.ma/category/series?g=comedie-serie&contentType=playlist&lang=fr",
        "sheet_id": "1Q791HvnWfbhWmAGX07ndL86MsJDsejdUWZi7b-MlZEs"
    },
    "Action": {
        "url": "https://forja.ma/category/series?g=action-serie&contentType=playlist&lang=fr",
        "sheet_id": "1dwUhHhwcaLZRKO3UaUcKgpc91GIVSIVO7iopx400rCM"
    },
    "History": {
        "url": "https://forja.ma/category/series?g=histoire-serie&contentType=playlist&lang=fr",
        "sheet_id": "1CjajZeds5Y1avDmLnVQEizpGzRlyuGjShilJVJOzYsI"
    },
    "Documentaries": {
        "url": "https://forja.ma/category/fnqxzgjwehxiksgbfujypbplresdjsiegtngcqwm&lang=fr",
        "sheet_id": "1861tQnmQxdi36zsy5aaKZU-f9emGQp7v9agTrB71Ai4"
    },
    "Kids": {
        "url": "https://forja.ma/category/uvrqdsllaeoaxfporlrbsqehcyffsoufneihoyow&lang=fr",
        "sheet_id": "16R5VH1tJvwzei44ghNCapgCZLiWpvLdWFk8H70eH2mE"
    }
}

def scrape_urls(category_url):
    """Scrape series URLs from the category page."""
    response = requests.get(category_url)
    if response.status_code != 200:
        print(f"Failed to fetch {category_url}")
        return []

    soup = BeautifulSoup(response.text, 'html.parser')
    urls = []

    # Find all links starting with /content/
    for link in soup.find_all('a', href=True):
        href = link['href']
        if href.startswith('/content/'):
            full_url = f"https://forja.ma{href}?lang=fr"
            urls.append(full_url)

    return urls

def update_google_sheet(sheet_id, urls):
    """Update the Google Sheet with the scraped URLs."""
    try:
        sheet = client.open_by_key(sheet_id).sheet1
        sheet.clear()  # Clear the existing data
        sheet.append_row(["Series URL"])  # Add header
        for url in urls:
            sheet.append_row([url])  # Add each URL to the sheet
        print(f"Updated Google Sheet: {sheet_id}")
    except Exception as e:
        print(f"Error updating Google Sheet: {e}")

def update_all_categories():
    """Update all categories in Google Sheets."""
    for category, data in CATEGORIES.items():
        print(f"Scraping {category}...")
        urls = scrape_urls(data['url'])
        if urls:
            update_google_sheet(data['sheet_id'], urls)
        else:
            print(f"No URLs found for {category}.")

# Schedule the script to run daily
schedule.every().day.at("00:00").do(update_all_categories)

if __name__ == "__main__":
    print("Starting script...")
    update_all_categories()  # Run once immediately
    while True:
        schedule.run_pending()
        time.sleep(1)
