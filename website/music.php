<?php
include('includes/stdafx.php');
$songlist = new Songlist();
$checksum = $songlist->generateChecksum();
if(isset($_GET['checksum'])) {
	exit($checksum);
}
if(!isset($_GET['update'])) {
	?>
	<script type="text/javascript">
		var checksum = '<?php echo($checksum); ?>';
	</script>
	<?php
}
?>
