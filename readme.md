# Crypto Trading Dashboard

Gives an overview of your crypto trading activities

## Installation

1. Install PHP dependencies with `composer Install`

2. Copy ".env.example" to ".env" and fill in all missing values.

    If you want to use SQLite, set `DB_CONNECTION=sqlite` and set `DB_DATABASE` to a local file path, e.g., `db.sqlite`. After that, run `touch db.sqlite`.

3. Run `php artisan key:generate` to populate the `APP_KEY`

4. Run Database Migrations: `php artisan migrate`

5. Install js dependencies with `npm install` (or `yarn install`)

6. Build js and css assets: `npm run dev`

## Start the server

1. Run `php artisan serve` in one terminal to start the PHP development server
2. Run `npm run watch` in another terminal to automatically update the frontend assets when files are changed.

## Add another crypto trading plattform

1. Create a class which implements the `DriverInterface` from `App\Driver\DriverInterface.php`. See `Bitcoinde` and `Poloniex` implementations as examples.
2. Add the plattform credentials in `config/app.php` in the "driver" section
3. Add Driver to `api.active` config (in `.env` or `config.app.php`)

You may test your driver class by creating a test which extends the `AbstractDriverTest` class under `tests/Integration`.
