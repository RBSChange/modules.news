<?php
/**
 * @date 8 févr. 08 15:01:29
 * @author franck.stauffer
 * @package modules.news
 */

class news_ArchivePaginator extends ArrayObject
{
	private $archiveSpanYears;
	public $yearPaginator = array();
	public $monthPaginator = array();

	public function __construct($globalNewsList, $year, $month, $spanyears = 6)
	{
		$newsList = array();
		if (isset($globalNewsList[$year]))
		{
			if(isset($globalNewsList[$year][$month]))
			{
				$newsList = $globalNewsList[$year][$month];
			}
		}
		$this->archiveSpanYears = $spanyears;
		$this->setupYearPagination($globalNewsList, $year, $month);
		if (isset($globalNewsList[$year]))
		{
			$this->setupMonthPaginationByYear($globalNewsList, $year, $month);
		}
		parent::__construct($newsList);
	}

	private function setupYearPagination($globalNewsList, $currentYear, $currentMonth)
	{
		$this->yearPaginator = array();
		$max = date_Calendar::now();
		$min = date_Calendar::now();
		$min->sub(date_Calendar::YEAR, $this->archiveSpanYears);
		for ($i = 0;  $i <= $this->archiveSpanYears ; $i++)
		{
			$endDate = date_Calendar::now()->sub(date_Calendar::YEAR, $i);
			$janEndDate = date_Calendar::now()->sub(date_Calendar::YEAR, $i)->setMonth(1);
			
			$count = 0;
			$year = strval($max->getYear()-$i);
			if(isset($globalNewsList[$year]))
			{
				$count = count($globalNewsList[$year]);
				$month = 1;
				if ($janEndDate->isBefore($min))
				{
					$month = $endDate->getMonth();
				}
				if ($count > 0)
				{
					while( isset($globalNewsList[$year][$month]) === false )
					{
						$month++;
					}
				}
			}
			
			$param = array('year' => $year, 'month' => $month, 'page' => 1);
			$this->yearPaginator[] = array(	'value' => $year, 
											'name'=> strval($year), 
											'count' => $count, 
											'url' => LinkHelper::getCurrentUrl(array( 'newsParam'=> $param)), 
											'isCurrent' => strval($currentYear) == $year,
											'isEmpty' => $count == 0
											);
		}
	}

	private function setupMonthPaginationByYear($list, $currentYear, $currentMonth)
	{
		$this->monthPaginator = array();
		$now = date_Calendar::now();
		$max = date_Calendar::getInstance("$currentYear-12-31");
		if ($now->isBefore($max))
		{
			$max = $now;
		}
		$min =  date_Calendar::getInstance("$currentYear-01-01");
		$absoluteMin = date_Calendar::now()->sub(date_Calendar::YEAR, $this->archiveSpanYears);

		if($min->isBefore($absoluteMin))
		{
			$min = $absoluteMin;
		}

		for ($i = date_Calendar::getInstance($min->toString());  $i->isBetween($min, $max, false); $i->add(date_Calendar::MONTH, 1))
		{
			$month = strval($i->getMonth());
			$param = array('year' => $currentYear, 'month' => $month, 'page' => 1);
			$count = 0;
			if (isset($list[$currentYear]))
			{
				if(isset($list[$currentYear][$month]))
				{
					$count = count($list[$currentYear][$month]);
				}
			}		
			$this->monthPaginator[] = array( 'value' => $month,  
											  'name' => date_DateFormat::format($i, 'F') . ($count == 0 ? "" : " ($count)"),  
											  'singlename' => date_DateFormat::format($i, 'F'),  
											  'count' => $count, 
											  'url' => LinkHelper::getCurrentUrl(array( 'newsParam'=> $param)),
											  'isCurrent' => strval($currentMonth) == $month,
											  'isEmpty' => $count == 0
											  );
		}
	}
}