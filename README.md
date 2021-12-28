# ciLogger
CodeIgniter Logger w/ json output functionality

Drop this in applications folder, will include file logger.php in config and Logger.php in libraries

Include in your autoload libraries in config/autoload.php

    $autoload['libraries'] = array( 'Logger');

Rename it if you want

     $autoload['libraries'] = array( 'Logger'=>'log' );

Mess with the config/logger.php 

     /*
     |--------------------------------------------------------------------------
     | Error Log Config
     |--------------------------------------------------------------------------
     |	log_directory - if blank will default to /tmp/
     | 	log_name = if blank will default to application
     |	log_extension = if blank will default to .log  DONT INCLUDE DOT in def
     |	log_include_fileName - Include filename of caller
     |	log_include_trace - Include trace of the caller, class:function:line
     |	log_json_pretty_print - If true, will dump arrays as json in pretty formatted style, else will just dump straight json to log 
     |		(Any arrays sent to log( ) are automatically json_encoded ).
     |	log_date_in_filename - Adds Y-m-d to end of filename, before extension
     |	log_all_in_json - Outputs the whole log in json format with keys: [level, timestamp, ip, traceFileName, traceCaller, data].  
     |		NOTE - this also uses the log_json_pretty_print to format output except it applies to the entire output.
     |		ALSO NOTE - json output disables the mark( ) and line( ) functions
     */



NOTE! if You want to use the json version, set 
log\_all\_in\_json = true

#USING
     $this->logger->log( "Write something to the log" );
     $this->logger->mark( );    // OUTPUT ### MARK ### to the log, because mark's a great dude, no actually just a handy way to get a quick sanity check
     $this->logger->line( );    // OUTPUT ------------------------------ for all those visually oriented people out there. 

mark and line are disabled in json mode.

You can feed log( ) an array, that's no problem, it will just output the array as json on a new line from the log:
	
	$this->logger->log( array("key"=>"val" ));
	// [2021-12-31 23:59:59 - 1.1.1.1] #
	// { 
	//		key: val
	// }

You can turn the pretty print on or off with the config line:

	$log_json_pretty_print = false;

ALSO - important, I left the write( ) function public, so you can actually directly write to the log.
  
     $this->logger->write( "Some text" ) ;
     

And you can also use
 
     $this->logger->writeJson( array( 'stuff', 'to', 'output' ));


Second param to write it the filename:

	$this->logger->write( "Some text", "altLog" );
	// cat altLog.log
	// [2021-12-31 23:59:59 - 1.1.1.1] # Some text

# JSON MODE
I mentioned it above but you can enable JSON mode in the logger.php config file:

     $log_all_in_json = true;

This will output everything in JSON.  Obviously.  Here's a sample:

     {
         "level": "[TEST]",
         "timestamp": "2021-12-31 23:59:59",
         "ip": "1.1.1.1",
         "traceFileName": "\/var\/www\/html\/\/application\/controllers\/logTest.php",
         "traceCaller": "logTest_model:logTest:230",
         "data": Some Data for JSON Mode
     }

And if you write( ) an array, it will JSON encode the array: 
	{
		"data": [
			1, 
			2, 
			3
		]
	}

You can turn off the pretty printer in the config:

	$log_json_pretty_print = false;

NOTE - This variable does double duty.  In regular mode, it will enable/disable pretty printing of arrays in the flat text log:

	[2021-12-31 23:59:59 - 1.1.1.1] #
	{[1,2,3,4,5]}

AND in json mode, will enable or disable pretty print on the whole thing.  Which I dont want to reproduce so just use your imagination. 

#INCLUDING LEVEL
You can include a log level, that's your optional second param in the log function.  It doesn't do anything in this end, but you can use it in any reading stuff, like regex filter.  you can make the level anything you want.  

     $this->logger->log( "Write something to the log", 'INFO' );
     //[INFO] [2021-12-31 23:59:59 - 1.1.1.1] Write something to the log


In the JSON mode,  this value just gets stuck into the array passed to json_encode under key "level"

     "level": "[INFO]",

Yeah I was too lazy to filter out the brackets.

#SPECIFIC FILENAME
You can override the filename temporarily by adding in a third parameter to the log function, or the second parameter to the write function.  Just remember in the log function that the level is second param so feed it null and then give the name:

     $this->logger->log( "Some text", null, 'log2' );
     // Will output to /wherever/log2.log

Note that the extension and the save directory will remain the same as what you used to configure the thing initially. 

# RECONFIG
I made the constructor wrap a public config function, so you can change any of the things you want before writing a log.  Of course, remember to change them back afterwards.  I might make it so you can do a reconfig, write and reverse, or maybe just something to restore intitial state later on.  

	$this->logger->config( array( "log_include_trace"=>false, "log_include_fileName"=>false ));
	$this->logger->log( "Log without trace info" );
	// [2021-12-31 23:59:59 - 1.1.1.1] # Log without trace info

    $this->logger->config( array( "log_include_trace"=>true, "log_include_fileName"=>true ));
    $this->logger->log( "Log WITH trace info" );
	// [2021-12-31 23:59:59 - 1.1.1.1] /var/www/html/application/controllers/logTest.php - logTest_model:logTest:237 # Log WITH trace info

Theoretically this lets you make multiple loggers with different configs.  Not sure exactly how to do that in CI but I'm sure its  not that tough.  Maybe even a factory, that might be cool, multiple logs writing out based on the level with different formats.  


#TRACE
Optionally can include the caller information.  You can include the filename and the class:function:lineNumber.  Php a little funky with its trace stack (Python > PHP) but it gets the job done. 

      /var/www/html/application/controllers/logTest.php - logTest_model:logTest:237 # Write something to the log

Here it is in JSON:

    "traceFileName": "\/var\/www\/html\/application\/controllers\/logTest.php",
    "traceCaller": "logTest_model:logTest:237",
  
You can disable one or both of these in the config.php:

	$log_include_fileName = false;
    $log_include_trace = false; 

#Other tidbits
Enable including the date in filenames:

    $log_date_in_filename = true;
	// somefile_2012-12-31.log


If you dont specify the filename and path in config file, it will default to 

     /tmp/application.log 

Just to be safe because we can always write to /tmp/ 

## The timestamp and ip address
Perhaps you've noticed in all examples the New Year's joy in all of the log outputs...yeah it will always include that.  I didn't see a reason to disable.  And its not quite new years but it's close enough and I'm working today so wtf why not.  

There is also an IP address.  This will grab the remote IP from $_SERVER.  Or if you are running from the CLI it will instead grab the username and hostname (it uses exec whoami and hostname, which should work on a modern windows machine.  I'd test it but I have gnutools in my path so that might skew the results.  ymmv). 

You can't turn off either of these options.  Perhaps next revision I'll make them optional.  
Anyways, in the JSON version, they come through with their own keys:
	
    "timestamp": "2021-12-31 23:59:59",
    "ip": "1.1.1.1",

OR in CLI mode:
	
	"ip": "ubuntu@myHost.com"


