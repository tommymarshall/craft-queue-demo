<?php
namespace Craft;

class QueueVariable
{
	public function marshal()
	{
		$response = craft()->queue->marshal();

		return $response;
	}
}