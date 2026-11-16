<?php
/* Omtankens Rum - tar emot bokningsformularet och mejlar det vidare.
   Ingen tredjepartstjanst inblandad: uppgifterna gar fran Loopias server
   rakt till Marias inkorg och sparas inte pa webbplatsen.

   ANDRA HAR OM DET BEHOVS
   ----------------------- */
$MOTTAGARE = 'omtankensrum@gmail.com';   // dit forfragningarna skickas
$AVSANDARE = 'webb@omtankensrum.se';     // maste ligga pa den egna doman
/* ----------------------------------------------------------------- */

function falt($namn) {
    $v = isset($_POST[$namn]) ? trim((string) $_POST[$namn]) : '';
    return mb_substr($v, 0, 2000);
}
function rad($v) {           // tar bort radbrytningar ur rubrikrader
    return trim(str_replace(array("\r", "\n", "%0a", "%0d"), ' ', $v));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: boka.html', true, 303);
    exit;
}

/* Spamfalla: falt som ar dolt for manniskor men fylls i av robotar. */
if (falt('webbplats') !== '') {
    header('Location: tack.html', true, 303);
    exit;
}

$namn       = falt('namn');
$epost      = falt('epost');
$telefon    = falt('telefon');
$session    = falt('session');
$tid        = falt('tid');
$meddelande = falt('meddelande');
$samtycke   = falt('samtycke');

$fel = array();
if ($namn === '')                                        { $fel[] = 'Skriv ditt namn.'; }
if (!filter_var($epost, FILTER_VALIDATE_EMAIL))          { $fel[] = 'Kontrollera e-postadressen.'; }
if ($session === '')                                     { $fel[] = 'Välj vilken session du vill boka.'; }
if ($samtycke !== 'ja')                                  { $fel[] = 'Kryssa i rutan om hur uppgifterna sparas.'; }

if (!$fel) {
    $amne = 'Bokningsförfrågan från ' . rad($namn);
    $text = "Ny bokningsförfrågan från omtankensrum.se\n\n"
          . "Namn:       $namn\n"
          . "E-post:     $epost\n"
          . "Telefon:    " . ($telefon !== '' ? $telefon : '-') . "\n"
          . "Session:    $session\n"
          . "Önskad tid: " . ($tid !== '' ? $tid : '-') . "\n\n"
          . "Meddelande:\n" . ($meddelande !== '' ? $meddelande : '-') . "\n\n"
          . "-- \nSkickad " . date('Y-m-d H:i') . " via formuläret på omtankensrum.se\n";

    $rubriker = "From: Omtankens Rum <$AVSANDARE>\r\n"
              . 'Reply-To: ' . rad($namn) . ' <' . rad($epost) . ">\r\n"
              . "Content-Type: text/plain; charset=UTF-8\r\n"
              . "MIME-Version: 1.0\r\n";

    $skickat = @mail($MOTTAGARE, '=?UTF-8?B?' . base64_encode($amne) . '?=',
                     $text, $rubriker, '-f' . $AVSANDARE);

    if ($skickat) {
        header('Location: tack.html', true, 303);
        exit;
    }
    $fel[] = 'Meddelandet kunde inte skickas just nu. Ring eller sms:a 073-610 80 74 '
           . 'så löser vi det.';
}

/* Nagot blev fel - visa en sida i samma formgivning. */
http_response_code(422);
?><!doctype html>
<html lang="sv">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kontrollera formuläret — Omtankens Rum</title>
<meta name="robots" content="noindex">
<link rel="icon" href="bilder/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="css/stil.css">
</head>
<body>
<header class="sidhuvud">
  <div class="omslut sidhuvud__inre">
    <a class="markesnamn" href="index.html">
      <img src="bilder/ikon.svg" alt="" width="38" height="38">
      <span>Omtankens Rum</span>
    </a>
  </div>
</header>
<main class="omslut sektion" style="max-width:720px">
  <h1>Nästan klart</h1>
  <div class="meddelande meddelande--fel" style="margin-top:28px">
    <p style="margin:0 0 10px"><strong>Det här behöver rättas:</strong></p>
    <ul style="margin:0;padding-left:20px">
      <?php foreach ($fel as $f) { echo '<li>' . htmlspecialchars($f, ENT_QUOTES, 'UTF-8') . '</li>'; } ?>
    </ul>
  </div>
  <p>Gå tillbaka och fyll i det som saknas, så skickar vi iväg förfrågan. Det du redan skrivit
     finns kvar om du använder webbläsarens bakåtknapp.</p>
  <p style="margin-top:28px"><a class="knapp" href="boka.html">Tillbaka till formuläret</a></p>
</main>
</body>
</html>
