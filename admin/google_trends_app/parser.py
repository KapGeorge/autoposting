import shutil
import csv
import pymysql
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time
import os

chrome_options = Options()
chrome_options.add_argument("--headless")
chrome_options.add_argument("--disable-gpu")
chrome_options.add_argument(
    "user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"
)

# set download directory
download_dir = os.path.abspath("downloads")
os.makedirs(download_dir, exist_ok=True)
prefs = {"download.default_directory": download_dir, "download.prompt_for_download": False}
chrome_options.add_experimental_option("prefs", prefs)

driver = webdriver.Chrome(options=chrome_options)

try:
    url = "https://trends.google.com/trending?geo=RU"
    driver.get(url)
    wait = WebDriverWait(driver, 15)

    # open "Export"
    export_btn = wait.until(
        EC.element_to_be_clickable((By.XPATH, "//button[.//span[contains(text(), 'Экспортировать')]]"))
    )
    export_btn.click()
    time.sleep(2) 

    # looking for <li> 
    lis = driver.find_elements(By.XPATH, "//li")
    csv_li = None
    for idx, li in enumerate(lis):
        print(f"{idx}: {li.text}")
        if "Скачать в формате CSV" in li.text:
            csv_li = li
            break

    if csv_li:
        driver.execute_script("arguments[0].scrollIntoView(true);", csv_li)
        driver.execute_script("arguments[0].click();", csv_li)
        print("clicked 'Download as CSV'")
        # wait for file to be downloaded  
        timeout = 15
        csv_file = None
        for _ in range(timeout):
            files = [f for f in os.listdir(download_dir) if f.endswith(".csv")]
            if files:
                csv_file = files[0]
                break
            time.sleep(1)
        if csv_file:
            shutil.move(os.path.join(download_dir, csv_file), "downloaded_trends.csv")
            print("file saved as downloaded_trends.csv")
        else:
            print("CSV file not found.")
    else:
        print("Not found 'Download as CSV'")
        driver.quit()
        exit()

finally:
    driver.quit()

# --- Process downloaded CSV and save to TXT ---

csv_path = "downloaded_trends.csv"
txt_path = "trends_grouped.txt"

if os.path.exists(csv_path):
    with open(csv_path, encoding="utf-8") as csvfile, open(txt_path, "w", encoding="utf-8") as txtfile:
        reader = csv.DictReader(csvfile)
        groups = {}
        for row in reader:
            popular = row.get("Популярные", "").strip()
            trend_str = row.get("Составляющие тренда", "").strip()
            # Split trend string by comma and remove empty values
            trends = [t.strip() for t in trend_str.split(",") if t.strip()]
            if not popular:
                continue
            if popular not in groups:
                groups[popular] = []
            groups[popular].extend(trends)
        for popular, trends in groups.items():
            txtfile.write(f"{popular}:\n")
            for trend in trends:
                txtfile.write(f"  - {trend}\n")
            txtfile.write("\n")
    print(f"Data saved to {txt_path}")
else:
    print("CSV file not found for processing.")

# --- Database connection settings ---
DB_HOST = "37.140.192.23"
DB_PORT = 3306  # or your port
DB_USER = "u2604360_wp766"
DB_PASS = "14rS93p]G["
DB_NAME = "u2604360_wpposter"

TABLE_NAME = "google_trends_data"

# --- Create table ---
def create_table(connection):
    with connection.cursor() as cursor:
        cursor.execute(f"""
            CREATE TABLE IF NOT EXISTS `{TABLE_NAME}` (
                id INT AUTO_INCREMENT PRIMARY KEY,
                popular VARCHAR(255),
                trend VARCHAR(255)
            ) CHARACTER SET utf8mb4
        """)
    connection.commit()

# --- Import data from CSV ---

if os.path.exists(csv_path):
    # Connect to DB
    connection = pymysql.connect(
        host=DB_HOST,
        port=DB_PORT,
        user=DB_USER,
        password=DB_PASS,
        database=DB_NAME,
        charset="utf8mb4"
    )
    create_table(connection)
    with open(csv_path, encoding="utf-8") as csvfile:
        reader = csv.reader(csvfile)
        for row in reader:
            # row: [popular, ... , trend, ...]
            if not row or not row[0]:
                continue
            popular = row[0].strip()
            # If there is a separate column for trend, use the correct index
            # For example, if it is the 4th column:
            trend = row[4].strip() if len(row) > 4 else ""
            with connection.cursor() as cursor:
                cursor.execute(
                    f"INSERT INTO `{TABLE_NAME}` (popular, trend) VALUES (%s, %s)",
                    (popular, trend)
                )
    connection.commit()
    connection.close()
    print(f"Data from {csv_path} inserted into table {TABLE_NAME}")
else:
    print("CSV file not found for database import.")