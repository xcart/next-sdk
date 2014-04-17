<?php
if (!isset($_GET['branch'])) {
    die('ERROR.');
}
//var_dump($_SERVER);
$host = $_SERVER['HTTP_HOST'];

$string = file_get_contents('next/src/etc/config.php');
$pattern = '/(\[host_details\]\nhttp_host = ")(.*)("\nhttps_host = ")(.*)(")/i';
$replacement = '${1}' . $host . '${3}' . $host . '${5}';

$data =  preg_replace($pattern, $replacement, $string);
file_put_contents('next/src/etc/config.php', $data);

echo "Instaling from branch:" . $_GET['branch'];
flush();
echo '<pre>';
passthru ('./branch.sh ' . $_GET['branch']);
echo '</pre>';