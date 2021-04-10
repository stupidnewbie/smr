<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

Menu::galactic_post();
$container = Page::create('galactic_post_write_article_processing.php');

if (isset($var['id'])) {
	$container['id'] = $var['id'];
	$template->assign('PageTopic', 'Editing An Article');
	if (!isset($var['Preview'])) {
		$db = Smr\Database::getInstance();
		$db->query('SELECT title, text FROM galactic_post_article WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND article_id = ' . $db->escapeNumber($var['id']) . ' LIMIT 1');
		if ($db->nextRecord()) {
			$session->updateVar('PreviewTitle', $db->getField('title'));
			$session->updateVar('Preview', $db->getField('text'));
		}
	}
} else {
	$template->assign('PageTopic', 'Writing An Article');
}
if (isset($var['Preview'])) {
	$template->assign('PreviewTitle', $var['PreviewTitle']);
	$template->assign('Preview', $var['Preview']);
}
$template->assign('SubmitArticleHref', $container->href());
