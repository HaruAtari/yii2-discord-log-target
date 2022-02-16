# Discord log target for Yii2

It sends log messages to your discord channel via webhook.

[![Packagist Version](https://img.shields.io/packagist/v/haruatari/yii2-discord-log-target?style=for-the-badge)](https://packagist.org/packages/haruatari/yii2-discord-log-target)
[![Total Downloads](https://img.shields.io/packagist/dt/haruatari/yii2-discord-log-target?style=for-the-badge)](https://packagist.org/packages/haruatari/yii2-discord-log-target)

## Installation

Install via Composer:

```bash
composer require haruatari/yii2-discord-log-target
```

or add

```bash
"haruatari/yii2-discord-log-target" : "~1.0"
```

to the `require` section of your `composer.json` file.


## Usage

Ad it into your Yii2 config file:

```php
// ...
'components' => [
    'log' => [
        'targets' => [
            [
                '__class' => \haruatari\yii2\discordLogTarget\DiscordTarget::class,
                'webhookUrl' => "your webhook's url",
                'messageTitle' => 'App name', // Application ID will be used if not specified
                'avatarUrl' => 'https://your-avatar-image-url', // The image will be used as discord webhook avatar if specified               
            ],
        ],
    ],
]
// ...
```

You also can use standard target parameters like `categories`, `levels` etc.
