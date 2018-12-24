# Nexmo Voice Event Debugger

This tool is intended as a diagnostic tool to assist during the development phase of software projects. If you are working with the Nexmo voice applications, you can use this project to give you a simple `event_url` endpoint to send your webhooks to, and inspect what is received.

If you're new to Nexmo, you can sign up here: <https://dashboard.nexmo.com> and learn how to create a voice application over here: <https://developer.nexmo.com/voice/voice-api/overview#getting-started>

> Note that this is intended for development use only. This isn't just a disclaimer: this is a debugging tool and it really, really won't work at scale.

Jump straight to:

* [Installation](#installation)
* [Usage](#usage)
* [Notes](#notes)
* [Contributing](#contributing)

## Installation

This application can be used in a choice of ways:

* Deployed to Heroku with the "deploy to heroku" button - [see docs](#install-with-heroku)
* Deployed to Heroku manually - [see docs](#manual-heroku-install)
* Run locally with [Ngrok](https://ngrok.com/) - [see docs](#run-locally)

Each of these options is covered in its own section below:

### Install with Heroku


### Manual Heroku Install

**Step 0:** Clone this repository to your own machine.

**Step 1:** Create Heroku application

You will need the [Heroku CLI](https://devcenter.heroku.com/articles/heroku-cli) to complete this section.

From the root directory of the cloned project, create the application:

```
heroku create
```

This will show you the application name and URL: copy the _name_ now as we need it in the next step

**Step 2:** Configure AddOns (optional)

> If you want to be able to view the event logs, then run this step to add the `heroku-redis` AddOn to your project. This is free but does require a credit card to be registered on the account. Without Redis and without making further changes (it's an easy project to extend) the application will write to the PHP error_log and you will only be able to view the output by checking your application logs (which might be enough, depending on your needs).

Add the Redis AddOn:

```
heroku addons:create heroku-redis:hobby-dev -a [paste application name here]
```

**Step 3:** Deploy the application

Push the code up to the Heroku application you created:

```
heroku push
```

**Step 4:** Check the installation

Now copy the application URL and visit it: you should see a message about there not being any records to display. That's fine!

When you configure your Nexmo voice application, the `event_url` should be set to `https://wonderful-heroku-app-naming.herokuapp.com/event` (replace as appropriate).

### Run locally

**Step 0:** Clone this repository to your own machine.

This project runs on PHP, it's been tested with PHP 7.1 and later. It can write to your error log, a local file, or Redis (we won't cover how to install Redis in this guide). You will need a current, stable version of [PHP](https://php.net) and [Composer](https://getcomposer.org).

**Step 1:** Install the dependencies

```
composer install
```

**Step 2:** Configure the application

You will need to copy `.env-example` to `.env` and edit appropriately:

* If `REDIS_URL` is set, this application will log incoming events to Redis. Each event becomes an entry in a list called "logs".
* If `LOG_FILE` is set, this application will write one new line to the end of this file for each incoming event.
* If both values are set, both locations will be written to. Check `config.php` to see where the Monolog setup happens and adapt/extend to meet your needs.
* If neither is set, you can leave the `.env` file empty (but it needs to exist) and the application will fall back to writing events to PHP's error log.

**Step 3:** Start the webserver.

From the `public/` directory, run this command to start the built-in webserver:

```
php -S localhost:8080
```

You can change the port number to suit your needs.

**Step 4:** Verify that everything works as expected.

Visit <http://localhost:8080> and you should see a message about not having any data. That's fine, we're ready to go!

**Step 5:** Set up Ngrok to allow Nexmo to access your application

If you're running the code locally, the code won't be available to the wider 'net. Since Nexmo needs to be able to make a web request to this code, we can use [Ngrok](https://ngrok.com) to create a secure tunnel to the outside world. Check the [Ngrok installation instructions](https://ngrok.com/download) if you don't already have it set up (it's easy, I promise).

Once you have the application running, start an ngrok tunnel with a command like this:

```
ngrok http 8080
```

You will see a dashboard including some "Forwarding URL" fields - copy the https one, which will look something like: `https://abcd1234.ngrok.io`. If you make a web request to that endpoint, you will see the request appear in the window where you ran the command.

Also now visit <http://localhost:4040> which is the powerful and excellent dashboard of Ngrok - from here you can see exactly the request that arrived, what your application returned, and replay requests if you want to.

> Note that each time you start Ngrok, you will get a new URL and will need to update your Nexmo application with the new URL. Alternatively, consider paying for an Ngrok account since this allows you to have named tunnels that you can use every time.

When you configure your Nexmo voice application, the `event_url` will be (replace your actual Ngrok tunnel name): `https://abcd1234.ngrok.io/event`

## Usage

When setting up your Nexmo voice application, set the `event_url` to be the URL of where you installed this project, with `/event` appended. F

## Notes

This project uses PHP, Slim Framework and Monolog; these are all very standard components that you can extend, replace or generally should be very maintainable. By default this application writes event information to the error log of the PHP application: support for files and Redis is also included - but with Monolog you can configure any handler you'd like so please do feel free to go ahead and extend this to meet your needs.

## Contributing

We welcome issues, suggestions and pull requests. If there's something you have added for your own use, feel free to share it widely with other users of this project!
