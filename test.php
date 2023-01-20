<?php
if(isset($_COOKIE['exportData'])) {
    echo "<pre>";
    print_r(json_decode($_COOKIE['exportData']));
}
?>