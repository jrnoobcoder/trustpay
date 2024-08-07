
# Integration of Stripe with Laravel API

This is a simple project to Integration of Stripe payment using Laravel API. 
## Requirements

Important pre-requisite

```bash
  php 8.1^
  laravel 10.0.x^
  composer 2.5.^
  mysql 
  apache or nginx
```

## Stripe
You have to create account on Stripe to get API keys for testing for more information click on below link

[Stripe Documentation](https://docs.stripe.com/keys)

You have define these keys in Laravel's `.env` file which is present in the root folder

`API Keys: `
```
#public key 
STRIPE_KEY="pk_test_51JvyCkD0V1BjnH0DRk**********************"

#secret key
STRIPE_SECRET="sk_test_51JvyCkD0V1BjnH0**********************"
```
## Environment Variables

To run this project, you will need to add the following environment variables to your `.env` file

**Database Variable**


```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=

```
**SMTP Variable to send emails to reset password**

```
MAIL_MAILER=smtp
MAIL_HOST=smtp-example.com
MAIL_PORT=587
MAIL_USERNAME=example@gmail.com
MAIL_PASSWORD=I3jasRxhOgk***
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=example@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Run Locally

Clone the project

```bash
  git clone https://github.com/jrnoobcoder/trustpay.git
```

Go to the project directory

```bash
  cd trustpay
```

Install dependencies

```bash
  composer update
```

Run migration 
```
php artisan migrate
```

Start the server

```bash
  php srtisan serve
```

Open below url in the browser
```
localhot:8000/
```

## API Reference

#### Get all items

```http
  GET /api/items
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `api_key` | `string` | **Required**. Your API key |

#### Get item

```http
  GET /api/items/${id}
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `id`      | `string` | **Required**. Id of item to fetch |

#### add(num1, num2)

Takes two numbers and returns the sum.

