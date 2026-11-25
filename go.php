<?php
// go.php?proj=02&select=101   read-only view of cable 02-101 (no login)
// The Edit button opens the existing cableEdit.phtml, which asks for the login.

$proj = $_GET['proj'] ?? '';
$sel  = $_GET['select'] ?? '';
if (!preg_match('/^\d{1,2}\z/', $proj) || !preg_match('/^\d{1,3}\z/', $sel)) {
    http_response_code(400);
    exit('Usage: go.php?proj=02&select=101');
}
$projN = (int) $proj;
$selN  = (int) $sel;
$id    = $projN * 1000 + $selN;          // same numbering the database uses

// >>> Put here the same include that cableBrowser-ro.phtml uses (it sets $sock) <<<
include("REPLACE_WITH_THE_INCLUDE_FROM_cableBrowser-ro.phtml");

$result = mysqli_query($sock,
    "SELECT type, con1, equip1, loc1, con2, equip2, loc2, note, userid, time
     FROM cables WHERE nr = $id");
$row = $result ? mysqli_fetch_row($result) : false;
if (!$row) {
    http_response_code(404);
    exit('Cable not found.');
}
$row = array_map(fn($v) => htmlspecialchars((string) $v, ENT_QUOTES), $row);

$labels = ['Type', 'Connector-1', 'Equipment-1', 'Location-1',
           'Connector-2', 'Equipment-2', 'Location-2', 'Note'];
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Cable <?= sprintf('%02d-%03d', $projN, $selN) ?></title>
  <style>
    body { background: #C0C0C0; font-family: serif; }
    input[type=text] { width: 60em; max-width: 90%; background: #E8E8E8; }
    td { padding: 2px 6px; }
  </style>
</head>
<body>
  <form action="cableEdit.phtml" method="post">
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="submit" value="Edit">
  </form>

  <h3>Cable <?= sprintf('%02d-%03d', $projN, $selN) ?></h3>
  <table>
  <?php foreach ($labels as $i => $label): ?>
    <tr><td><?= $label ?>:</td>
        <td><input type="text" value="<?= $row[$i] ?>" readonly></td></tr>
  <?php endforeach; ?>
  </table>
  <b>Last changed by: <?= $row[8] ?> on <?= $row[9] ?></b>
</body>
</html>
