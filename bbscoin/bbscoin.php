<?php
chdir('../../../');

define('DISABLEXSSCHECK', true);

require './source/class/class_core.php';

$discuz = C::app();

$cachelist = array('plugin');

$discuz->cachelist = $cachelist;
$discuz->init();

if (!isset($_G['cache']['plugin']['bbscoin'])) {
    loadcache('plugin');
}
$config = $_G['cache']['plugin']['bbscoin'];

require_once DISCUZ_ROOT.'./source/plugin/bbscoin/bbscoinapi.php';
require_once DISCUZ_ROOT.'./source/plugin/bbscoin/bbscoinapi_partner.php';

BBSCoinApiWebWallet::setSiteInfo($config['bbscoin_siteid'], $config['bbscoin_sitekey']);
BBSCoinApiWebWallet::recvCallback();

