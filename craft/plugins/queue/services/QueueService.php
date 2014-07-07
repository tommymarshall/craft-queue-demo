<?php

namespace Craft;

require CRAFT_PLUGINS_PATH . 'queue/illuminate-queue/vendor/autoload.php';

use \Illuminate\Queue\Capsule\Manager as Queue;

class SendEmail {

	public function __construct()
	{
		QueuePlugin::log('CONSTRUCT!', LogLevel::Info, true);
	}

	public function fire($job, $data)
	{
		QueuePlugin::log('Pretend to send email with data (INFO): ' . $data['message'], LogLevel::Info, true);
	}

}

class QueueService extends BaseApplicationComponent
{

	protected $queue;

	public function __construct()
	{
		$this->queue = new Queue;

		$this->queue->addConnection([
			'driver'  => 'iron',
			'host'    => 'mq-aws-us-east-1.iron.io',
			'token'   => 'UzwOIEHJKn6t4K94w549v82NGlA',
			'project' => '51bf756c2267d85e00000a42',
			'queue'   => 'my_queue',
			'encrypt' => true,
		]);

		$this->queue->setAsGlobal();

		$this->queue->getContainer()->bind('encrypter', function() {
			return new \Illuminate\Encryption\Encrypter('foobar');
		});

		$this->queue->getContainer()->bind('request', function() {
			return new \Illuminate\Http\Request();
		});
	}

	public function run()
	{
		$message = 'This is my awesome message';
		Queue::push('SendEmail', array('message' => $message));
	}

	public function marshal()
	{
		Queue::marshal();
	}
}