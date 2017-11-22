<!DOCTYPE HTML>
<?php
// $rand = array("3e427d", "37cddb", "72a671", "d6e696", "37420c");
// $bingo = $rand[time() % count($rand)];
// $imgurl = "https://dummyimage.com/400x400/{$bingo}/fff.jpg&text=" . $_GET['t'];
$rand = array("01.jpg", "02.jpg", "03.jpg", "04.jpg", "05.jpg");
$bingo = $rand[time() % count($rand)];
$imgurl = get_template_directory_uri() . "/type7/" . $bingo;
$description = "趕快下載Firefox，讓你的心智與網路一樣自由開闊。";
$page_title = "Firefox Quantum 量子大躍進";
?>
<html>
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?php echo $page_title; ?></title>
<meta name="description" content="<?php echo $description; ?>" />
<meta property="og:site_name" content="<?php echo $page_title; ?>" />
<meta property="og:type" content="article" />
<meta property="og:url" content="<?php echo curPageURL(); ?>" />
<meta property="og:title" content="<?php echo $page_title; ?>" />
<meta property="og:description" content="<?php echo $description; ?>" />
<meta property="og:image:type" content="image/jpeg" />
<meta property="og:image:width" content="400" />
<meta property="og:image:height" content="400" />
<meta property="og:image" content="<?php echo $imgurl; ?>" />
<meta itemprop="name" content="<?php echo $page_title; ?>" />
<meta itemprop="description" content="<?php echo $description; ?>" />
<meta itemprop="image" content="<?php echo $imgurl; ?>" />
<meta name="twitter:card" content="photo" />
<meta name="twitter:title" content="<?php echo $page_title; ?>" />
<meta name="twitter:image" content="<?php echo $imgurl; ?>" />
</head>
<?php embed_ga();?>
<?php page_redirect();?>
</html>
