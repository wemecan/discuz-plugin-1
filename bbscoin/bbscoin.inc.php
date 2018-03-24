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


if ($config['bbscoin_siteid'] && $config['bbscoin_sitekey']) {
    BBSCoinApiWebWallet::setSiteInfo($config['bbscoin_siteid'], $config['bbscoin_sitekey']);
    BBSCoinApiWebWallet::recvCallback();
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

