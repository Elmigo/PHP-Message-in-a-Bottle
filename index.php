<?php
  $settings = [
    "expiry_hours"      => 24, 
    "link_delay"        => 250, 
    "language_code"     => 'en', 
    "db_credentials"    => [
      'hostname'          => 'HOSTNAME', 
      'username'          => 'USERNAME', 
      'password'          => 'PASSWORD', 
      'database'          => 'DATABASE'
    ],
    "bot_names"         => [
      'Googlebot', 'Bingbot', 'DuckDuckBot', 'Slurp', 'Baiduspider', 'YandexBot', 'Sogou', 'Exabot', 'Facebot', 'ia_archiver', 'facebookexternalhit', 'Twitterbot', 'Pinterestbot', 'LinkedInBot', 'Discordbot', 'WhatsApp', 'TelegramBot', 'Applebot', 'MJ12bot', 'AhrefsBot', 'SemrushBot', 'DotBot', 'Cliqzbot', 'SeznamBot', 'CoccocBot', 'Gigabot', 'BLEXBot', 'rogerbot', 'Pingdom', 'UptimeRobot', 'GTMetrix', 'Screaming Frog', 'Sitebulb', 'Netcraft', 'WebPageTest', 'Varnish', 'Cloudflare', 'curl', 'Wget', 'Python-urllib', 'PHP', 'Java', 'libwww-perl', 'wget', 'Go-http-client', 'Apache-HttpClient', 'Scrapy', 'Scraper'
    ]
  ];

  $language = [
    'en'  => [
      'text_bot_block'          => 'Bots are not allowed to view messages.',
      'text_message_expired'    => 'This message no longer exists or has expired. Messages will self-destruct on sight, or automatically after ',
      'text_hours'              => ' hours.',
      'text_page_title'         => 'Anonymous Self-Destructing Message',
      'text_write_message'      => 'Write a message',
      'text_instruction'        => 'Copy the link of this page to share your message. This message self-destructs when viewed or after ',
      'text_markdown_examples'  => '# Heading 1<br>## Heading 2<br>### Heading 3<br>#### Heading 4<br>##### Heading 5<br>###### Heading 6<br><br>*<b>bold text</b>*<br>_<i>italic text</i>_<br>~<s>crossed text</s>~<br>=<u>underlined text</u>=<br>`<code>code line</code>`<br><br>* bullet list item<br>- bullet list item<br>1. ordered list item<br>2. ordered list item<br><br>[<a href="#">link text</a>](website_url)<br>--- horizontal line<br>___ horizontal line',
      'text_expand'             => 'Formatting info',
      'text_collapse'           => 'Hide info'
    ]
  ];

  function simpleMarkdown($text) {
    $safe = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    $lines = preg_split("/\r\n|\n|\r/", $safe);

    $result = [];
    $inList = false;
    $listType = '';

    foreach ($lines as $line) {
      $line = trim($line);
      if ($line === '') {
        if ($inList) {
          $result[] = "</$listType>";
          $inList = false;
        }
        continue;
      }

      if ($line === '---' || $line === '___') {
        if ($inList) {
          $result[] = "</$listType>";
          $inList = false;
        }
        $result[] = "<hr>";
        continue;
      }

      if (preg_match('/^(\-|\*)\s+(.+)$/', $line, $matches)) {
        if (!$inList || $listType !== 'ul') {
          if ($inList) $result[] = "</$listType>";
          $result[] = "<ul>";
          $inList = true;
          $listType = 'ul';
        }
        $result[] = "<li>{$matches[2]}</li>";
        continue;
      }

      if (preg_match('/^\d+\.\s+(.+)$/', $line, $matches)) {
        if (!$inList || $listType !== 'ol') {
          if ($inList) $result[] = "</$listType>";
          $result[] = "<ol>";
          $inList = true;
          $listType = 'ol';
        }
        $result[] = "<li>{$matches[1]}</li>";
        continue;
      }

      $isHeading = false;
      for ($i = 6; $i >= 1; $i--) {
        if (preg_match('/^' . str_repeat('#', $i) . ' (.+)$/', $line, $matches)) {
          if ($inList) { $result[] = "</$listType>"; $inList = false; }
          $result[] = '<h' . $i . '>' . $matches[1] . '</h' . $i . '>';
          $isHeading = true;
          break;
        }
      }

      if (!$isHeading) {
        if ($inList) { $result[] = "</$listType>"; $inList = false; }
        $result[] = '<p>' . nl2br($line) . '</p>';
      }
    }

    if ($inList) $result[] = "</$listType>";

    $output = implode("\n", $result);

    $patterns = [
      '/(?<!\w)_(.*?)_(?!\w)/s'   => '<i>$1</i>', 
      '/(?<!\w)\*(.*?)\*(?!\w)/s' => '<b>$1</b>', 
      '/(?<!\w)~(.*?)~(?!\w)/s'   => '<del>$1</del>',
      '/(?<!\w)=(.*?)=(?!\w)/s'   => '<u>$1</u>',
      '/`(.*?)`/s'                 => '<code>$1</code>',
      '/\[(.*?)\]\((.*?)\)/s'      => '<a href="$2">$1</a>'
    ];

    foreach ($patterns as $regex => $replace) {
      $output = preg_replace($regex, $replace, $output);
    }

    return $output;
  }

  $mysqli = new mysqli(
    $settings['db_credentials']['hostname'], 
    $settings['db_credentials']['username'], 
    $settings['db_credentials']['password'], 
    $settings['db_credentials']['database']
  );

  if ($mysqli->connect_errno) {
      die("DB error: " . $mysqli->connect_error);
  }

  $mysqli->set_charset("utf8mb4");
  $mysqli->query("DELETE FROM messages WHERE `date` < (NOW() - INTERVAL " . $settings['expiry_hours'] . " HOUR)");

  if (isset($_GET['t']) && $_SERVER['REQUEST_METHOD'] === 'GET') {

    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    foreach ($settings['bot_names'] as $bot) {
        if (stripos($userAgent, $bot) !== false) {
            http_response_code(403);
            exit($language[$settings['language_code']]['text_bot_block']);
        }
    }

    $token = $_GET['t'];

    $stmt = $mysqli->prepare("
      SELECT text, date FROM messages 
      WHERE token = ? AND `date` > (NOW() - INTERVAL " . $settings['expiry_hours'] . " HOUR)
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($text, $date);

    if ($stmt->num_rows > 0 && $stmt->fetch()) {
      $stmt->close();
      $del = $mysqli->prepare("DELETE FROM messages WHERE token = ?");
      $del->bind_param("s", $token);
      $del->execute();

      $message = simpleMarkdown($text);
    } else {
      $message = $language[$settings['language_code']]['text_message_expired'] . $settings['expiry_hours'] . $language[$settings['language_code']]['text_hours'];
    }
  } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $message = str_replace(["\r\n", "\r", "\n"], "\n", trim($data['message'] ?? ''));
    $token = $data['token'] ?? null;
    $date = date("Y-m-d H:i:s");

    if ($message !== '') {
      if ($token) {
        $stmt = $mysqli->prepare("UPDATE messages SET `text` = ?, `date` = ? WHERE token = ?");
        $stmt->bind_param("sss", $message, $date, $token);
        $stmt->execute();
      } else {
        $token = bin2hex(random_bytes(16));
        $stmt = $mysqli->prepare("INSERT INTO messages (`text`, `token`, `date`) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $message, $token, $date);
        $stmt->execute();
      }

      header("Content-Type: application/json");
      echo json_encode(["link" => "https://shred.elmigo.nl/?t=$token", "token" => $token]);
      exit;
    }
  }
?>

<!doctype html>
<html lang="nl">
<head>
  <meta charset="utf-8">
  <title><?php echo $language[$settings['language_code']]['text_page_title']; ?></title>
  <?php if (isset($_GET['t'])): ?>
    <meta name="robots" content="noindex, nofollow">
  <?php endif; ?>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #efefef;
      width: 100%;
      margin: 0;
    }
    a {
      text-decoration: none;
    }
    p {
      width: calc(100% - 3rem);
      font-size: 1rem;
      margin: 1.2rem;
    }
    #editor {
      width: calc(100% - 3.8rem);
      background-color: #fff;
      border-radius: 8px;
      min-height: 128px;
      padding: 0.7rem;
      margin: 1.2rem;
    }
    #editor:empty:before {
      content: attr(placeholder);
      pointer-events: none;
      color: #aaa;
    }
    #editor:focus-visible {
        outline: none;
    }
    #message {
      width: calc(100% - 3.8rem);
      background-color: #fff;
      border-radius: 8px;
      min-height: 128px;
      padding: 0.7rem;
      margin: 1.2rem;
    }
    #message *:not(li), #message *:not(p) {
      margin-right: 0;
      margin-left: 0;
      padding: 0;
    }
    #message p {
      font-size: 1rem;
      margin: 0;
    }
    #message li {
      margin-left: 1.2rem;
      font-size: 1rem;
    }
    .instruction {
      margin-right: 1.5rem;
      margin-left: 1.5rem;
      font-size: 0.85rem;
      color: #aaa;
    }
  </style>
