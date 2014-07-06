<?php


namespace Craft;

class QueuePlugin extends BasePlugin
{
	public function getName()
	{
		return Craft::t('Queue');
	}

	public function getVersion()
	{
		return '0.0.1';
	}

	public function getDeveloper()
	{
		return 'Tommy Marshal';
	}

	public function getDeveloperUrl()
	{
		return 'http://github.com/tommymarshall';
	}

	public function hasCpSection()
	{
		return true;
	}

	public function registerCpRoutes()
	{
		return array(
			'reroute\/new' => 'reroute/_form',
			'reroute\/(?P<rerouteId>\d+)' => 'reroute/_form',
		);
	}

	public function init() {
		parent::init();
	}
}