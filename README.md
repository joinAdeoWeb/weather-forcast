<<<<<<< HEAD
# docker-compose-laravel
A pretty simplified Docker Compose workflow that sets up a LEMP network of containers for local Laravel development. You can view the full article that inspired this repo [here](https://dev.to/aschmelyun/the-beauty-of-docker-for-local-laravel-development-13c0).

## Usage

To get started, make sure you have [Docker installed](https://docs.docker.com/docker-for-mac/install/) on your system, and then clone this repository.

Next, navigate in your terminal to the directory you cloned this, and spin up the containers for the web server by running `docker-compose up -d --build app`.

After that completes, follow the steps from the [src/README.md](src/README.md) file to get your Laravel project added in (or create a new blank one).

**Note**: Your MySQL database host name should be `mysql`, **not** `localhost`. The username and database should both be `homestead` with a password of `secret`. 

Bringing up the Docker Compose network with `app` instead of just using `up`, ensures that only our site's containers are brought up at the start, instead of all of the command containers as well. The following are built for our web server, with their exposed ports detailed:

- **nginx** - `:80`
- **mysql** - `:3306`
- **php** - `:9000`
- **redis** - `:6379`
- **mailhog** - `:8025` 

Three additional containers are included that handle Composer, NPM, and Artisan commands *without* having to have these platforms installed on your local computer. Use the following command examples from your project root, modifying them to fit your particular use case.

- `docker-compose run --rm composer update`
- `docker-compose run --rm npm run dev`
- `docker-compose run --rm artisan migrate`

## Permissions Issues

If you encounter any issues with filesystem permissions while visiting your application or running a container command, try completing one of the sets of steps below.

**If you are using your server or local environment as the root user:**

- Bring any container(s) down with `docker-compose down`
- Replace any instance of `php.dockerfile` in the docker-compose.yml file with `php.root.dockerfile`
- Re-build the containers by running `docker-compose build --no-cache`

**If you are using your server or local environment as a user that is not root:**

- Bring any container(s) down with `docker-compose down`
- In your terminal, run `export UID=$(id -u)` and then `export GID=$(id -g)`
- If you see any errors about readonly variables from the above step, you can ignore them and continue
- Re-build the containers by running `docker-compose build --no-cache`

Then, either bring back up your container network or re-run the command you were trying before, and see if that fixes it.

## Persistent MySQL Storage

By default, whenever you bring down the Docker network, your MySQL data will be removed after the containers are destroyed. If you would like to have persistent data that remains after bringing containers down and back up, do the following:

1. Create a `mysql` folder in the project root, alongside the `nginx` and `src` folders.
2. Under the mysql service in your `docker-compose.yml` file, add the following lines:

```
volumes:
  - ./mysql:/var/lib/mysql
```

## Usage in Production

While I originally created this template for local development, it's robust enough to be used in basic Laravel application deployments. The biggest recommendation would be to ensure that HTTPS is enabled by making additions to the `nginx/default.conf` file and utilizing something like [Let's Encrypt](https://hub.docker.com/r/linuxserver/letsencrypt) to produce an SSL certificate.

## Compiling Assets

This configuration should be able to compile assets with both [laravel mix](https://laravel-mix.com/) and [vite](https://vitejs.dev/). In order to get started, you first need to add ` --host 0.0.0.0` after the end of your relevant dev command in `package.json`. So for example, with a Laravel project using Vite, you should see:

```json
"scripts": {
  "dev": "vite --host 0.0.0.0",
  "build": "vite build"
},
```

Then, run the following commands to install your dependencies and start the dev server:

- `docker-compose run --rm npm install`
- `docker-compose run --rm --service-ports npm run dev`

After that, you should be able to use `@vite` directives to enable hot-module reloading on your local Laravel application.

Want to build for production? Simply run `docker-compose run --rm npm run build`.

## MailHog

The current version of Laravel (9 as of today) uses MailHog as the default application for testing email sending and general SMTP work during local development. Using the provided Docker Hub image, getting an instance set up and ready is simple and straight-forward. The service is included in the `docker-compose.yml` file, and spins up alongside the webserver and database services.

To see the dashboard and view any emails coming through the system, visit [localhost:8025](http://localhost:8025) after running `docker-compose up -d site`.
=======
<h1>Clothes Recommendation App</h1>

This is a web application built using Laravel and MySQL that allows users to get the weather conditions for the next three days and receive two clothing recommendations for each day. Users simply need to enter the name of a city and the application will retrieve the relevant weather information and display the recommendations.

<h3>Preview</h3>

https://weather.deivdev.com/

<h3>Requirements</h3>

XAMPP 

Git

Composer

<h3>Installation</h3>

1. Clone the Git repository to your local machine:
git clone [https://github.com/your-username/weather-app.git](https://github.com/Deiv-Dev/library.git)

2. Change to the project directory:
cd weather-app

3. Install the required dependencies using Composer:
composer install

4. Start XAMPP and ensure that MySQL is running.

5. Create a new database for the application in MySQL.

6. Copy the .env.example file to a new file called .env and update the following variables with your database information:

DB_DATABASE=your_database_name <br>
DB_USERNAME=your_database_username <br>
DB_PASSWORD=your_database_password

7. Generate a new application key:
php artisan key:generate

8. Run the database migrations to create the necessary tables:
php artisan migrate

9. Populate the database with seed data:
php artisan db:seed

10. Start the application:
php artisan serve

11. Navigate to http://127.0.0.1:8000 in your web browser to use the application.

<h3>Usage</h3>

Enter the name of a city in the input field and click the "Get Weather" button.
The application will retrieve the weather information for the next three days and display it on the page.
The application will also display two clothing recommendations for each day based on the weather conditions.
>>>>>>> origin/main
