<?php declare(strict_types=1);

$container = Page::create('skeleton.php', 'message_view.php');
$container->addVar('folder_id');

if (Request::get('action') == 'No') {
	$container->go();
}

if (empty($var['message_id'])) {
	create_error('Please click the small yellow icon to report a message!');
}

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

// get next id
$db = Smr\Database::getInstance();
$db->query('SELECT max(notify_id) FROM message_notify WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY notify_id DESC');
if ($db->nextRecord()) {
	$notify_id = $db->getInt('max(notify_id)') + 1;
} else {
	$notify_id = 1;
}

// get message form db
$db->query('SELECT account_id, sender_id, message_text
			FROM message
			WHERE message_id = ' . $var['message_id'] . ' AND receiver_delete = \'FALSE\'');
if (!$db->nextRecord()) {
	create_error('Could not find the message you selected!');
}

// insert
$db->query('INSERT INTO message_notify
			(notify_id, game_id, from_id, to_id, text, sent_time, notify_time)
			VALUES ('.$notify_id . ', ' . $db->escapeNumber($player->getGameID()) . ', ' . $db->getInt('sender_id') . ', ' . $db->getInt('account_id') . ', ' . $db->escapeString($db->getField('message_text')) . ', ' . $var['sent_time'] . ', ' . $var['notified_time'] . ')');

$container->go();
