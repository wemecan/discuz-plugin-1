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

$_G['bbscoin_paymentid'] = hash('sha256', $_G['setting']['siteuniqueid'].$_G['uid']);

require_once DISCUZ_ROOT.'./source/plugin/bbscoin/bbscoinapi.php';
require_once DISCUZ_ROOT.'./source/plugin/bbscoin/bbscoinapi_partner.php';

BBSCoinApiWebWallet::setSiteInfo($config['bbscoin_siteid'], $config['bbscoin_sitekey'], $config['bbscoin_nosecure']);
BBSCoinApiWebWallet::recvCallback();

