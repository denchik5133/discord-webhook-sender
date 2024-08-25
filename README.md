# Discord Webhook Sender

A PHP class for sending messages to Discord webhooks with cooldown handling and optional embeds. This class helps you manage the rate at which messages are sent to avoid hitting rate limits imposed by Discord webhooks.

## Features

- **Cooldown Handling**: Automatically handles cooldowns between requests to prevent rate limiting.
- **Webhook Validation**: Ensures the provided webhook URL is valid.
- **Message Sending**: Send messages with optional username, avatar URL, and embeds.
- **Error Handling**: Provides error messages if the message fails to send.

## Installation

1. Clone the repository:
    ```bash
    git clone https://github.com/denchik5133/discord-webhook-sender.git
    ```

2. Navigate to the project directory:
    ```bash
    cd discord-webhook-sender
    ```

## Usage

1. Include the `DiscordWebhookSender` class in your PHP script.

2. Instantiate the class with your Discord webhook URL and optional cooldown time.

3. Use the `sendMessage` method to send messages.

### Example

```php
<?php

require_once 'DiscordWebhookSender.php';

try {
    $webhookUrl = 'https://discord.com/api/webhooks/your-webhook-url';

    $embeds = [
        [
            'title' => 'Embed Title',
            'description' => 'This is an example of an embed description.',
            'url' => 'https://example.com',
            'color' => 5814783,
            'footer' => [
                'text' => 'Footer Text',
                'icon_url' => 'https://example.com/footer-icon.png'
            ],
            'timestamp' => date('c'),
            'author' => [
                'name' => 'Author Name',
                'url' => 'https://example.com/author',
                'icon_url' => 'https://example.com/author-icon.png'
            ],
            'fields' => [
                [
                    'name' => 'Field Name 1',
                    'value' => 'Field Value 1',
                    'inline' => true
                ],
                [
                    'name' => 'Field Name 2',
                    'value' => 'Field Value 2',
                    'inline' => true
                ]
            ]
        ]
    ];

    $sender = new DiscordWebhookSender($webhookUrl, 10); // Set the delay time to 10 seconds
    $sender->sendMessage('This is the message content.', 'My Bot', 'https://example.com/avatar.png', $embeds);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
