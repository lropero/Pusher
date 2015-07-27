<?php
include('includes/stdafx.php');
if(isset($_POST['string'])) {
	file_put_contents('songlist.db', $_POST['string']);
	$songlist = new Songlist();
	$songlist->extractCovers();
}
?>