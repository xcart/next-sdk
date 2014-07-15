#!/usr/bin/env php
<?php
$buf='';
do {
    $buf_ = fgets (STDIN);
    $buf.=$buf_;
} while (!empty($buf_));

#var_dump($buf);
if (preg_match('/INSTANCE\s+(i-\S*)\s+(ami-\S*)\s+(\S*)\s+(\S*)\s+(\S*)/Sm', $buf, $matches)) {

            $result['instance_id'] = $matches[1];
            $result['ami'] = $matches[2];
            if (preg_match("/running$/S", $matches[0])) {
                $result['public_dns'] = $matches[3];
                $result['private_dns'] = $matches[4];
                $result['status'] = $matches[5];
                echo $result['public_dns'];
                die();
            }
            else {
                $result['status'] = $matches[3];
            }
}
echo 'N';

