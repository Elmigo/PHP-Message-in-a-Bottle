# PHP Anonymous Self-Destructing Message
[See Preview](https://shred.elmigo.nl)
Self-hosted Anonymous Self-destructing Message System
## You can read it only once, ever!
Anonymously write a message and share the link. The message can only be read once, ever! After reading the message, it will self-destruct. After a set amount of hours, the message will automatically self destruct.
This script works on its own and requires nothing but the page itself. It can be uploaded anywhere on a server where PHP is enabled, and it has been tested for PHP 8.1 specifically.

## Supports markdown and multi-language
Messages support simple markdown using asterisks for bold text, underscores for italic text, hyperlinks and much more. The script supports multi-language input and English translations are available by default. Just add custom language translations and change the used language in the settings array. Or hook the script into your own website or web-app and let it determine which language to use.

## Full description of this project
Anonymously write a message using simple markdown and share the link with someone. The message will self-destruct when viewed or automatically after 24 hours (or any set amount). No form fields, no contact details, no accounts or signup. Just go to the page, write and share the link. A very simple one-page HTML structure with PHP and Javascript to handle the self-destruction and generate the URL directly into the address bar of your browser. The script supports simple markdown, similar to most instant messaging apps: Surround text by asterisks (*) to make it bold, underscores (_) to make it italic and tildes (~) to make it underlined. Or set your own symbols within the PHP script and add more. Using hashtags (#) you can create titles, similar to how Github handles titles.

### How to install and use (very simple)
1. Open the `index.php` file in your favorite IDE
2. Edit the `$settings` array to add database credentials
3. Edit the `$language` array when needed
4. Upload the `index.php` file to your server
5. Navigate to the page and write a message with formatting
6. Copy the URL generated in your browsers address bar
7. Share the URL with someone who may read your message
8. Do not open your own link, the message self-destructs!

[See Preview](https://shred.elmigo.nl)
