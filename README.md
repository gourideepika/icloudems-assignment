# About application
This application imports  2,00,000 student fee ledger CSV, stores the source rows in a staging table, extracts due entries, and writes them to financial transaction and financial transaction detail tables.

## Requirements

- PHP 8.2 or newer
- Composer
- MySQL
- A database-backed Laravel queue

## Setup

Install the application and then run the following commands in terminal:

command 1 - composer install

Create .env file and copy .env.example file code to .env file
Create MySQL database named icloudems_assignment and update .env with the database:

.env file - 

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=icloudems_assignment
DB_USERNAME=root
DB_PASSWORD=

Run command in terminal - 

command 1 - php artisan key:generate
command 2 - php artisan migrate

Run the web server and queue worker in separate terminals:

Terminal 1 - php artisan serve

Terminal 2 - php artisan queue:work --tries=3 --timeout=3600

Open http://127.0.0.1:8000 in browser.

Upload csv file and chcek the records. 

NOTE:- 
1. The complete process of uploading, importing and inserting 2 lakh records takes approximately 5–6 minutes.

2. The queue worker must be running in terminal while importing the CSV. (php artisan queue:work --tries=3 --timeout=3600)

3. Refer to Step6_code_submission_file_deepika.docx for the code submission.

4. For creating database run: php artisan migrate

5. An SQL file is also included for reference and can be used to recreate the database structure and tables.

6. Project screenshots are also added for reference inside public/screenshot folder.