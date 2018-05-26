<?php

defined('_JEXEC') or die;

JLoader::import('ActivitiesHelper', JPATH_ADMINISTRATOR . '/components/com_jagdpr/helpers/');

/**
 * @package     JaGdprKunena
 *
 * @since       version
 */
class PlgJaGdprKunena extends JPlugin {

	/**
	 *
	 * @return string
	 *
	 * @since version
	 */
	function onPrepareLayout() {

		// Check if Kunena API exists
		$api = JPATH_ADMINISTRATOR . '/components/com_kunena/api.php';

		if (!is_file($api)) {
			return;
		}

		jimport('joomla.application.component.helper');

		// Check if Kunena component is installed/enabled
		if (!\Joomla\CMS\Component\ComponentHelper::isEnabled('com_kunena')) {
			return;
		}

		// Load Kunena API
		require_once $api;

		// Do not load if Kunena version is not supported or Kunena is not installed
		if (!(class_exists('KunenaForum') && KunenaForum::isCompatible('4.0') && KunenaForum::installed())) {
			return;
		}

		include_once JPluginHelper::getLayoutPath('jagdpr', 'kunena');
	}

	/**
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	function getRequestPending() {
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$sql = 'SELECT * FROM #__jagdpr_activities WHERE userid = ' . $user->id . ' AND status = "pending" AND plugin="kunena" LIMIT 1';
		$db->setQuery($sql);
		$exists = $db->loadResult();

		return $exists;
	}

	/**
	 *
	 *
	 * @since version
	 * @throws Exception
	 */
	function onRequestdelete() {
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		// Send to activities
		ActivitiesHelper::insertActivity($user, 'request delete', 'pending', 'kunena');

		$app->enqueueMessage(JText::_('COM_JAGDPR_REQUEST_DELETE_MESSAGE_SENT'));
		$app->redirect(JRoute::_('index.php?option=com_jagdpr'));

		jexit();
	}

	/**
	 * @param   null $userid userid
	 * @param   bool $admin  admin
	 *
	 *
	 * @since version
	 * @throws Exception
	 */
	function onDeleteuser($userid = null, $admin = false) {
		$user = KunenaUserHelper::get($userid);
		$app = JFactory::getApplication();
		$this->loadLanguage();
		$lang = JFactory::getLanguage()->getTag();
		$this->kversion = KunenaForum::version();
		$this->anonymous_text = $this->params->get('anonymous_text')->{$lang};
		$this->anonymous_text = empty($this->anonymous_text) ? 'Removed by GDPR' : $this->anonymous_text;
		$anonymous_id = $this->createAnonymousUser($use->id);
		$this->anonymizeUser($user->id, $anonymous_id);
		
		$anonymize_content = $this->params->get('anonymize_content') ? true : false;
		$delete_attachments = $this->params->get('delete_attachments') ? true : false;
		$this->anonymizeTopics($user->id, $anonymous_id, $anonymize_content, $delete_attachments);

		if (empty($admin)) {
			ActivitiesHelper::insertActivity($user, 'deleted', 'completed', 'kunena');
			ActivitiesHelper::sendMailToUser($user, 'deleted', 'completed', 'kunena');
			$app->enqueueMessage(JText::_('COM_JAGDPR_DELETE_MESSAGE_SUCCESS'));
			$app->redirect(JRoute::_('index.php?option=com_jagdpr'));
		}
	}
	
	function createAnonymousUser($userid) {
		$config = JFactory::getConfig();
		$db = JFactory::getDbo();
		$name = JText::_('JAGDPR_KUNENA_ANONYMOUS_NAME');
		$username = JText::_('JAGDPR_KUNENA_ANONYMOUS_USERNAME') . md5($userid . date('Y-m-d H:i:s') . $config->get('secret'));

		$email = JText::_('JAGDPR_KUNENA_ANONYMOUS_EMAIL');
		$password = rand(1000, 9999);

		$sql = "insert into #__users (`name`, `username`, `email`, `password`, `registerDate`, `params`) values ('" . $db->escape($name) . "', '" . $db->escape($username) . "', '" . $db->escape($email) . "', '" . $db->escape($password) . "', now(), '{}')";
		$db->setQuery($sql);
		$db->execute();
		$anonymous_id = $db->insertid();

		$sql = "insert into #__user_usergroup_map (`user_id`, `group_id`) values (" . intval($anonymous_id) . ", '2')";
		$db->setQuery($sql);
		$db->execute();

		return intval($anonymous_id);
	}
	
