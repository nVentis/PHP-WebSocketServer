# PHP-WebSocketServer
An easy-to-use WebSocket server class implemented in PHP.

# How to use
The main file WebSocketServer.php has to be included in your PHP file. After that, create a custom class which extends to WebSocketServer. For a test implementation, see exampleServer.php. Make sure to run the script in the PHP command-line interface. An example on Windows with XAMPP could be
* D:\XAMPP\php\php.exe -f "D:\XAMPP\htdocs\PHP-WebSocketServer\example.php" .

The following list shows possible class properties ( property : [type] - [default] [- Comment]   )

**1. Methods**

Name    | Arguments     | Comment
--------|---------------|--------------------------------------------------------------------------------
Log     | $M            | displays $M in the console and / or saves $M in the logfile, depending on logTo
Start   |               | starts the server; until then, any option can still be configured
Close   | $Socket       | closes a connection and cleans up the Clients + Sockets arrays
Write   | $SocketID, $M | automatically encode $M and sends it to socket $SocketID


**2. Properties**

  Name         | Type    | Default | Comment
  -------------|---------|---------|-----------------------------------------------------------------
  Sockets      | Array   | array() | contains every sockets as follows : $SocketID => $Socket
  logToFile    | Boolean | false   |
  logFile      | String  | log.txt | path to logfile; only used if logToFile = true
  logToDisplay | Boolean | true    | if true, Log() output will be displayed in console
  bufferLength | Number  | 2048    | number of bytes to be read from each socket
  maxClients   | Number  | 20      | number of sockets / clients to be opened simultaneously maximum
  errorReport  | Number  | E_ALL   |
  timeLimit    | Number  | 0       |
  implicitFlush| Boolean | true    |

3. Custom methods - the following methods may be changed by your extending class; they are executed directly after...
  * onOpen ($SocketID)
    * ...a successul handshake
  * onData ($SocketID, $M)
    * ...a message ($M) has been received (on socket $SocketID)
  * onClose ($SocketID)
    * ...a connection to a socket has been closed
  * onError ($SocketID, $M)
    * ...a critical error; $M includes additional information; connection to the socket has already been closed
  * onOther ($SocketID, $M)
    * ...a non-critical warning; the connection is still active
  * onOpening($SocketID)
    * ...a socket has been accepted and added to the public Sockets and private Clients array


First Header | Second Header
-------------|-------------
Content Cell |  Content Cell
Content Cell |  Content Cell
