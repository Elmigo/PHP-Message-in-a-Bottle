# PHP Anonymous Self-Destructing Message
## Light weight one-page message system
Anonymously write a message using simple markdown and share the link with someone. The message will self-destruct when viewed or automatically after 24 hours (or any set amount). No form fields, no contact details, no accounts or signup. Just go to the page, write and share the link. A very simple one-page HTML structure with PHP and Javascript to handle the self-destruction and generate the URL directly into the address bar of your browser. The script supports simple markdown, similar to most instant messaging apps: Surround text by asterisks (*) to make it bold, underscores (_) to make it italic and tildes (~) to make it underlined. Or set your own symbols within the PHP script and add more. Using hashtags (#) you can create titles, similar to how Github handles titles.

### How to install and use (very simple)
1. Upload the page anywhere you like on your server
2. Edit the file to add DB credentials in the settings array
3. Navigate to the page and write your message
4. Copy the link from the browsers address bar and share it
5. Do not open your own link or the message disappears
