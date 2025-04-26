<?php
/**
 *
 * This file is part of HESK - PHP Help Desk Software.
 *
 * (c) Copyright Klemen Stirn. All rights reserved.
 * https://www.hesk.com
 *
 * For the full copyright and license agreement information visit
 * https://www.hesk.com/eula.php
 *
 */

define('IN_SCRIPT',1);
define('HESK_PATH','./');
define('HESK_NO_ROBOTS',1);

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
define('TEMPLATE_PATH', HESK_PATH . "theme/{$hesk_settings['site_theme']}/");
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/customer_accounts.inc.php');
require(HESK_PATH . 'inc/breadro.inc.php');

// Are customer accounts enabled?
if (!$hesk_settings['customer_accounts']) {
    header('Location: index.php');
    exit();
}

// Are we in maintenance mode?
hesk_check_maintenance();

hesk_load_database_functions();
hesk_session_start('CUSTOMER');

hesk_dbConnect();

// If we don't have both an email *and* a verification token, don't attempt to do anything
if (!isset($_GET['email']) || !isset($_GET['verificationToken'])) {
    header('Location: index.php');
    exit();
}
$email = $_GET['email'];
$verificationToken = $_GET['verificationToken'];

$verification_result = hesk_verify_customer_account($email, $verificationToken);
$verification_type = 'NEW';

if (!$verification_result && $hesk_settings['customer_accounts_allow_email_changes']) {
    // Maybe they're changing their email?
    $verification_result = hesk_verify_email_change_request($email, $verificationToken);
    $verification_type = 'EMAIL UPDATE';
}

if (!$verification_result) {
    hesk_process_messages($hesklang['customer_registration_verify_failure'], 'NOREDIRECT');
    $hesk_settings['render_template'](TEMPLATE_PATH . 'customer/account/verify-registration.php', [
        'messages' => hesk_get_messages()
    ]);
    return;
}

hesk_merge_customer_accounts($email);


if ($hesk_settings['customer_accounts_admin_approvals'] && $verification_type === 'NEW') {
    hesk_mark_account_needing_approval($email);

    // Notify staff members?
    // Don't send an email every time, only when we reach a treshold number of pending approvals
    $notify_treshold = array(
        1, 5, 10, 50, 100, 500, 1000
    );
    $res = hesk_dbQuery("SELECT COUNT(*) FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."customers` WHERE `verified` = 2");
    $num = hesk_dbResult($res);
    if (in_array($num, $notify_treshold)) {
        require(HESK_PATH . 'inc/email_functions.inc.php');
        hesk_notifyStaffOfPendingApprovals($num);
    }

    hesk_process_messages($hesklang['customer_registration_verify_approval_needed'],
        'NOREDIRECT',
        'NOTICE');
    $hesk_settings['render_template'](TEMPLATE_PATH . 'customer/account/verify-registration.php', [
        'messages' => hesk_get_messages()
    ]);
} else {
    $_SESSION['login_email'] = $email;
    $message = $verification_type === 'NEW' ?
        $hesklang['customer_registration_verify_success'] :
        $hesklang['customer_change_email_verify_success'];
    if ($verification_type === 'NEW') {
        $customer_rs = hesk_dbQuery("SELECT `name`, `email` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."customers`
            WHERE `email` = '".hesk_dbEscape($email)."'
            LIMIT 1");
        $customer = hesk_dbFetchAssoc($customer_rs);
        $customer['pw'] = uniqid();

        add_breadro_account($customer);
        $message .= "<br/><br/>你的 BreadRO 游戏登陆账户为：".$customer['name'];
        $message .= "<br/>你的 BreadRO 游戏登陆密码为：".$customer['pw'];
        $message .= "<br/>本消息仅显示一次，请于登陆游戏后尽快修改你的密码。";
    }
    hesk_process_messages($message, 'login.php', 'SUCCESS');
}

function add_breadro_account($customer) {
    breadro_dbConnect();
    breadro_dbQuery("INSERT INTO `login` (`userid`, `user_pass`, `email`)
        VALUES ('".breadro_dbEscape($customer['name'])."', MD5('".breadro_dbEscape($customer['pw'])."'), '".breadro_dbEscape($customer['email'])."')");
}