</head>
<body>
  <?php if (isset($_GET['t']) && $_SERVER['REQUEST_METHOD'] === 'GET'): ?>
    <div id="message"><?php echo $message; ?></div>
    <p><a href="https://shred.elmigo.nl/"><?php echo $language[$settings['language_code']]['text_write_message']; ?></a></p>
  <?php else: ?>
    <p id="editor" contenteditable="true" placeholder="Write a message"></p>
    <p class="instruction">
      <?php echo $language[$settings['language_code']]['text_instruction'] . $settings['expiry_hours'] . $language[$settings['language_code']]['text_hours']; ?><br><br>
      <a href="#" id="toggleExtra"><?php echo $language[$settings['language_code']]['text_expand']; ?></a><br><br>
      <span class="extra" style="display:none;"><?php echo $language[$settings['language_code']]['text_markdown_examples']; ?></span>
    </p>

    <script>
      const editor = document.getElementById("editor");
      let saveTimeout = null;
      let currentToken = null;

      async function saveMessage() {
        const text = editor.innerText.trim();
        if (text.length === 0) return null;

        try {
          const res = await fetch(window.location.pathname, { 
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ message: text, token: currentToken })
          });

          const data = await res.json();
          currentToken = data.token;

          const newUrl = "?t=" + currentToken;
          history.replaceState({token: currentToken}, '', newUrl);

          return newUrl;
        } catch (err) {
          console.error("Save failed:", err);
          return null;
        }
      }

      editor.addEventListener("input", function() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(saveMessage, <?php echo $settings['link_delay']; ?>);
      });
      
      document.getElementById('toggleExtra').addEventListener('click', function(e) {
        e.preventDefault();
        const extra = document.querySelector('.extra');
        if (extra.style.display === 'none' || extra.style.display === '') {
          extra.style.display = 'inline';
          this.textContent = '<?php echo $language[$settings['language_code']]['text_collapse']; ?>';
        } else {
          extra.style.display = 'none';
          this.textContent = '<?php echo $language[$settings['language_code']]['text_expand']; ?>';
        }
      });
    </script>
  <?php endif; ?>
</body>
</html>