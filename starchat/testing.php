<?php

// TESTING... 123

require "../config.php";
include "starchat.php";

use Starchat\Starchat;

$init = new Starchat($conn);

$query = $init->starchat_sql("SELECT * FROM messages WHERE username=?", "s", "nekobit");

$result = $query->get_result();

while ($row = $result->fetch_assoc()) {
	echo "{$row['message']}\n";
}

?>