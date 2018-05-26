<?php
/**
 * $JA#COPYRIGHT$
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * @package     ${NAMESPACE}
 *
 * @since       version
 */
class PlgJagdprKunenaInstallerScript
{
	function postflight($type, $parent)
	{
		// Enable plugin
		if ($type == 'install')
		{
			$db    = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->update('#__extensions')
				->set('enabled=1, params=' . $db->quote('{}') . '')
				->where(array(
					$db->quoteName('type') . '=' . $db->quote('plugin'),
					$db->quoteName('element') . '=' . $db->quote('kunena'),
					$db->quoteName('folder') . '=' . $db->quote('jagdpr')
				));

			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}
}