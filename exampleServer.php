<?php // WebSocket Server Example

include ("WebSocketServer.php");
$Address	= "127.0.0.1";
$Port		= 8080;

class customServer extends WebSocketServer{
	function onData($SocketID, $M){
		$this->Log("Client #$SocketID > $M");
		$this->Write($SocketID, $M);
	}
}
$customServer = new customServer($Address, $Port);
$customServer->Start();
?>