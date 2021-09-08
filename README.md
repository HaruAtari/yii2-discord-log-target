# Discord log target for Yii2

It sends log messages to your discord channel via webhook.

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
        [
            '__class' => \haruatari\yii2\discordLogTarget\DiscordTarget::class,
            'webhookUrl' => "your webhook's url",
        ],
    ]
]
// ...
```

You also can use standard target parameters like `categories`, `levels` etc.