	function anonymizeUser($userid, $anonymous_id) {
		$db = JFactory::getDbo();
		$query = 'UPDATE #__kunena_users';
		$query .= ' SET userid = ' . $anonymous_id . ',';
		$query .= ' status = 0,';
		$query .= ' status_text = 0,';
		$query .= ' view = "",';
		$query .= ' signature = "",';
		$query .= ' moderator = 0,';
		$query .= ' banned = "0000-00-00 00:00:00",';
		$query .= ' posts = 0,';
		$query .= ' avatar = "",';
		$query .= ' karma = 0,';
		$query .= ' karma_time = 0,';
		$query .= ' group_id = 1,';
		$query .= ' personalText = "",';
		$query .= ' gender = 0,';
		$query .= ' birthdate = "0000-00-00",';
		$query .= ' location = "",';
		$query .= ' friendfeed = "",';
		$query .= ' bebo = "",';
		$query .= ' digg = "",';
		$query .= ' icq = "",';
		$query .= ' telegram = "",';
		$query .= ' vk = "",';
		$query .= ' microsoft = "",';
		$query .= ' skype = "",';
		$query .= ' twitter = "",';
		$query .= ' facebook = "",';
		$query .= ' google = "",';
		$query .= ' myspace = "",';
		$query .= ' linkedin = "",';
		$query .= ' delicious = "",';
		$query .= ' instagram = "",';
		$query .= ' qq = "",';
		$query .= ' blogspot = "",';
		$query .= ' flickr = "",';
		$query .= ' apple = "",';
		$query .= ' qzone = "",';
		$query .= ' weibo = "",';
		$query .= ' wechat = "",';
		$query .= ' yim = "",';
		if (version_compare($this->kversion, '5.1', 'ge')) {
			$query .= ' whatsapp = "",';
			$query .= ' youtube = "",';
			$query .= ' ok = "",';
			$query .= ' socialshare = 0,';
		}
		$query .= ' websitename = "",';
		$query .= ' websiteUrl = "",';
		$query .= ' rank = 0,';
		$query .= ' hideEmail = 1,';
		$query .= ' showOnline = 0,';
		$query .= ' canSubscribe = -1,';
		$query .= ' userListtime = -2,';
		$query .= ' thankyou = 0,';
		$query .= ' ip = ""';
		$query .= ' WHERE userid = ' . $userid;
		$db->setQuery($query);
		$db->execute();

		$query = 'INSERT INTO #__kunena_users_banned (userid, expiration) VALUES (' . $anonymous_id . ', "0000-00-00 00:00:00")';
		$db->setQuery($query);
		$db->execute();

		$query = 'UPDATE #__kunena_polls_users SET userid = ' . $anonymous_id . ' WHERE userid = ' . $userid;
		$db->setQuery($query);
		$db->execute();

		$query = 'UPDATE #__kunena_user_categories SET user_id = ' . $anonymous_id . ' WHERE user_id = ' . $userid;
		$db->setQuery($query);
		$db->execute();

		$query = 'UPDATE #__kunena_user_read SET user_id = ' . $anonymous_id . ' WHERE user_id = ' . $userid;
		$db->setQuery($query);
		$db->execute();

		$query = 'UPDATE #__kunena_announcement SET created_by = ' . $anonymous_id . ' WHERE created_by = ' . $userid;
		$db->setQuery($query);
		$db->execute();

		$query = 'UPDATE #__kunena_user_topics SET user_id = ' . $anonymous_id . ' WHERE user_id = ' . $userid;
		$db->setQuery($query);
		$db->execute();


		$query = 'UPDATE #__kunena_thankyou SET userid = ' . $anonymous_id . ' WHERE userid = ' . $userid;
		$db->setQuery($query);
		$db->execute();
	}

