<?php
// WebSocketServer implementation in PHP
// by Bryan Bliewert, nVentis@GitHub

class WebSocketClient {
	// get according socket in WebSocketServer using $this->Sockets[$Client->ID]
	public
		$ID,
		$Headers	= null,
		$Handshake	= null,
		$timeCreated= null;
	
	function __construct($Socket){
		$this->ID			= intval($Socket);
		$this->timeCreated	= time();
	}
}

class WebSocketServer {
	public
		$logToFile		= false,
			$logFile	= "log.txt",
		$logToDisplay	= true,
		$Sockets		= array(),
		$bufferLength	= 2048,
		$maxClients		= 20,
		
		// applied with Start()
		$errorReport	= E_ALL,
		$timeLimit		= 0,
		$implicitFlush	= true;
	
	protected
		$Address,
		$Port,
		$socketMaster,
		$Clients = array();
	
	public function Log($M){
		$M = "[". date(DATE_RFC1036, time()) ."] - $M \r\n";
		if ($this->logToFile) file_put_contents($this->logFile, $M, FILE_APPEND); 
		if ($this->logToDisplay) echo $M;
	}
	
	protected function addClient($Socket){
		$ClientID = intval($Socket);
		$this->Clients[$ClientID] = new WebSocketClient($Socket);
		$this->Sockets[$ClientID] = $Socket;
		return $ClientID;
	}
	
	protected function getClient($Socket){
		return $this->Clients[intval($Socket)];
	}

	public function Close($Socket){
		socket_close($Socket);
		$SocketID = intval($Socket);
		unset($this->Clients[$SocketID]);
		unset($this->Sockets[$SocketID]);
		$this->onClose($SocketID);
		return $SocketID;
	}
	
	protected function Handshake($Socket, $Buffer) {
		$SocketID = intval($Socket);
		$magicGUID = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";
		$Headers = array();
		$Lines = explode("\n", $Buffer);
		foreach ($Lines as $Line) {
			if (strpos($Line, ":") !== false) {
				$Header = explode(":", $Line, 2);
				$Headers[strtolower(trim($Header[0]))] = trim($Header[1]);
			} elseif (stripos($Line, "get ") !== false) {
				preg_match("/GET (.*) HTTP/i", $Buffer, $reqResource);
				$Headers['get'] = trim($reqResource[1]);
			}
		}
		
		if (!isset($Headers['host'])
			|| !isset($Headers['sec-websocket-key'])
			|| (!isset($Headers['upgrade']) || strtolower($Headers['upgrade']) != 'websocket')
			|| (!isset($Headers['connection']) || strpos(strtolower($Headers['connection']), 'upgrade') === FALSE))
			$addHeader = "HTTP/1.1 400 Bad Request";
		if (!isset($Headers['sec-websocket-version']) || strtolower($Headers['sec-websocket-version']) != 13)
			$addHeader = "HTTP/1.1 426 Upgrade Required\r\nSec-WebSocketVersion: 13";
		if (!isset($Headers['get']))
			$addHeader = "HTTP/1.1 405 Method Not Allowed\r\n\r\n";     

		if (isset($addHeader)) {
			@socket_write($Socket, $addHeader, strlen($addHeader));
			$this->onError($SocketID, "Handshake aborted - [". trim($addHeader)."]");
			return $this->Close($Socket);
		}

		$Token = "";
		for ($i = 0; $i < 20; $i++) {
			$Token .= chr(hexdec(substr(sha1($Headers['sec-websocket-key'] . $magicGUID), $i * 2, 2)));
		}
		$Token = base64_encode($Token) . "\r\n";

		$addHeader = "HTTP/1.1 101 Switching Protocols\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Accept: $Token\r\n";
		@socket_write($Socket, $addHeader, strlen($addHeader));
		
		$this->Clients[$SocketID]->Headers = $Headers;
		$this->Clients[$SocketID]->Handshake = $Buffer;
		$this->onOpen($SocketID);
	}
	
	protected function Encode($M) {
		// inspiration for Encode() method : http://stackoverflow.com/questions/8125507/how-can-i-send-and-receive-websocket-messages-on-the-server-side
		$L = strlen($M);

		$bHead = [];
		$bHead[0] = 129; // 0x1 text frame (FIN + opcode)

		if ($L <= 125) {
            $bHead[1] = $L;
		} else if ($L >= 126 && $L <= 65535) {
            $bHead[1] = 126;
            $bHead[2] = ( $L >> 8 ) & 255;
            $bHead[3] = ( $L      ) & 255;
		} else {
            $bHead[1] = 127;
            $bHead[2] = ( $L >> 56 ) & 255;
            $bHead[3] = ( $L >> 48 ) & 255;
            $bHead[4] = ( $L >> 40 ) & 255;
            $bHead[5] = ( $L >> 32 ) & 255;
            $bHead[6] = ( $L >> 24 ) & 255;
            $bHead[7] = ( $L >> 16 ) & 255;
            $bHead[8] = ( $L >>  8 ) & 255;
            $bHead[9] = ( $L	   ) & 255;
		}

		return (implode(array_map("chr", $bHead)) . $M);
	}
	
