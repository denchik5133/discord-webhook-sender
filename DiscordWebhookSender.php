<?php

class DiscordWebhookSender {
    private $webhookUrl;
    private $cooldownTime; // Time delay between requests in seconds
    private $lastSendTimeFile; // File for storing the time of the last message sent

    public function __construct($webhookUrl, $cooldownTime = 5) {
        $this->webhookUrl = $webhookUrl;
        $this->cooldownTime = $cooldownTime;
        $this->lastSendTimeFile = __DIR__ . '/last_send_time.txt';
    }

    private function applyCooldown() {
        if (file_exists($this->lastSendTimeFile)) {
            $lastSendTime = (int)file_get_contents($this->lastSendTimeFile);
            $currentTime = time();

            // Calculate the remaining time until the next allowed request
            $timeSinceLastSend = $currentTime - $lastSendTime;
            if ($timeSinceLastSend < $this->cooldownTime) {
                $sleepTime = $this->cooldownTime - $timeSinceLastSend;
                echo "Cooling down. Sleeping for $sleepTime seconds.\n";
                sleep($sleepTime);
            }
        }

        // Update the time of the last sent message
        file_put_contents($this->lastSendTimeFile, time());
    }

    public function sendMessage($content, $username = null, $avatarUrl = null, $embeds = []) {
        // Applying a delay between requests
        $this->applyCooldown();

        // Checking Webhook URLs
        if (empty($this->webhookUrl) || !filter_var($this->webhookUrl, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid Webhook URL');
        }

        $payload = [
            'content' => $content,
            'username' => $username,
            'avatar_url' => $avatarUrl,
            'embeds' => $embeds
        ];

        $payload = json_encode(array_filter($payload, function($value) {
            return $value !== null;
        }));

        $ch = curl_init($this->webhookUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload)
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        // Disabling SSL validation for debugging
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode != 204) {
            // Checking response status and error handling
            echo "Failed to send message, HTTP status code: $httpCode\n";
            echo "cURL Error: $error\n";
            return false;
        } else {
            echo 'Message sent successfully!';
            return true;
        }
    }
}

// Example of use:
try {
    $webhookUrl = 'https://discord.com/api/webhooks/964193653595705385/DlIBCifglI1N34p0-Zv1T0CtUo2IbqYQgj0v7BKArtNeuYbWTX9KiyZrkqkNvj7rVohv';

    // Creating Embeds
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
    // Exception handling and error message output
    echo 'Error: ' . $e->getMessage();
}