	function anonymizeTopics($userid, $anonymous_id, $anonymize_content, $delete_attachments) {
		$db = JFactory::getDbo();
		// anonymize first posts
		$query = 'UPDATE #__kunena_topics';
		$query .= ' SET first_post_userid = ' . $anonymous_id;
		
		if ($anonymize_content) {
			$query .= ',';
//			$query .= ' subject = ' . $db->quote($this->anonymous_text) . ',';
			$query .= ' first_post_message = ' . $db->quote($this->anonymous_text) . ',';
			$query .= ' first_post_guest_name = ' . $db->quote(JText::_('JAGDPR_KUNENA_ANONYMOUS_NAME'));
		}
		
		$query .= ' WHERE first_post_userid = ' . $userid;
		$db->setQuery($query);
		$db->execute();

		// anonymize last posts
		$query = 'UPDATE #__kunena_topics';
		$query .= ' SET last_post_userid = ' . $anonymous_id;
		if ($anonymize_content) {
			$query .= ',';
			$query .= ' last_post_message = ' . $db->quote($this->anonymous_text) . ',';
			$query .= ' last_post_guest_name = ' . $db->quote(JText::_('JAGDPR_KUNENA_ANONYMOUS_NAME'));
		}
		$query .= ' WHERE last_post_userid = ' . $userid;
		$db->setQuery($query);
		$db->execute();

		$query = 'SELECT id FROM #__kunena_messages WHERE userid = ' . $userid;
		$db->setQuery($query);
		$ids = $db->loadColumn();
		
		if ($delete_attachments) {
			$this->deleteAttachments($userid);
		} else {
			$this->anonymizeAttachmenst($userid, $anonymous_id);
		}

		if (count($ids)) {
			// anonymize messages
			$query = 'UPDATE #__kunena_messages';
			$query .= ' SET userid = ' . $anonymous_id . ',';
			if ($anonymize_content) {
				$query .= ' name = ' . $db->quote(JText::_('JAGDPR_KUNENA_ANONYMOUS_NAME')) . ',';
				$query .= ' email = ' . $db->quote(JText::_('JAGDPR_KUNENA_ANONYMOUS_EMAIL')) . ',';
				$query .= ' subject = ' . $db->quote($this->anonymous_text) . ',';
				$query .= ' ip = "",';
			}
			$query .= ' modified_by = ""';
			$query .= ' WHERE userid = ' . $userid;
			$db->setQuery($query);
			$db->execute();

			// anonymize messages text
			if ($anonymize_content) {
				$query = 'UPDATE #__kunena_messages_text'
						. ' SET message = ' . $db->quote($this->anonymous_text)
						. ' WHERE mesid in (' . implode(',', $ids) . ')';
				$db->setQuery($query);
				$db->execute();
			}
		}
	}
	
	function deleteAttachments($userid) {
		if (!$userid) {
			return;
		}
		jimport('joomla.filesystem.file');
		$db = JFactory::getDbo();
		$query = 'SELECT folder, filename FROM `#__kunena_attachments` WHERE userid = ' . $userid;
		$db->setQuery($query);
		$attachments = $db->loadObjectList();
		
		foreach ($attachments as $att) {
			JFile::delete( JPATH_ROOT . '/' . $att->folder . '/' . $att->filename);
		}
		
		$query = 'DELETE FROM `#__kunena_attachments` WHERE userid = ' . $userid;
		$db->setQuery($query);
		$db->execute();
	}

	function anonymizeAttachmenst($userid, $anonymous_id) {
		$db = JFactory::getDbo();
		$query = 'UPDATE `#__kunena_attachments` SET userid = ' . $anonymous_id . ' WHERE userid = ' . $userid;
		$db->setQuery($query);
		$db->execute();
	}

