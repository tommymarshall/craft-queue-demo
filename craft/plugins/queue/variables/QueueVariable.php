<?php
namespace Craft;

class QueueVariable
{
	public function marshall()
	{
		$response = craft()->queue->marshall();

		QueuePlugin::log('Response: ' . $response, LogLevel::INFO);

		return $response;
	}
}