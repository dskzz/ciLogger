<?php

declare(strict_types=1);

class Logger
{
	private 		$_ci;
	protected $logLocation;
	protected $logName;
	protected $logExtension;
	private $_defaultName = 'application';
	private $_defaultLocation = '/tmp/';
	private $_defaultExtension = 'log';
	protected $logIncludeTrace;
	protected $logIncludeFileName;
	protected $logDateInFilename;
	protected $logAllInJson;


    public function __construct( $config  ) {
		$this->_ci = & get_instance();
		
		$this->config( $config );

	}

	public function config( $config )
	{
		if ( isset( $config[ "log_name" ] ) && !empty( $config[ "log_name" ] ))
			$this->logName = $config[ "log_name" ];
		else{
			// if we try to reconfig on the fly we want to make sure this isnt clobbered
			if( !$this->logName )
				$this->logName = $this->_defaultName;
		}
	
			
		if ( isset( $config[ "log_directory" ] ) && !empty(  $config[ "log_directory" ] ))
			$this->logLocation = $config[ "log_directory" ];
		else{
			// if we try to reconfig on the fly we want to make sure this isnt clobbered
			if( !$this->logLocation)
				$this->logLocation = $this->_defaultLocation;
		}
	
		if ( !empty( $config[ "log_extension" ] )){
			$this->logExtension = $config[ "log_extension" ];			
		}
		else
		{
			if( !$this->logExtension )
				$this->logExtension = $this->_defaultExtension;
		}

		if ( isset( $config[ "log_include_fileName" ] ))
			$this->logIncludeFileName = $config[ "log_include_fileName" ];			

		if ( isset( $config[ "log_include_trace" ] ))
			$this->logIncludeTrace = $config[ "log_include_trace" ];	

		if( isset( $config[ "log_json_pretty_print" ] ))
			$this->logJsonPretty = $config[ "log_json_pretty_print" ];	

		if( isset( $config[ "log_date_in_filename" ] ))
			$this->logDateInFilename = $config[ "log_date_in_filename" ];	
	
		if( isset( $config[ "log_all_in_json" ] ))
			$this->logAllInJson = $config[ "log_all_in_json" ];	
	}


	public function log( $str , $level = null, $fileName=null )
	{
		
		$trace = $this->get_calling_info();
		
		$trace['class'];
		$trace['line'];
		$trace['function'];
		$trace['file'];
		$ip = $this->getIp();
		$ts = $this->ts( );

		$levelActual = '';
		if( $level ) $levelActual = "[$level] ";
		

		$multiLine = '';

		$jsonParam = 0;
		if( $this->logJsonPretty ) $jsonParam = JSON_PRETTY_PRINT;

		if( is_array( $str ) || is_object( $str )){
			$multiLine = "\n";
			$outStr = json_encode( $str, $jsonParam );
		}

		$traceFileName = "$trace[file] - ";
		$traceInfo = "$trace[class]:$trace[function]:$trace[line] ";
		if( $this->logIncludeFileName == false ) $traceFileName ='';
		if( $this->logIncludeTrace == false ) $traceInfo ='';

		if( $this->logAllInJson )
		{
			$this->writeJson( array( 'level'=>$levelActual, 'timestamp'=>$ts, 'ip'=>$ip, 'traceFileName'=>$traceFileName, 'traceCaller'=>$traceInfo, 'data'=>$str ), $fileName );
		}
		else
		{
			$this->write( 
				"$levelActual"."[$ts - $ip] $traceFileName$traceInfo# $multiLine$outStr\n\n" , $fileName );
		}
	}

	public function writeJson( $writeArr, $fileName=null )
	{
		$jsonParam = 0;
		if( empty( $writeArr['traceFileName'] )) unset( $writeArr['traceFileName'] );
		if( empty( $writeArr['traceCaller'] )) unset( $writeArr['traceCaller'] );
		if( empty( $writeArr['level'] )) unset( $writeArr['level'] );


		if( $this->logJsonPretty ) $jsonParam = JSON_PRETTY_PRINT;
		$str = json_encode( $writeArr, $jsonParam )."\n\n";
		$this->write( $str, $fileName );
	}

	public function mark( $fileName = null )
	{
		if(  $this->logAllInJson ) return;	//sort of pointless if we are jsoning everything
		$this->write( 
			"###MARK###\n\n", $fileName );
	}

	public function line( $fileName = null )
	{
		if(  $this->logAllInJson ) return;	//sort of pointless if we are jsoning everything
		$this->write( 
			"----------------------------------------------------------\n\n", $fileName );
	}



	public function write( $line, $fileName = null )
	{
		$file = $this->prepareFileName( $fileName );
		file_put_contents( $file, $line , FILE_APPEND );
	}

	function prepareFileName( $fileName = null )
	{
		if ( strcmp( $this->logLocation[-1], "/" ))
			$this->logLocation .= '/';

		$fileNameActual = $this->logName;
		if( $fileName ) 
			$fileNameActual = $fileName;

		if( $this->logDateInFilename )
		{
			$fileNameActual .= '_'. $this->ts("Y-m-d");
		}

		if( $this->logExtension && (!stristr( $fileNameActual, ".".$this->logExtension )))
			$fileNameActual = "$fileNameActual.$this->logExtension";
	
		return $this->logLocation . $fileNameActual;
	}

	function getIp( )
	{
		if( !is_cli( ))
			$ip = isset( $_SERVER['REMOTE_ADDR'] ) ?  $_SERVER['REMOTE_ADDR'] : '';
		else
			$ip = exec("whoami") ."@".exec("hostname");
		
		return $ip;
	}

	function ts( $format = "Y-m-d h:i:s" )
	{
		$date = new DateTime();
		$date->setTimezone ( new DateTimeZone( 'America/New_York' ));
		return $date->format( $format );
	}

	function get_calling_info() {

		//get the trace
		$trace = debug_backtrace();
	
		// Get the class that is asking for who awoke it
		$class = $trace[1]['class'];
	
		// +1 to i cos we have to account for calling this function
		for ( $i=1; $i<count( $trace ); $i++ ) {
			if ( isset( $trace[$i] ) ) // is it set?
				 if ( $class != $trace[$i]['class'] ) // is it a different class
					 return $trace[$i];
		}
	}

	function dumpState( )
	{
		print "\n\n-----------------------------------------------------------\n";
		print 'logLocation -> '.  $this->logLocation ."\n";
		print 'logName -> '.  $this->logName ."\n";
		print 'logExtension -> '.  $this->logExtension ."\n";
		print 'logIncludeTrace -> '.  $this->logIncludeTrace ."\n";
		print 'logIncludeFileName -> '.  $this->logIncludeFileName ."\n";
		print "-----------------------------------------------------------\n\n\n";
	}
}
