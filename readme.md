# Crypto Trading Dashboard
Delivers an overview of your cryptotrading activities

## installation


1. Install PHP dependencies:
<code>composer Install</code>

2. Copy ".env-example" to ".env" and fill in all missing values

3. Run Database Migrations:
<code>php artisan migrate</code>

4. Install js dependencies
<code>npm install</code>
5. Build js and css Assets
<code>npm run</code>

## Add another crypto trading plattform

1. Create a class which implements the DriverInterface App\Driver\DriverInterface.php. See Bitcoinde and Poloniex implementations as examples.
2. Add the plattform credentials in config/app.php in the "driver" section
3. Add Driver to "api.active" config (in ".env" or "config.app.php")

You may test your driver class by creating a test which extends the AbstractDriverTest class under "tests/Integration"
