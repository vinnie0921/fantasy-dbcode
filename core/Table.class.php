<?php
/**
 * Author: Che-Ming Lin
 * E-mail: vinnie0921@gmail.com
 * Table.class.php ver 1.2.0
 */
require_once('Query.class.php');

class Table
{
	private $_uid;
	private $_conn;
	private $_tableName;
	private $_arrOriginalValues;
	private $_isLoaded = false;

	public function getUid()
	{
		return $this->_uid;
	}

	public function setUid($uid)
	{
		if(!is_numeric($uid))
		{
			throw new Exception('UID must be a number!');
		}
		else if($uid == 0)
		{
			throw new Exception('UID can not be a zero!');
		}

		$this->_uid = $uid;
	}

	protected function getConn()
	{
		return $this->_conn;
	}

	protected function setConn($conn)
	{
		$this->_conn = $conn;
	}

	protected function getTableName()
	{
		return $this->_tableName;
	}

	protected function setTableName($tableName)
	{
		$this->_tableName = $tableName;
	}

	public function iterateVisible()
	{
		foreach($this as $key => $value)
		{
			echo "$key => $value\n";
		}
	}

	/**
	 * 載入資料
	 *
	 * @param int $uid UID
	 * @param string $fields 欄位
	 * @return boolean 是否載入成功
	 */
	public function load($uid, $fields=null)
	{
		$this->_uid = $uid;
		$this->_isLoaded = true;

		if(!$uid)
		{
			throw new Exception("object must has a uid!");
		}

		if(is_null($fields))
		{
			$fields = "*";
		}

		$sql = "SELECT " . $this->_conn->escape($fields) . " ";
		$sql.= "FROM `" . $this->_conn->escape($this->_tableName) . "` ";
		$sql.= "WHERE uid = '" . $this->_conn->escape($uid) ."'";

		$arr_data = $this->_conn->select($sql);

		if(!empty($arr_data))
		{
			foreach ($arr_data[0] as $key => $value)
			{
				$this->$key = $value;
				$this->_arrOriginalValues[$key] = $value;
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * 新增一筆資料
	 *
	 * @return int insert id
	 */
	public function insert()
	{
		if($this->_isLoaded)
		{
			throw new Exception("The [" . $this->_tableName . "] table object can not insert a new row, because it has already loaded!");
		}

		$arrFields = array();
		$arrValues = array();

		$arrClassFields = get_class_vars(get_class($this));

		$index = 0;
		foreach ($arrClassFields as $field => $value)
		{
			// this is important to fix 5.2.+ php bug, don't remove it.
			if(isset($field[0]) && $field[0] == '_' && $field != '_uid')
			{
				// if the field is private property, go to next field.
				continue;
			}

			if($field == '_uid')
			{
				$arrFields[$index] = "`uid`";
				$arrValues[$index] = "'" . $this->_conn->escape($this->getUid()) . "'";
			}
			else
			{
				$arrFields[$index] = "`" . $field . "`";
				$arrValues[$index] = "'" . $this->_conn->escape($this->$field) . "'";
			}

			$index++;
		}

		$sql = "INSERT INTO `" . $this->_conn->escape($this->_tableName) . "` (" . join(",", $arrFields) . ") ";
		$sql.= "VALUES (" . join(",", $arrValues) . ");";

		$this->_uid = $this->_conn->insert($sql);

		return $this->_uid;
	}

	/**
	 * 更新一筆資料
	 *
	 * @return int affected rows num
	 */
	public function update()
	{
		if(!$this->_uid)
		{
			throw new Exception($this->_tableName . " needs uid to call update()");
		}

		$arr_update_fields = array();
		$index = 0;

		foreach ($this->_arrOriginalValues as $key => $value)
		{
			if($value !== $this->$key)
			{
				$arr_update_fields[$index] = "`" . $key . "` = '" . $this->_conn->escape($this->$key) . "'";
				$index++;
			}
		}

		if($index > 0)
		{
			$str_update_fields = join(",", $arr_update_fields);

			$sql = "UPDATE `" . $this->_conn->escape($this->_tableName) . "` ";
			$sql.= "SET " . $str_update_fields . " ";
			$sql.= "WHERE `uid` = '" . $this->_uid . "' ";
			$sql.= "LIMIT 1 ;";

			$affected_rows_num = $this->_conn->update($sql);
		}
		else
		{
			$affected_rows_num = 0;
		}

		return $affected_rows_num;
	}

	/**
	 * 刪除一筆資料
	 *
	 * @return int affected rows num
	 */
	public function delete()
	{
		if(!$this->_isLoaded)
		{
			throw new Exception('The [' . $this->_tableName . '] table object can not delete, you must load table first!');
		}

		$sql = "DELETE FROM `" . $this->_conn->escape($this->_tableName) . "` ";
		$sql.= "WHERE `uid` = '" . $this->_uid . "' ";
		$sql.= "LIMIT 1 ;";

		$affected_rows_num = $this->_conn->delete($sql);

		return $affected_rows_num;
	}
	
	/**
	 * 是否存在這個 uid
	 *
	 * @return boolean
	 */
	public function exists($uid)
	{
		$conn = $this->getConn();

		$sql = "SELECT COUNT(*) as itemsCount ";
		$sql.= "FROM `" . $this->getTableName() . "` ";
		$sql.= "WHERE uid = '" . $conn->escape($uid) . "' ";
		$arrData = $conn->select($sql);

		return ($arrData[0]['itemsCount'] > 0)?true:false;
	}

	/**
	 * 計算筆數
	 *
	 * @return int
	 */
	public function count(Query $objQuery=null)
	{
		$conn = $this->getConn();

		$sql = "SELECT COUNT(*) as itemsCount ";
		$sql.= "FROM `" . $this->getTableName() . "` ";

		if(isset($objQuery))
		{
			$sql.= $this->procQueryObject($objQuery);
		}
		
		$arrData = $conn->select($sql);

		return $arrData[0]['itemsCount'];
	}

	/**
	 * 瀏覽
	 *
	 * @param Query $objQuery
	 * @return array
	 */
	public function browse(Query $objQuery=null)
	{
		$conn = $this->getConn();

		if(empty($objQuery->select))
		{
			$sql = "SELECT * ";
		}
		else
		{

			$arrSelect = explode(',', $objQuery->select);
			if(!is_array($arrSelect) || empty($arrSelect))
			{
				throw new Exception(" objQuery->select value is not available!");
				break;
			}

			foreach ($arrSelect as $key => $val)
			{
				$arrSelect[$key] = '`' . $conn->escape(trim($val)) . '`';
			}

			$sql = "SELECT " . implode(',', $arrSelect) . " ";
		}

		$sql.= "FROM `" . $this->getTableName() . "` ";

		if(isset($objQuery))
		{
			$sql.= $this->procQueryObject($objQuery);
		}

		return $conn->select($sql);
	}

	/**
	 * 轉換 Query object 成為 sql
	 *
	 * @param Query $objQuery
	 * @return string
	 */
	private function procQueryObject(Query $objQuery)
	{
		$conn = $this->getConn();

		$arrConditions = $objQuery->getConditions();

		$allowOperator = array('>', '>=', '<', '<=', '<>', '=', 'LIKE', 'IN');

		$str = "";
		$sql = "";

		$allow = true;
		$arrConditionsLength = count($arrConditions);
		$i = 0;

		if($arrConditionsLength > 0)
		{
			$str.= "WHERE ";
		}

		foreach ($arrConditions as $conditionData)
		{
			if(in_array($conditionData['operator'], $allowOperator))
			{
				$conditionData['column'] = $conn->escape($conditionData['column']);

				if(is_array($conditionData['value']))
				{
					if(empty($conditionData['value']))
					{
						$allow = false;
						throw new Exception("WHERE IN operator's value can't be an empty! ");
						break;
					}

					if(strtoupper($conditionData['operator']) == 'IN')
					{
						// WHERE IN operator
						foreach ($conditionData['value'] as $key => $arrTmp)
						{
							$conditionData['value'][$key] = $conn->escape($conditionData['value'][$key]);
							$conditionData['value'][$key] = "'" . strval($conditionData['value'][$key]) . "'";
						}

						unset($key);
						unset($arrTmp);

						$str.= "`" . $conn->escape($conditionData['column']) . "` " . $conditionData['operator'] . " (" . implode(',', $conditionData['value']) . ") ";
					}
				}
				else
				{
					// LIKE and Normal operator
					if(strtoupper($conditionData['operator']) == 'LIKE')
					{
						$conditionData['value'] = $conn->escape($conditionData['value']);
						$str.= "`" . $conn->escape($conditionData['column']) . "` " . $conditionData['operator'] . " '%" . $conditionData['value'] . "%' ";
					}
					else
					{
						$conditionData['value'] = $conn->escape($conditionData['value']);
						$str.= "`" . $conn->escape($conditionData['column']) . "` " . $conditionData['operator'] . " '" . $conditionData['value'] . "' ";
					}
				}

				if($i != $arrConditionsLength-1)
				{
					$str.= 'AND ';
				}
			}
			else
			{
				$allow = false;
				throw new Exception('The query object operator is not allow! ');
				break;
			}

			$i++;
		}

		if($allow)
		{
			$sql.= $str;
			unset($str);
		}

		if(!empty($objQuery->groupColumn))
		{
			$arrGroupColumn = explode(',', $objQuery->groupColumn);
			if(!is_array($arrGroupColumn) || empty($arrGroupColumn))
			{
				throw new Exception(" objQuery->groupColumn value is not available!");
				break;
			}

			foreach ($arrGroupColumn as $key => $val)
			{
				$arrGroupColumn[$key] = '`' . $conn->escape(trim($val)) . '`';
			}

			$sql.= "GROUP BY " . implode(',', $arrGroupColumn) . " ";

			if(strtoupper($objQuery->groupby) == 'ASC' || strtoupper($objQuery->groupby) == 'DESC')
			{
				$sql.= " " . $objQuery->groupby . " ";
			}
		}

		if(!empty($objQuery->orderColumn))
		{
			$sql.= "ORDER BY `" . $conn->escape($objQuery->orderColumn) . "` ";

			if(strtoupper($objQuery->orderby) == 'ASC' || strtoupper($objQuery->orderby) == 'DESC')
			{
				$sql.= " " . $objQuery->orderby . " ";
			}
		}

		if(!is_null($objQuery->limitStart) && !is_null($objQuery->limitTotal))
		{
			if(is_numeric($objQuery->limitStart) && is_numeric($objQuery->limitTotal))
			{
				$sql.= "LIMIT " . $objQuery->limitStart . ", " . $objQuery->limitTotal . " ";
			}
		}

		return $sql;
	}
}
?>