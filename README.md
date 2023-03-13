<h1>Clothes Recommendation App</h1>

This is a web application built using Laravel and MySQL that allows users to get the weather conditions for the next three days and receive two clothing recommendations for each day. Users simply need to enter the name of a city and the application will retrieve the relevant weather information and display the recommendations.

Prerequisites

XAMPP
Git
Composer

Installation

1. Clone the Git repository to your local machine:
git clone [https://github.com/your-username/weather-app.git](https://github.com/Deiv-Dev/weather-forcast.git)

2. Change to the project directory:
cd weather-app

3. Install the required dependencies using Composer:
composer install

4. Start XAMPP and ensure that MySQL is running.

5. Create a new database for the application in MySQL.

6 .Copy the .env.example file to a new file called .env and update the following variables with your database information:

DB_DATABASE=your_database_name

DB_USERNAME=your_database_username

DB_PASSWORD=your_database_password

7. Generate a new application key:
php artisan key:generate

8. Run the database migrations to create the necessary tables:
php artisan migrate

9. Populate the database with seed data:
php artisan db:seed

10.Start the application:
php artisan serve

11. Navigate to http://localhost:8000 in your web browser to use the application.

Usage

Enter the name of a city in the input field and click the "Get Weather" button.
The application will retrieve the weather information for the next three days and display it on the page.
The application will also display two clothing recommendations for each day based on the weather conditions.
