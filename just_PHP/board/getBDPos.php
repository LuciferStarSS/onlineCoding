<?php
//ѧ���˻�ȡС�װ嵱ǰ����
$room=isset($_COOKIE['R'])?$_COOKIE['R']:1;
$classid=isset($_COOKIE['CLASSID'])?$_COOKIE['CLASSID']:3;
include "../../include/config.inc.php";
//$pos= @file_get_contents("../../data/config/".$room."/".$classid.".bdpos.dat");
//echo "./".$room."/".$classid.".bdpos.dat";
$pos= @file_get_contents("./".$room."/".$classid.".bdpos.dat");
if($pos!="")
   echo $pos;
else
   echo "[]";
?>