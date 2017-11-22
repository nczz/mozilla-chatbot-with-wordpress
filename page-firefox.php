<?php
$t = isset($_GET['t'])?$_GET['t']:1;
header("Location: https://mozilla.undo.im/type{$t}/?t=".time());
