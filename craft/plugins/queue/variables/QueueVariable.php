<?php
namespace Craft;

class QueueVariable
{
	public function marshall()
	{
		$response = craft()->queue->marshall();

		return $response;
	}
}