<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Run process via pipes
 */
class Kohana_Process {

	const READ_LENGTH       = 1024;
	const STDIN             = 0;
	const STDOUT            = 1;
	const STDERR            = 2;
	
	protected $exitcode     = NULL;
	protected $pipes        = NULL;	
	protected $process      = NULL;
	public static $callback = NULL;

	protected function __construct ( $cmd )
	{
		$this->pipes = (array) null;
		$descriptor = [
			["pipe", "r"], // in
			["pipe", "w"], // out
			["pipe", "w"]  // err
		];
		
		$this->process = proc_open(
			$cmd,
			$descriptor,
			$this->pipes,
			null,
			null,
			['bypass_shell'=>TRUE]
		);
		
		//Set STDOUT and STDERR to non-blocking 
		stream_set_blocking($this->pipes[Kohana_Process::STDOUT], 0);
		stream_set_blocking($this->pipes[Kohana_Process::STDERR], 0);
			
		$process_info = proc_get_status($this->process);
		
		if ($process_info['running'] === FALSE)
		{
			return FALSE;
		}
		
		return $process_info;		
	}

	public static function run_process ($cmd, $pipe = 'stdout') {
		$pipe = constant('Kohana_Process::' . strtoupper($pipe));
		$process = new Kohana_Process ($cmd);
		if ($process) 
		{
			$data = [];
			while ($process->is_active())
			{
				$data = $process->get_status ($pipe);
				if (Kohana_Process::$callback)
				{
					call_user_func(Kohana_Process::$callback, $data);
				}				
			}
			$data['exitcode'] = $process->exitcode;
			call_user_func(Kohana_Process::$callback, $data);
		}
	}

	protected function get_status ($pipe_num)
	{		
		$data = [];				
		$buffer          = $this->_fillbuffer ($pipe_num);		
		$data['buffer']  = $buffer;		
		$last_line       = array_pop ($buffer);
		$data['message'] = $last_line;
			
		if ( !$this->is_active())
		{			
			$data['exitcode'] = $this->exitcode;		
		}		
		
		return $data;
	}

	public function is_active ()
	{
		$process_info = proc_get_status($this->process);
		if (Arr::get($process_info, 'running'))
			return TRUE;
		else {
			$this->exitcode = Arr::get($process_info, 'exitcode');
			return FALSE;
		}	
	}	

	private function _fillbuffer ( $pipe_num ) {
		$buffer = [];
		$pipes = [Arr::get($this->pipes, $pipe_num)];
		
		if (feof(Arr::get($pipes, 0)))
			return FALSE;
		
		$ready = stream_select ($pipes, $write, $ex, 1, 0);
		
		if ($ready === FALSE)
			return FALSE;
		elseif ($ready === 0 ) { 
			return $buffer; // will be empty
		}
				
		$status = ['unread_bytes' => 1];
		$read = TRUE;
		while ( $status['unread_bytes'] > 0 ) {
			$read = fread(Arr::get($pipes, 0), Kohana_Process::READ_LENGTH);
			if ($read !== FALSE) {
				$buffer[] = trim($read);
			}			
			$status = stream_get_meta_data(Arr::get($pipes, 0));
		}		
		return $buffer;
	}	
}