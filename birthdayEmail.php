<?php

// Set flag that this is a parent file.
const _JEXEC = 1;

error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_LIBRARIES . '/import.legacy.php';
require_once JPATH_LIBRARIES . '/cms.php';

// Load the configuration
require_once JPATH_CONFIGURATION . '/configuration.php';

/**
 * This script will fetch the update information for all extensions and store
 * them in the database, speeding up your administrator.
 *
 * @since  2.5
 */
class birthdayEmail extends JApplicationCli
{
	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function doExecute()
	{
		$db = JFactory::getDbo();
		$result = null;
/*
SELECT DATE(REPLACE(b.profile_value,'"','')) as bdate,a.id,a.name,a.email FROM `j2y4g_users` as a
 LEFT JOIN `j2y4g_user_profiles` as b ON a.id=b.user_id WHERE profile_key = 'profile.dob' AND profile_value != '' 
 AND profile_value LIKE ( CONCAT('%-',DATE_FORMAT(CURDATE() + INTERVAL 1 DAY,'%m-%d'),' %') )

*/
		$query = $db->getQuery(true)
			->select($db->quoteName('DATE(REPLACE(b.profile_value,\'"\',\'\')) as b.date', 'a.id','a.email','a.name'))
			->from($db->quoteName('#__users as a'))
			->leftjoin($db->quoteName('#__user_profiles as b'))
			->on('a.id=b.user_id')
			->where($db->quoteName(MONTH('profile_key')) . ' = ' . $db->quote('profile.dob'))
			->where($db->quoteName(MONTH('profile_value')) . ' != ""')
			->where($db->quoteName(MONTH('profile_value')) . ' ( CONCAT(\'%-\',DATE_FORMAT(CURDATE() + INTERVAL 1 DAY,\'%m-%d\'),\' %\') ) ');

		$db->setQuery($query);

		try
		{
			$mail = JFactory::getMailer();
			$config = JFactory::getConfig();
			$sender = array(
				$config->get( 'mailfrom' ),
				$config->get( 'fromname' ) 
			);

			$mailer->setSender($sender);
			$results = $db->loadObjectList();
			foreach($results as $result){
				$recipient = $result->email;
				$mailer->addRecipient($recipient);
				$body   = "Hello Dhaval,\n\nHappy birthday in advance, wish you happy and healthy life";
				$mailer->setSubject('Happy Birthday in Advance');
				$mailer->setBody($body);
				$mailer->Send();
			}

		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

	}
}

JApplicationCli::getInstance('Updatecron')->execute();
