<h3>Installation</h3>

1. Clone the Git repository to your local machine:
`git clone https://github.com/Deiv-Dev/weather-forcast.git`

2. Install Docker on your system if you haven't already done so. 
You can use docker-compose from here `https://github.com/aschmelyun/docker-compose-laravel/`

3. Open a terminal or command prompt and navigate to the root directory of the cloned repository.

4. Build the Docker image for the project using the `docker-compose build` command.

5. run database migrations and seed data using the `docker-compose run artisan migrate --seed`

6. start the Docker container for the project using the `docker-compose up` command.

<h3>Usage</h3>

1. Open Postman and send a POST request to `http://localhost/api/clothes-recomendations` with a 
key: city and value: name of a city you want to receive recommendation

2. The application will retrieve the weather information for the next three days and display two clothing recommendations for each day based on the weather conditions.
