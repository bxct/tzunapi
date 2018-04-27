<html>
    <head></head>
    <body>
        <p><a href="/cache_info.php?clear=1">Clear!</a></p>
        <div>
<?php
if(array_key_exists('clear', $_GET)) {
    apc_clear_cache();
    header('location: /cache_info.php');
    exit();
}
var_dump(apc_cache_info());
?>
        </div>
    </body>
</html>