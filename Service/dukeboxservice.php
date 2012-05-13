<?php
error_reporting(1);
ini_set('display_errors', '1');

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
$max_clients = 10;

socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socket, 0, 3783);
socket_listen($socket, $max_clients);

$clients = array('0' => array('socket' => $socket));

while(TRUE)
{
	$read[0] = $socket;

	for($i=1; $i<count($clients) + 1; ++$i)
	{
		if($clients[$i] != NULL)
		{
			$read[$i+1] = $clients[$i]['socket'];
		}
	}

	$ready = socket_select($read, $write = NULL, $except = NULL, $tv_sec = NULL);

	if(in_array($socket, $read))
	{
		for($i=1; $i < $max_clients+1; ++$i)
		{
			if(!isset($clients[$i]))
			{
				$clients[$i]['socket'] = socket_accept($socket);

				socket_getpeername($clients[$i]['socket'],$ip);

				$clients[$i]['ipaddy'] = $ip;

				socket_write($clients[$i]['socket'], 'Welcome to my Custom Socket Server'."\r\n");
				socket_write($clients[$i]['socket'], 'There are '.(count($clients) - 1).' client(s) connected to this server.'."\r\n");

				echo 'New client connected: ' . $clients[$i]['ipaddy'] .' ';
				break;
			}
			elseif($i == $max_clients - 1)
			{
				echo 'To many Clients connected!'."\r\n";
			}

			if($ready < 1)
			{
				continue;
			}
		}
	}

	for($i=1; $i<$max_clients+1; ++$i)
	{
		if(in_array($clients[$i]['socket'], $read))
		{
			$data = @socket_read($clients[$i]['socket'], 1024, PHP_NORMAL_READ);

			if($data === FALSE)
			{
				unset($clients[$i]);
				echo 'Client disconnected!',"\r\n";
				continue;
			}

			$data = trim($data);

			if(!empty($data))
			{
				if($data == 'exit')
				{
					socket_write($clients[$i]['socket'], 'Thanks for trying my Custom Socket Server, goodbye.'."\n");
					echo 'Client ',$i,' is exiting.',"\n";
					unset($clients[$i]);
					continue;
				}

				for($j=1; $j<$max_clients+1; ++$j)
				{
					if(isset($clients[$j]['socket']))
					{
						if(($clients[$j]['socket'] != $clients[$i]['socket']) && ($clients[$j]['socket'] != $socket))
						{
							echo($clients[$i]['ipaddy'] . ' is sending a message!'."\r\n");
							socket_write($clients[$j]['socket'], '[' . $clients[$i]['ipaddy'] . '] says: ' . $data . "\r\n");
						}
					}
				}
				break;
			}
		}
	}
}