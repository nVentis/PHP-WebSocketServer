# PHP-WebSocketServer
An easy-to-use WebSocket server class implemented in PHP.

# How to use
The main file WebSocketServer.php has to be included in your PHP file. After that, create a custom class which extends to WebSocketServer. For a test implementation, see exampleServer.php. Make sure to run the script in the PHP command-line interface.

The following list shows possible class properties ( property : [type] - [default] -   )

1. Public WebSocketServer properties
  logToFile   : [Boolean] - [false]
  logFile     : [String]  - [log.txt] - path to logfile; only used if logToFile = true
  logToDisplay: [Boolean] - [true]    - if true, Log() output will be displayed in console
  bufferLength: [Number]  - [2048]    - number of bytes to be read from each socket
  maxClients  : [Number]  - [20]      - number of sockets / clients to be opened simultaneously maximum
  errorReport   : [Number]  - [E_ALL] 
  timeLimit     : [Number]  - [0]
  implicitFlush : [Boolean] - [true]

2. Public WebSocketServer methods
