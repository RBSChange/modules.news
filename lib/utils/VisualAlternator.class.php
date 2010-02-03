<?php
/**
 * @date 8 févr. 08 15:01:49
 * @author franck.stauffer
 * @package modules.news
 */

class news_VisualAlternator
{
	const LEFT_ONLY = 'left';
	const RIGHT_ONLY = 'right';
	const ALTERNATE = 'alternate';

	private $idx = -1;
	private $type = null;

	public function __construct($type)
	{
		$this->type = $type;
	}

	public function position()
	{	
		$this->idx++;
		switch($this->type)
		{
			case self::ALTERNATE:
				return ($this->idx)%2 == 0;
				break;
			case self::RIGHT_ONLY:
				return false;
				break;
			case self::LEFT_ONLY:
			default:
				return true;
				break;
		}
		
	}

}

