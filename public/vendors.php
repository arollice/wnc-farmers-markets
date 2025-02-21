<?php
include_once('../private/config.php');

// Include the header
include_once HEADER_FILE;
?>

<h1>Vendors</h1>
<?php displayTable('vendor');
?>
<p>This page will display a list of all vendors.</p>

<?php
// Include the footer
include_once FOOTER_FILE;
?>
