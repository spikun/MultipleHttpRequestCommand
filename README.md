MultipleHttpResquestCommand
-----

### Usage

	$command = new MultipleHttpResquestCommand();
	$command->addPostRequest($someKey, $url, array('name' => 'Cassio');
	$command->setMaxRequestsPerExecution(100); //optional
	$data = $command->execute();
	print_r($data[$someKey]); //result
	