<?php
namespace Craft;

class Mailer_MainService extends BaseApplicationComponent
{

	/**
	 * Create MailerTasks from passed $formData
	 *
	 * @param Mailer_FormModel $formData
	 * @return bool
	 */
	public function newMailer_fromForm(Mailer_FormModel $formData)
	{
		//Create EmailModel
		$email = new EmailModel();

		$email->subject 	= $formData->subject;
		$email->fromName 	= $formData->sender_name;
		$email->fromEmail 	= $formData->sender_mail;
		$email->htmlBody 	= $formData->htmlBody;

		if ($formData->attachment) {
			$email->attachments = $formData->attachment;
		}



		//Custom Mailer
		if ($formData->sendto_custom) {
			//Create recipients array
			$custom_recipients = array(
				array(
					'to' => $formData->to,
					'cc' => $formData->cc,
					'bcc' => $formData->bcc
				)
			);

			//Save to model
			$recipients = new Mailer_RecipientsModel();
			$recipients->recipients = $custom_recipients;

			//Check
			if (!$recipients->validate()) {
				return false;
			}

			//Create mailer
			$this->newMailer($recipients, $email, Craft::t('Emails (Custom)') );

			//Clean
			unset($recipients);
		}


		//Usergroups Mailer
		if ($formData->sendto_usergroups) {
			//Get Recipients
			$usergroup_recipients = $this->getUserGroupRecipients($formData->usergroups, $formData->users);

			if ($usergroup_recipients != null) { //null = All users got excluded

				//Save to model
				$recipients = new Mailer_RecipientsModel();
				$recipients->recipients = $usergroup_recipients;

				//Check
				if (!$recipients->validate()) {
					return false;
				}

				//Create mailer
				$this->newMailer($recipients, $email, Craft::t('Emails (UserGroups)'));

				//Clean
				unset($recipients);
			}
		}


		//Users Mailer
		if ($formData->sendto_users) {
			//Get Recipients
			$user_recipients = $this->getUserRecipients($formData->users);

			//Save to model
			$recipients	= new Mailer_RecipientsModel();
			$recipients->recipients = $user_recipients;

			//Check
			if (!$recipients->validate()) {
				return false;
			}

			//Create mailer
			$this->newMailer($recipients, $email, Craft::t('Emails (Users)'));

			//Clean
			unset($recipients);
		}


		return true;
	}




	/**
	 * Start a MailerTask
	 *
	 * @param Mailer_RecipientsModel $recipients
	 * @param EmailModel $email
	 * @return bool
	 */
	public function newMailer(Mailer_RecipientsModel $recipients, EmailModel $email, $description=null)
	{
		//StartTask
		craft()->tasks->createTask('Mailer', $description, array(
			'recipients' 	=> $recipients,
			'email' 		=> $email
		));


		return true;
	}




	/**
	 * Creates a recipients-array from UserGroup-Ids
	 *
	 * @param array $usergroup_ids
	 * @param array $exlude_user_ids
	 * @return array (Or null if all users got excluded)
	 */
	public function getUserGroupRecipients($usergroup_ids, $exlude_user_ids=array())
	{
		//Get all users from specified usergroups
		$criteria 			= craft()->elements->getCriteria(ElementType::User);
		$criteria->groupId 	= $usergroup_ids;
		$users 				= $criteria->find();


		//Include admins?
		if (in_array('admin', $usergroup_ids)) {
			$admin_criteria 		= craft()->elements->getCriteria(ElementType::User);
			$admin_criteria->admin 	= true;
			$admin_users 			= $admin_criteria->find();

			$users = array_merge($admin_users, $users);
		}


		//Get ids of those users
		$user_ids = array();

		foreach ($users as $user) {
			$user_ids[] = $user->id;
		}


		//Check
		if (count($user_ids) == 0) {
			MailerPlugin::log('getUserGroupRecipients(): No Users found in passed UserGroups. UserGroups IDs: "'. implode(', ', $usergroup_ids) .'"', LogLevel::Error);

			return false;
		}


		//Exclude IDs
		if (!empty($exlude_user_ids)) {
			$user_ids = array_diff($user_ids, $exlude_user_ids);

			if (count($user_ids) == 0) {
				//All users got excluded
				return null;
			}
		}


		//Get recipients-array
		$recipients = $this->getUserRecipients($user_ids);


		//Check
		if (!$recipients) {
			return false;
		}


		return $recipients;
	}


	/**
	 * Creates a recipients-array from User-Ids
	 *
	 * @param array $user_ids
	 * @return array
	 */
	public function getUserRecipients($user_ids)
	{
		//Get all Users by ids
		$criteria 		= craft()->elements->getCriteria(ElementType::User);
		$criteria->id 	= $user_ids;
		$users 			= $criteria->find();


		//Create recipients-array
		$recipients = array();

		foreach ($users as $user) {
			$recipients[] = array(
					'to' => $user->email
				);
		}


		//Check
		if (count($recipients) == 0) {
			MailerPlugin::log('getUserRecipients(): No Users found with IDs: "'. implode(', ', $user_ids) .'"', LogLevel::Error);

			return false;
		}


		return $recipients;
	}


}