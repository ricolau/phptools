<?php
/**
 *
 * @version 2017/2/7
*/

$toolsDir = dirname(__FILE__);

require $toolsDir.'/http.class.php';
require $toolsDir.'/mysqld.class.php';
require $toolsDir.'/pinyin.class.php';
require $toolsDir.'/shell.class.php';

require $toolsDir.'/rico.class.php';

rico::loadMiscellaneous();
