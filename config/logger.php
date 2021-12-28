<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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

$config[ 'log_directory' ] = '/var/www/html/application/logs/';

$config[ 'log_name' ] = '';

$config[ 'log_extension' ] = 'log';

$config['log_include_fileName'] = true;

$config['log_include_trace'] = true;

$config['log_json_pretty_print'] = true;

$config['log_date_in_filename'] = false;

$config['log_all_in_json'] = false;