	public function socialButtons() {
		$social = array('twitter' => array('url' => 'https://twitter.com/##VALUE##', 'title' => JText::_('COM_KUNENA_MYPROFILE_TWITTER'), 'nourl' => '0'),
			'facebook' => array('url' => 'https://www.facebook.com/##VALUE##', 'title' => JText::_('COM_KUNENA_MYPROFILE_FACEBOOK'), 'nourl' => '0'),
			'myspace' => array('url' => 'https://www.myspace.com/##VALUE##', 'title' => JText::_('COM_KUNENA_MYPROFILE_MYSPACE'), 'nourl' => '0'),
			'linkedin' => array('url' => 'https://www.linkedin.com/in/##VALUE##', 'title' => JText::_('COM_KUNENA_MYPROFILE_LINKEDIN'), 'nourl' => '0'),
			'delicious' => array('url' => 'https://del.icio.us/##VALUE##', 'title' => JText::_('COM_KUNENA_MYPROFILE_DELICIOUS'), 'nourl' => '0'),
			'friendfeed' => array('url' => 'http://friendfeed.com/##VALUE##', 'title' => JText::_('COM_KUNENA_MYPROFILE_FRIENDFEED'), 'nourl' => '0'),
			'digg' => array('url' => 'http://www.digg.com/users/##VALUE##', 'title' => JText::_('COM_KUNENA_MYPROFILE_DIGG'), 'nourl' => '0'),
			'skype' => array('url' => 'skype:##VALUE##', 'title' => JText::_('COM_KUNENA_MYPROFILE_SKYPE'), 'nourl' => '0'),
			'yim' => array('url' => '##VALUE##', 'title' => JText::_('COM_KUNENA_MYPROFILE_YIM'), 'nourl' => '1'),
			'google' => array('url' => 'https://plus.google.com/+##VALUE##', 'title' => JText::_('COM_KUNENA_MYPROFILE_GOOGLE'), 'nourl' => '0'),
			'microsoft' => array('url' => '##VALUE##', 'title' => JText::_('COM_KUNENA_MYPROFILE_MICROSOFT'), 'nourl' => '1'),
			'icq' => array('url' => 'https://icq.com/people/cmd.php?uin=##VALUE##&action=message', 'title' => JText::_('COM_KUNENA_MYPROFILE_ICQ'), 'nourl' => '0'),
			'blogspot' => array('url' => 'https://##VALUE##.blogspot.com/', 'title' => JText::_('COM_KUNENA_MYPROFILE_BLOGSPOT'), 'nourl' => '0'),
			'flickr' => array('url' => 'https://www.flickr.com/photos/##VALUE##', 'title' => JText::_('COM_KUNENA_MYPROFILE_FLICKR'), 'nourl' => '0'),
			'bebo' => array('url' => 'https://www.bebo.com/Profile.jsp?MemberId=##VALUE##', 'title' => JText::_('COM_KUNENA_MYPROFILE_BEBO'), 'nourl' => '0'),
			'instagram' => array('url' => 'https://www.instagram.com/##VALUE##/', 'title' => JText::_('COM_KUNENA_MYPROFILE_INSTAGRAM'), 'nourl' => '0'),
			'qq' => array('url' => '##VALUE##', 'title' => JText::_('COM_KUNENA_MYPROFILE_QQ'), 'nourl' => '1'),
			'qzone' => array('url' => '##VALUE##', 'title' => JText::_('COM_KUNENA_MYPROFILE_QZONE'), 'nourl' => '1'),
			'weibo' => array('url' => '##VALUE##', 'title' => JText::_('COM_KUNENA_MYPROFILE_WEIBO'), 'nourl' => '1'),
			'wechat' => array('url' => '##VALUE##', 'title' => JText::_('COM_KUNENA_MYPROFILE_WECHAT'), 'nourl' => '1'),
			'vk' => array('url' => 'https://vk.com/##VALUE##', 'title' => JText::_('COM_KUNENA_MYPROFILE_VK'), 'nourl' => '0'),
			'telegram' => array('url' => 'https://t.me/##VALUE##', 'title' => JText::_('COM_KUNENA_MYPROFILE_TELEGRAM'), 'nourl' => '0'),
			'apple' => array('url' => '##VALUE##', 'title' => JText::_('COM_KUNENA_MYPROFILE_APPLE'), 'nourl' => '1'),
			'whatsapp' => array('url' => '##VALUE##', 'title' => JText::_('COM_KUNENA_MYPROFILE_WHATSAPP'), 'nourl' => '1'),
			'youtube' => array('url' => 'https://www.youtube.com/##VALUE##', 'title' => JText::_(' COM_KUNENA_MYPROFILE_YOUTUBE'), 'nourl' => '0'),
			'ok' => array('url' => 'https://ok.ru/##VALUE##', 'title' => JText::_('COM_KUNENA_MYPROFILE_OK'), 'nourl' => '0'),
		);

		return $social;
	}

}
