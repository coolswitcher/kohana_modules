<?php defined('SYSPATH') OR die('No direct script access.');
class Finder_Filter_Iterator extends RecursiveFilterIterator {

	public static $config = NULL;

	public function accept()
	{
		$current_file = $this->current()->getRealPath();
		$filename     = $this->current()->getFilename();
		$path         = $this->current()->getPath();

		$include_names = Arr::get(Finder_Filter_Iterator::$config, 'include_names');
		$exclude_names = Arr::get(Finder_Filter_Iterator::$config, 'exclude_names');
		$include_paths = Arr::get(Finder_Filter_Iterator::$config, 'include_paths');
		$exclude_paths = Arr::get(Finder_Filter_Iterator::$config, 'exclude_paths');
		$types		   = Arr::get(Finder_Filter_Iterator::$config, 'types');
		$hidden_files  = Arr::get(Finder_Filter_Iterator::$config, 'hidden_files', FALSE);		

		if ( $this->current()->isFile() )
		{			
			if ($hidden_files !== TRUE AND preg_match ('#^\..*#', $filename))
				return FALSE;

			if ($include_names)
				return preg_match ($include_names, $filename);

			if ($exclude_names)
				return !preg_match ($exclude_names, $filename);

			if ($include_paths)
				return preg_match ($include_paths, basename($path));

			if ($exclude_paths)
				return ! preg_match ($exclude_paths, basename($path));						  	
			
			if ($types) {
				$types = "#.*\.(" . implode ('|', $types) . ")$#iu";
				return preg_match ($types, $filename);
			}

			return TRUE;

		}	  	  	
	  	
	  	return TRUE;
	  			  
	}
}