<?php
$flashStatus = $_GET["status"] ?? "";
$flashMessage = $_GET["message"] ?? "";
?>

<?php if ($flashMessage !== "") { ?>
    <div class="alert <?php echo $flashStatus === "success" ? "alert-success" : "alert-danger"; ?> border-0 shadow-sm">
        <?php echo htmlspecialchars($flashMessage, ENT_QUOTES, "UTF-8"); ?>
    </div>
<?php } ?>
