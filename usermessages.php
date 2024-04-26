<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.UserMessages
 *
 * @copyright   Copyright (C) NPEU 2020.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

/**
 * Display various messages to users when certain events occur.
 */
class plgSystemUserMessages extends JPlugin
{
    protected $autoloadLanguage = true;

    /**
     * @param   array  $options  Array holding options
     *
     * @return  boolean  True on success
     */
    public function onUserAfterLogin($options)
    {
        // Default login success message:
        JFactory::getApplication()->enqueueMessage(JText::_('PLG_SYSTEM_USERMESSAGES_LOGGED_IN_MESSAGE'));

        // Staff login messages:
        if (in_array(10, $options['user']->getAuthorisedGroups())) {
            // This user is in the staff group. Assumed to be a staff member.
            // Get the profile data:
            jimport('joomla.user.helper');
            $user         = JFactory::getUser();
            $user_id      = $user->id;
            $user_profile = JUserHelper::getProfile($user_id);

            // If the user has never saved their profile, then the staffprofile.x rows won't exist,
            // and the user shouldn't be able to save it without filling in the mandatory fields.
            // Note alias, gravatar and avatar are automatically generated if not found, so we can't
            // use those. So, checking for role and strapline as these are compulsory.
            if (empty($user_profile->profile['role']) || empty(trim($user_profile->profile['biography']))) {
                JFactory::getApplication()->enqueueMessage(JText::_('PLG_SYSTEM_USERMESSAGES_STAFF_PROFILE_MESSAGE'), 'notice');
            }

        }
    }

    /**
	 * Check for the logout cookie and display the message.
     * Note this might be a bit of a hack, this file:
     * plugins/system/logout/logout.php
     * sets a cookie onUserLogout called 'PlgSystemLogout'
     * The value of this cookie appears to always be NULL unless this method has been run, and it
     * only appears to get run once, and it's value is then an empty string for that one request
     * only. Also note that because of the redirect it's not possible to use enqueueMessage inside
     * a different onUserLogout method in this plugin (at least I think that's what's going on).
	 *
	 * @return  void
     *
	 * @throws  InvalidArgumentException
	 */
     public function onAfterInitialise()
     {
        $app = JFactory::getApplication();
        if ($app->isClient('administrator')) {
            return; // Don't run in admin
        }

        $c = $app->input->cookie->get(JApplicationHelper::getHash('PlgSystemLogout'));
        if (!is_null($c))
        {
            $app->enqueueMessage(JText::_('PLG_SYSTEM_USERMESSAGES_LOGGED_OUT_MESSAGE'), 'notice');
        }
     }
}