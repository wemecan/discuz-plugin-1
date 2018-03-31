<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_bbscoin {
	function common() {
		global $_G;

        if (!isset($_G['cache']['plugin']['bbscoin'])) {
            loadcache('plugin');
        }
        $config = $_G['cache']['plugin']['bbscoin'];

        if ($_GET['bbscoin_api'] == 1 && $config['bbscoin_siteid'] && $config['bbscoin_sitekey']) {
            require_once DISCUZ_ROOT.'./source/plugin/bbscoin/bbscoinapi.php';
            require_once DISCUZ_ROOT.'./source/plugin/bbscoin/bbscoinapi_partner.php';

            BBSCoinApiWebWallet::setSiteInfo($config['bbscoin_siteid'], $config['bbscoin_sitekey']);
            BBSCoinApiWebWallet::recvCallback();
        }
    }
}



