<?php //ssrc/api/

require_once('../cres.php');

$query = DStr($_GET['q'], 'nothing');
switch ($action) {
case 'nothing':
?>
nothing
<?php

default:
?>
unknown query: <?=$query?>
<?php

}