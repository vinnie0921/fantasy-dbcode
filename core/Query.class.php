<?php
/**
 * Author: Che-Ming Lin
 * E-mail: vinnie0921@gmail.com
 * Query.class.php ver 1.2.0
 */
class Query
{
	private $_arrConditions = array();
	
	/**
	 * Start index
	 *
	 * @var int
	 */
	public $limitStart;
	
	/**
	 * total
	 *
	 * @var int
	 */
	public $limitTotal;
	
	/**
	 * ASC or DESC
	 *
	 * @var string
	 */
	public $orderby;
	
	/**
	 * field name which want to order by.
	 *
	 * @var string
	 */
	public $orderColumn;
	
	/**
	 * Each select expr indicates a column that you want to retrieve.
	 *
	 * @var string
	 */
	public $select;
	
	/**
	 * ASC or DESC
	 *
	 * @var string
	 */
	public $groupby;
	
	/**
	 * field name which want to group by.
	 *
	 * @var string
	 */
	public $groupColumn;

	/**
	 * add search conditions.
	 *
	 * @param string $columnName
	 * @param string $operator
	 * @param string $value
	 */
	public function addCondition($columnName, $operator, $value)
	{
		$arrCondition = array();
		$arrCondition['column'] = $columnName;
		$arrCondition['operator'] = $operator;
		$arrCondition['value'] = $value;
		
		array_push($this->_arrConditions, $arrCondition);
	}

	public function getConditions()
	{
		return $this->_arrConditions;
	}
}
?>