	public function Write($SocketID, $M){
		$M = $this->Encode($M); 
		return @socket_write($this->Sockets[$SocketID], $M, strlen($M));
	}
	
	protected function Decode($M){
		$M = array_map("ord", str_split($M));
		$L = $M[1] AND 127;
		
		if ($L == 126)
			$iFM = 4;
		else if ($L == 127)
			$iFM = 10;
		else
			$iFM = 2;
		
		$Masks = array_slice($M, $iFM, 4);
		
		$Out = "";
		for ($i = $iFM + 4, $j = 0; $i < count($M); $i++, $j++ ) {
			$Out .= chr($M[$i] ^ $Masks[$j % 4]);
		}
		return $Out;
	}
	
	public function Read($SocketID, $M){
		$this->onData($SocketID, $this->Decode($M));
	}
	
	function __construct($Address, $Port){
		$this->socketMaster = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if (!is_resource($this->socketMaster))
			$this->Log("The master socket could not be created: ".socket_strerror(socket_last_error()), true);
		
		socket_set_option($this->socketMaster, SOL_SOCKET, SO_REUSEADDR, 1);
		if (!socket_bind($this->socketMaster, $Address, $Port))
			$this->Log("Can't bind on master socket: ".socket_strerror(socket_last_error()), true);

		if(!socket_listen($this->socketMaster, $this->maxClients))
			$this->Log("Can't listen on master socket: ".socket_strerror(socket_last_error()), true);
		
		$this->Sockets["m"] = $this->socketMaster;
		$this->Log("Server initilaized on $Address:$Port");
	}
	
	public function Start(){
		$this->Log("Starting server...");
		error_reporting($this->errorReport);
		set_time_limit($this->timeLimit);
		if ($this->implicitFlush) ob_implicit_flush();
		
		while (true){
			if (empty($this->Sockets)) $this->Sockets["m"] = $this->socketMaster;
			$socketArrayRead = $this->Sockets;
			$socketArrayWrite = $socketArrayExceptions = NULL;
			// by-ref function, thus we can now iterate over the array
			@socket_select($socketArrayRead, $socketArrayWrite, $socketArrayExceptions, NULL);
			
			foreach($socketArrayRead as $Socket){
				$SocketID = intval($Socket);
				if ($Socket == $this->socketMaster){
					$Client = socket_accept($Socket);
					if (!is_resource($Client)){
						$this->onError($SocketID, "Connection could not be established");
						continue;
					} else {
						$this->addClient($Client);
						$this->onOpening($SocketID);
					}
				} else {
					$receivedBytes = @socket_recv($Socket, $dataBuffer, $this->bufferLength, 0);
					if ($receivedBytes === false) {
					// on error
						$sockerError	= socket_last_error($Socket);
						$socketErrorM	= socket_strerror($sockerError);
						if ($sockerError >= 100){
								$this->onError($SocketID, "Unexpected disconnect with error $sockerError [$socketErrorM]");
								$this->Close($Socket);
						} else {
							$this->onOther($SocketID, "Other socket error $sockerError [$socketErrorM]");
							$this->Close($Socket);
						}
						
					} elseif($receivedBytes == 0) {
					// no headers received (at all) --> disconnect
						$SocketID = $this->Close($Socket);
						$this->onError($SocketID, "Client disconnected - TCP connection lost");
					} else {
					// no error, --> check handshake
						$Client = $this->getClient($Socket);
						$this->Log("Client $SocketID is known - Handshake : " . (($Client->Handshake == false) ? "NO" : "TRUE" ) );
						if ($Client->Handshake == false){					
							if (strpos(str_replace("\r", '', $dataBuffer), "\n\n") === false ) {	// headers have not been completely received --> wait --> handshake
								$this->onOther($SocketID, "Continue receving headers"); continue;
							}
							$this->Handshake($Socket, $dataBuffer);
						} else {
							$this->Read($SocketID, $dataBuffer);
						}
					}
				}
			}
		}
	}
	
	// Methods to be configured by the user; executed directly after...
		function onOpen	($SocketID		)	//...successful handshake
			{ $this->Log("Handshake with socket #$SocketID successful"); }
		function onData	($SocketID, $M	)	// ...message receipt; $M contains the decoded message
			{ $this->Log("Received ". strlen($M) . " Bytes from socket #$SocketID"); }
		function onClose($SocketID		)	// ...socket has been closed AND deleted
			{ $this->Log("Connection closed to socket #$SocketID"); }
		function onError($SocketID, $M	)	// ...any connection-releated error
			{ $this->Log("Socket $SocketID - ". $M); }
		function onOther($SocketID, $M	)	// ...any connection-releated notification
			{ $this->Log("Socket $SocketID - ". $M); }
		function onOpening($SocketID	)	// ...being accepted and added to the client list
			{ $this->Log("New client connecting on socket #$SocketID"); }
}
?>