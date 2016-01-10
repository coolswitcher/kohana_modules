<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Search files, filter by types, sort items, ignore or not hidden files
 */
class Finder_Core {
	
	private $dit = NULL;
	private $max_depth = -1;
	private $filter;

	public function __construct ($path, $depth = -1)
	{
		$this->dit       = new RecursiveDirectoryIterator ($path);
		$this->max_depth = $depth;
	}

	public static function factory ($path, $depth = -1)
	{
		return new Finder_Core ($path, $depth);
	}

	/*
	Public methods	
	 */	
	/*
	Get sorted objects by name, or by date	
	 */
	public function get_sorted ($sortby = 'name', $asc = TRUE, $attrs = NULL) {
		$rit = $this->_get_rit();
		$sit = new Finder_Sorter_Iterator ($rit, $sortby, $asc);
		return $this->_get($sit, $attrs);
	}

	/*
	Get objects without sorting	
	*/
	public function get_all ( $attrs = NULL ) {
		$rit = $this->_get_rit();
		return $this->_get ($rit, $attrs);
	}

	public function set_filter ( $option, $value = NULL )
	{
		if ($option)
		{
			switch ($option)
			{
				case 'types':
					if (is_string ($value) && strpos ($value, ','))
					{
						$value = explode (',', $value);				
					}
					$value = (array) $value;
				break;
				case 'hidden_files':	
					$value = (bool) $value;
				break;
				default:
					$value = (string) $value;
				break;		
			}
			$this->filter[$option] = $value;
		}
		return $this;
	}			

	/*
	Private methods	
  	*/
	private function _get_rit ()
	{
		$config    = (array) Kohana::$config->load('finder');
		$config    = Arr::merge ($config, $this->filter);
		Finder_Filter_Iterator::$config = $config;
		$this->dit = new Finder_Filter_Iterator ($this->dit);
		$rit       = new RecursiveIteratorIterator ($this->dit, RecursiveIteratorIterator::SELF_FIRST);
		$rit->setMaxDepth ($this->max_depth);
		return $rit;		
	}
	
	/**
	 * [Generic get objects by RiT]
	 * @param  RecursiveIteratorIterator $rit
	 * @param  mixed                     $attrs
	 * @return array                     $files
	 */
	private function _get ( $rit, $attrs = NULL )
	{
		if ( !empty ($rit) )
		{
			$files = array ();
			
			if ( is_string ($attrs) && strpos ( $attrs, ',') ) {
				$attrs = explode (',', $attrs);
			}

			$attrs = (array) $attrs;
			
			foreach ($rit as $filePath => $fileInfo)
			{
				if ($fileInfo->isFile())
				{
					if (empty ($attrs))
					{
						$files[] = $fileInfo->getRealPath ();
					}
					else
					{
						$file_attr = array ();
						$file_attr['realpath'] = $fileInfo->getRealPath ();
						
						foreach ( $attrs as $attr) {
							$attr = trim ($attr);
							$method = 'get' . mb_convert_case( $attr, MB_CASE_TITLE);
							if ( method_exists ( $fileInfo, $method ) )
								$file_attr[$attr] = call_user_func ( array ( &$fileInfo, $method ) );
							switch ($attr)
							{
								case 'owner':
									$file_attr[$attr] = posix_getpwuid ($file_attr[$attr]);
								break;
								case 'group':
									$file_attr[$attr] = posix_getgrgid ($file_attr[$attr]);
								break;																								
								case 'perms':
									$file_attr[$attr] = substr ( sprintf ('%o', $file_attr[$attr]), -4);
								break;																								
							}
						}
						$files[] = $file_attr;
					}
				}
			}
			return $files;			
		}
		return FALSE;
	}	
}