<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once DISCUZ_ROOT.'./source/plugin/bbscoin/bbscoinapi.php';
require_once DISCUZ_ROOT.'./source/plugin/bbscoin/bbscoinapi_partner.php';

global $_G;

$config = _config();

if (!$config['bbscoin_wallet_address']) {
    showmessage(lang('plugin/bbscoin', 'pay_lang_s11'));
}

$_G['bbscoin_paymentid'] = hash('sha256', $_G['setting']['siteuniqueid'].$_G['uid']);

if ($config['bbscoin_apimode'] == 1) {
    require_once DISCUZ_ROOT.'./source/plugin/bbscoin/webapi.inc.php';
} elseif ($config['bbscoin_apimode'] == 2) {
    require_once DISCUZ_ROOT.'./source/plugin/bbscoin/webwallet.inc.php';
} else {
    require_once DISCUZ_ROOT.'./source/plugin/bbscoin/walletd.inc.php';
}


function _config() {
	global $_G;
    if (!isset($_G['cache']['plugin'])) {
        loadcache('plugin');
    }
    $config = $_G['cache']['plugin']['bbscoin'];
	return $config;		
}

