<?php
$flashStatus = $_GET["status"] ?? "";
$flashMessage = $_GET["message"] ?? "";
?>

<?php
if ($flashMessage !== "") {
    enqueue_toast($toastMessages, $flashMessage, $flashStatus === "success" ? "success" : "error");
}
?>
