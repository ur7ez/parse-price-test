# OLX Price Monitor

This project monitors price changes on OLX advertisements and sends email notifications when a price change is detected. It is built using this stack:
[Laravel](https://laravel.com/) v.11 with [Blade templates](https://laravel.com/docs/11.x/blade#main-content), [Tailwindcss](https://tailwindcss.com/) and [AlpineJS](https://alpinejs.dev/).

## Requirements

- PHP 8.1+
- Composer
- Laravel 11
- MySQL or another supported database
- Node.js (for frontend assets)

## Installation

1. Download the project (or clone using GIT):
    ```bash
    git clone https://github.com/ur7ez/parse-price-test.git
    ```
2. Copy `.env.example` into `.env` (```cp .env.example .env```) and edit `.env` file to configure your environment:
   * set up DB connection. For DB do not use `sqlite` - queue worker will not work with it. Use `DB_CONNECTION=mysql` and add other DB credentials.
   * configure your mail settings to enable email notifications (I used [Mailtrap](https://mailtrap.io/) free service for testing)
3. Navigate to the project's root directory using terminal and run
 `composer install`
4. Set the encryption key by executing `php artisan key:generate --ansi`
5. Run DB migrations `php artisan migrate`
6. Start local server by executing `php artisan serve`
7. To install frontend dependencies, open new terminal and navigate to the project root directory
8. Run `npm install`
9. Run `npm run dev` to start vite server for Laravel frontend

## Set up Queue Worker

Email notifications are being sent with queue (`ParseOlxPrice` Command class queues emails) to optimize performance, so you need to set up queue worker. 
* If you prefer Redis for queueing, install it and update `.env` with `QUEUE_CONNECTION=redis` 
* Use Laravel Queue Worker to start listening for a queued jobs:
    
```php artisan queue:work```

## Set up Scheduler

If you want to run the scheduled tasks to monitor prices periodically, you must run the Laravel scheduler.
To run a scheduled worker locally, in new terninal window run:

```php artisan schedule:work```

This command will run in the foreground and invoke the scheduler every minute until you terminate the command.

 To run scheduled tasks once (without worker), use:

`php artisan schedule:run`

Or you may set up cron job manually like this:
```bash
 while true; do php artisan schedule:run; sleep 60; done
 ```
## Testing the System

To test the OLX price monitoring manually, you can run the command:

`php artisan schedule:test --name="parse:olx-price --method=http"`

If everything is set up correctly, the prices will be monitored, and notifications will be sent when a price change is detected.

## Usage

### Subscribing for Price Notifications
* To subscribe to price notifications, a user can submit one or many OLX ad URLs at once through the frontend (form).
* To verify new subscription email, user will receive email notification with verification link. 
* Only after email is verified, its subscribed adv URLs will be allowed for price monitoring service.
* The service will automatically monitor the price for newly added subscriptions and notify the user if the price changes.

### Monitoring Prices

* The system will run monitoring the prices every hour (or as configured in routes/console.php). 
* Period to trigger new price check for each adv URL is set to 2 hours by config parameter `max_url_age` in [config/parser.php](config/parser.php)
* You can manually trigger the price monitoring by running `php artisan parse:olx-price`. By default, it uses http as method option (`--method=http`) but also `--method=selenium` option possible (slow!).

## Notifications

The application will send email notifications when the price of a monitored ad changes. The email contains the old and new prices along with the URL of the ad.



## <ins>Original Task Description</ins>

Implement a service that allows to monitor the price change of an advert on OLX:
1. The service must provide HTTP method for subscribing to price changes. Method receives a link to the ad and an email address to which to send a message.
2. After successful subscription, the service must monitor the price of the ad and send a message to the specified email address (in case of a price change).
3. If several users have subscribed to the same ad, the service should not check the ad price again.

### The results should include:
- A diagram of the service and its brief description
- A link to the code repository
- Subscription to price changes
- Tracking price changes
- Sending a message to the subscriber's e-mail
- Programming language - PHP

If several implementation options appeared during the task, describe the advantages and disadvantages of each of them. Indicate why you chose one or another option.

_To get the price of an ad, you can:_
- parse the ad web page
- independently analyze the traffic on mobile applications or a mobile version of website and find out what API is uded there to get information about the ad

### **Complications:**
- Implement a full-fledged service that solves the task (the service must run in a Docker container).
- Write tests (try to achieve 70% coverage or more).
- User email confirmation.
