<?php

namespace Craft;

require CRAFT_PLUGINS_PATH . 'queue/illuminate-queue/vendor/autoload.php';

use \Illuminate\Queue\Capsule\Manager as Queue;

class SendEmail {

	public function fire($job, $data)
	{
		QueuePlugin::log('Pretend to send email with data: ' . $data['message'], LogLevel::Info);

		// multiple recipients
		$to  = 'tommy.marshall@viget.com'; // note the comma

		// subject
		$subject = 'Birthday Reminders for August';

		// message
		$message = '
		<html>
		<head>
		  <title>Birthday Reminders for August</title>
		</head>
		<body>
		  <p>Here are the birthdays upcoming in August!</p>
		  <table>
		    <tr>
		      <th>Person</th><th>Day</th><th>Month</th><th>Year</th>
		    </tr>
		    <tr>
		      <td>Joe</td><td>3rd</td><td>August</td><td>1970</td>
		    </tr>
		    <tr>
		      <td>Sally</td><td>17th</td><td>August</td><td>1973</td>
		    </tr>
		  </table>
		</body>
		</html>
		';

		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		// Additional headers
		$headers .= 'To: Tommy Marshall <tommy.marshall@viget.com>' . "\r\n";
		$headers .= 'From: Birthday Reminder <no-reply@test.com>' . "\r\n";

		// Mail it
		mail($to, $subject, $message, $headers);
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