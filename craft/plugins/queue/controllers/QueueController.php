<?php

namespace Craft;

class QueueController extends BaseController
{

	protected $allowAnonymous = false;


	public function actionTest()
	{
		//Is CpRequest?
		if (craft()->request->isCpRequest() == false)
		{
			return false;
		}


		//Init
		$this->requirePostRequest();

		//Service
		$queue_service = craft()->queue->run();

		if ($queue_service)
		{
			//Success
			if (craft()->request->isAjaxRequest())
			{
				return $this->returnJson( array('success' => true) );
			}
			else
			{
				craft()->userSession->setNotice( Craft::t('The Mailer has successfully started') );
				return true;
			}
		}
		else
		{
			//Something has gone wrong
			craft()->userSession->setError( Craft::t('There was an internal error, please check the logs for more information!') );
		}
	}
}