<?php
/***************************************************************************
 *
 *   BBSCoin Api for PHP
 *   Author: BBSCoin Foundation
 *   
 *   Website: https://bbscoin.xyz
 *
 ***************************************************************************/
 
/****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Class for site interface
class BBSCoinApiPartner {

    public static function callback($json_data) {
        global $config, $_G;

        if ($json_data['data']['action'] == 'deposit') {
            if ($json_data['callbackData']['amount'] > 0) {
                $trans_amount = $json_data['callbackData']['amount'] / 100000000;
                $amount = $trans_amount * $config['pay_ratio'];

                $orderid = dgmdate(TIMESTAMP, 'YmdHis').random(3);
                $orderinfo = array(
                	'orderid' => $orderid,
                	'status' => '2',
                	'confirmdate' => $_G['timestamp'],
                	'uid' => $json_data['data']['uin'],
                	'amount' => $amount,
                	'price' => $trans_amount,
                	'submitdate' => $_G['timestamp'],
                );
                C::t('forum_order')->insert($orderinfo);

                $transaction_info = C::t('#bbscoin#common_bbscoin')->fetch_by_transaction_hash($json_data['callbackData']['hash']);
                if($transaction_info['transaction_hash']) {
                    return array('success' => true);
                }

                C::t('#bbscoin#common_bbscoin')->insert(
                    array(
                        'orderid' => $orderid,
                        'transaction_hash' => $json_data['callbackData']['hash'],
                        'address' => '',
                        'dateline' => $_G['timestamp'],
                    )
                );
            	updatemembercount($json_data['data']['uin'], array($config['pay_credit'] => $amount), 1, 'AFD', $json_data['data']['uin']);
            	notification_add($json_data['data']['uin'], 'system', 'system_notice', array('subject' => lang('plugin/bbscoin', 'pay_lang_s9'), 'message' => lang('plugin/bbscoin', 'pay_lang_s8').$orderid, 'from_id' => 0, 'from_idtype' => 'sendnotice'), 1);
            }

            return array('success' => true);
        } elseif ($json_data['data']['action'] == 'withdraw') {
            if ($json_data['callbackData']['status'] != 'normal') {
            	updatemembercount($json_data['data']['uin'], array($config['pay_credit'] => $json_data['data']['points']), 1, 'AFD', $json_data['data']['uin']);
            	notification_add($json_data['data']['uin'], 'system', 'system_notice', array('subject' => lang('plugin/bbscoin', 'pay_lang_s16'), 'message' => lang('plugin/bbscoin', 'pay_lang_s17').$json_data['data']['orderid'], 'from_id' => 0, 'from_idtype' => 'sendnotice'), 1);
            } else {
                C::t('forum_order')->update($json_data['data']['orderid'], array('status' => '2', 'confirmdate' => $_G['timestamp']));
            }

            return array('success' => true);
        } else {
            return array('success' => false, 'message' => 'error action');
        }
    }
}
