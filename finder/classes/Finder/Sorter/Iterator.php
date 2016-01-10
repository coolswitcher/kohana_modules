<?php defined('SYSPATH') OR die('No direct script access.');

/* Sorter Iterator */
class Finder_Sorter_Iterator extends SplHeap {

  private $sortby;
  private $asc;

  public function __construct (Iterator $iterator, $sortby, $asc)
  {
	$this->sortby = $sortby;
	$this->asc    = $asc;
    foreach ($iterator as $item)
    {
    	$this->insert($item);
    }
  }

  public function compare($b, $a)
  {     
	switch ($this->sortby)
	{
		case 'name':
		default:
		  	if ($this->asc)
		  		$r = strcmp($a->getPathname(), $b->getPathname());
		  	else
		  		$r = !strcmp($a->getPathname(), $b->getPathname());
		break;

		case 'date':
		  	if ( $this->asc )
		  		$r = intval ( $a->getMTime() >= $b->getMTime() );
		  	else
		  		$r = intval ( $a->getMTime() < $b->getMTime() );
		  	if ( $r === 0)
		  		$r = -1;
		break;        	
	}
	return $r;
  }
}
