<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}

BBSCoinApiWebWallet::setSiteInfo($config['bbscoin_siteid'], $config['bbscoin_sitekey'], $config['bbscoin_nosecure']);

//提交表单
if(submitcheck('addfundssubmit')){

    $orderid = date('YmdHis').base_convert($_G['uid'], 10, 36);
    $transaction_hash = trim($_POST['transactionhash']);
    $paymentId = trim($_POST['paymentId']);

    if(discuz_process::islocked('pay_bbscoin_'.$_G['uid'], 10)) {
    	showmessage(lang('plugin/bbscoin', 'pay_lang_s4'), '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
    }

    $transaction_info = C::t('#bbscoin#common_bbscoin')->fetch_by_transaction_hash($transaction_hash);

    if($transaction_info['transaction_hash']) {
        discuz_process::unlock('pay_bbscoin_'.$_G['uid']);
    	showmessage(lang('plugin/bbscoin', 'pay_lang_s3'), '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
    }

    if ($paymentId != $_G['bbscoin_paymentid']) {
        discuz_process::unlock('pay_bbscoin_'.$_G['uid']);
        showmessage(lang('plugin/bbscoin', 'pay_lang_s2'), '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
    }

    // online wallet
    try {
        $rsp_data = BBSCoinApiWebWallet::checkTransaction($config['bbscoin_walletd'], $transaction_hash, $paymentId, $_G['uid']);
    } catch (Exception $e) {
        discuz_process::unlock('pay_bbscoin_'.$_G['uid']);
    	showmessage('Error '.$e->getCode().','.$e->getMessage(), '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
    }

    discuz_process::unlock('pay_bbscoin_'.$_G['uid']);
    if ($rsp_data['success']) {
        showmessage(lang('plugin/bbscoin', 'pay_lang_s14'), '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
    } else {
        showmessage(lang('plugin/bbscoin', 'pay_lang_s15').' '.$rsp_data['message'], '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
    }

    exit();
} elseif(submitcheck('addcoinssubmit') && $config['pay_to_bbscoin']){
    $amount = $_POST['addcoinamount'];
    $need_point = ceil((($amount / $config['pay_to_coin_ratio']) * 100)) / 100;

    if ($need_point < 1) {
    	showmessage(lang('plugin/bbscoin', 'pay_lang_s1').'1');
    }

    $walletaddress = trim($_POST['walletaddress']);

    if ($config['bbscoin_wallet_address'] == $walletaddress) {
        showmessage(lang('plugin/bbscoin', 'pay_lang_s5'), '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
    }

    $real_price = $amount - $config['withdraw_fee'];

    if ($real_price <= 0) {
        showmessage(lang('plugin/bbscoin', 'pay_lang_s12'), '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
    }

    if(discuz_process::islocked('pay_bbscoin_'.$_G['uid'], 10)) {
    	showmessage(lang('plugin/bbscoin', 'pay_lang_s4'), '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
    }

    if ($need_point > getuserprofile('extcredits'.$config['pay_credit'])) {
        discuz_process::unlock('pay_bbscoin_'.$_G['uid']);
        showmessage(lang('plugin/bbscoin', 'pay_lang_s10'), '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
    }

    $orderid = date('YmdHis').base_convert($_G['uid'], 10, 36);

    $orderinfo = array(
    	'orderid' => $orderid,
    	'status' => '1',
    	'uid' => $_G['uid'],
    	'amount' => $need_point,
    	'price' => $amount,
    	'submitdate' => $_G['timestamp'],
    );
    C::t('forum_order')->insert($orderinfo);

    try {
        $rsp_data = BBSCoinApiWebWallet::send($config['bbscoin_walletd'], $config['bbscoin_wallet_address'], $real_price, $walletaddress, $orderid, $_G['uid'], $need_point, $config['withdraw_fee']);
    } catch (Exception $e) {
        discuz_process::unlock('pay_bbscoin_'.$_G['uid']);
    	showmessage('Error '.$e->getCode().','.$e->getMessage(), '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
    }

    if ($rsp_data['success'] == true) {
        C::t('#bbscoin#common_bbscoin')->insert(
            array(
                'orderid' => $orderid,
                'transaction_hash' => $rsp_data['result']['transactionHash'],
                'address' => $walletaddress,
                'dateline' => $_G['timestamp'],
            )
        );
    	updatemembercount($_G['uid'], array($config['pay_credit'] => -$need_point), 1, 'AFD', $_G['uid']);

    	notification_add($_G['uid'], 'system', 'system_notice', array('subject' => lang('plugin/bbscoin', 'pay_lang_s7'), 'message' => lang('plugin/bbscoin', 'pay_lang_s6').$orderid.', '.$rsp_data['result']['transactionHash'], 'from_id' => 0, 'from_idtype' => 'sendnotice'), 1);

        discuz_process::unlock('pay_bbscoin_'.$_G['uid']);
        showmessage(lang('plugin/bbscoin', 'pay_lang_s6').$orderid.', '.$rsp_data['result']['transactionHash'], '', array(), array('alert' => 'right', 'showdialog' => 1, 'showmsg' => true, 'closetime' => true));
    } else {
        discuz_process::unlock('pay_bbscoin_'.$_G['uid']);
        showmessage(lang('plugin/bbscoin', 'pay_lang_s5').' '.$rsp_data['message'], '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
    }

    exit();

}
