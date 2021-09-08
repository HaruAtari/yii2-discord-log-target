<?php

namespace haruatari\yii2\discordLogTarget;

use yii\base\Exception;
use yii\helpers\VarDumper;
use yii\httpclient\Client;
use yii\log\Logger;
use yii\log\Target;

class DiscordTarget extends Target
{
    /**
     * The url of your webhook
     * @see https://support.discord.com/hc/en-us/articles/228383668-Intro-to-Webhooks
     * @var string
     */
    public $webhookUrl;
    /**
     * A template of a message text. You can use some tokens which will be replace by values:
     * - {timestamp}
     * - {level}
     * - {category}
     * - {message} Raw message text
     * @var string
     */
    public $messageTemplate = "{message}";
    /**
     * A title for each message. Can be a name of your application or something like this
     * Should be a plain text without markdown markup
     * @var string
     */
    public $messageTitle;
    /**
     * If `true` messages will contain only processed $this::$messageTemplate instead of rich text
     * @var bool
     */
    public $usePureView = false;
    /**
     * An absolute url of bot's avatar.
     * Default avatar will be used if null
     * @var ?string
     */
    public $avatarUrl = null;
    /**
     * @var int[]
     */
    protected $levelColours = [
        Logger::LEVEL_ERROR => 15548997,
        Logger::LEVEL_WARNING => 16705372,
        Logger::LEVEL_INFO => 5763719,
    ];
    /**
     * @var int
     */
    protected $defaultLevelColour = 16777215;

    public function init()
    {
        parent::init();

        if ($this->webhookUrl === null) {
            throw new Exception('The $webhookUrl property should by specified');
        }
        if ($this->messageTitle === null) {
            $this->messageTitle = \Yii::$app->id;
        }
    }

    public function export()
    {
        if ($this->usePureView) {
            $content = ["**$this->messageTitle**"];
            foreach ($this->messages as $message) {
                $content[] = $this->formatMessage($message);
            }
            $content = implode("\n", $content);
            $this->sendMessage($content);
        } else {
            $embeds = [];
            foreach ($this->messages as $message) {
                list(, $level, $category, $timestamp) = $message;
                $time = $this->getTime($timestamp);
                $embeds[] = [
                    'title' => $this->messageTitle,
                    'description' => $this->formatMessage($message),
                    'color' => $this->levelColours[$level] ?? $this->defaultLevelColour,
                    'timestamp' => $time,
                    'footer' => [
                        'text' => $category,
                    ],
                ];
            }

            foreach (array_chunk($embeds, 10) as $chunk) {
                $this->sendMessage(null, $chunk);
            }
        }
    }

    protected function getContextMessage()
    {
        return '';
    }

    public function formatMessage($message)
    {
        list($text, $level, $category, $timestamp) = $message;
        $level = Logger::getLevelName($level);

        if (!is_string($text)) {
            // exceptions may not be serializable if in the call stack somewhere is a Closure
            if ($text instanceof \Throwable || $text instanceof \Exception) {
                $exceptionMessages = [];
                while ($text !== null) {
                    $exceptionMessages[] = "- {$text->getMessage()}";
                    $text = $text->getPrevious();
                }
                $text = implode("\n", $exceptionMessages);
            } else {
                $text = VarDumper::export($text);
            }
        }

        return str_replace(
            ['{timestamp}', '{level}', '{category}', '{message}'],
            [$this->getTime($timestamp), $level, $category, $text]
            , $this->messageTemplate
        );
    }

    /**
     * @param ?string $content
     * @param array $embeds
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    private function sendMessage($content = null, $embeds = [])
    {
        $data = [];
        if ($this->avatarUrl !== null) {
            $data['avatar_url'] = $this->avatarUrl;
        }
        if ($content !== null) {
            $data['content'] = $content;
        }
        if ($embeds !== []) {
            $data['embeds'] = $embeds;
        }

        (new Client())
            ->createRequest()
            ->setMethod('POST')
            ->setFormat('json')
            ->setUrl($this->webhookUrl)
            ->setData($data)
            ->send();
    }
}
