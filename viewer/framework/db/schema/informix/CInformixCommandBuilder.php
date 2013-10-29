<?php
/**
 * CMsCommandBuilder class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Christophe Boulain <Christophe.Boulain@gmail.com>
 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CInformixCommandBuilder provides basic methods to create query commands for tables for Informix Servers.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Christophe Boulain <Christophe.Boulain@gmail.com>
 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @version $Id: CInformixCommandBuilder.php 2821 2011-01-06 17:41:53Z qiang.xue $
 * @package system.db.schema.mssql
 * @since 1.0.4
 */
class CInformixCommandBuilder extends CDbCommandBuilder
{
	public function applyLimit($sql, $limit, $offset)
	{
		$limit = $limit!==null ? intval($limit) : -1;
		$offset = $offset!==null ? intval($offset) : -1;

// ZZZ Added " DISTINCT" to end of replace below as I think they should be there.
		if ($limit > 0 && $offset <= 0) //just limit
			$sql = preg_replace('/^([\s(])*SELECT( DISTINCT)?(?!\s*TOP\s*\()/i',"\\1SELECT\\2 FIRST $limit DISTINCT", $sql);
		else if($limit > 0 && $offset > 0)
			$sql = preg_replace('/^([\s(])*SELECT( DISTINCT)?(?!\s*TOP\s*\()/i',"\\1SELECT\\2 SKIP $offset FIRST $limit DISTINCT", $sql);

		return $sql;
	}

	/**
	 * Creates an UPDATE command.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param array $data list of columns to be updated (name=>value)
	 * @param CDbCriteria $criteria the query criteria
	 * @return CDbCommand update command.
	 */
	public function createUpdateCommand($table,$data,$criteria)
	{
		$this->ensureTable($table);
		$fields=array();
		$values=array();
		$bindByPosition=isset($criteria->params[0]);
		$i=0;
		foreach($data as $name=>$value)
		{
			if(($column=$table->getColumn($name))!==null)
			{
                if (  $name == "v1ehicle_type_id" 
                    || $name == "mode1m_addr" 
                    || $name == "v1etag_indicator" 
                        )
                {
                    printf("ign $name<br>");
                } else
                if($column->isSerial)
                {
                    printf("ignore is serial\n");
                }
				else if($value instanceof CDbExpression)
				{
					$fields[]=$column->rawName.'='.$value->expression;
					foreach($value->params as $n=>$v)
						$values[$n]=$v;
				}
				else if($bindByPosition)
				{
					$fields[]=$column->rawName.'=?';
					$values[]=$column->typecast($value);
				}
				else
				{
					$fields[]=$column->rawName.'='.self::PARAM_PREFIX.$i;
					$values[self::PARAM_PREFIX.$i]=$column->typecast($value);
					$i++;
				}
			}
		}

		if($fields===array())
			throw new CDbException(Yii::t('yii','No columns are being updated for table "{table}".',
				array('{table}'=>$table->name)));
		$sql="UPDATE {$table->rawName} SET ".implode(', ',$fields);
		$sql=$this->applyJoin($sql,$criteria->join);
		$sql=$this->applyCondition($sql,$criteria->condition);
		$sql=$this->applyOrder($sql,$criteria->order);
		$sql=$this->applyLimit($sql,$criteria->limit,$criteria->offset);

		$command=$this->getDbConnection()->createCommand($sql);
		$this->bindValues($command,array_merge($values,$criteria->params));

		return $command;
	}

	/**
	 * Creates a COUNT(*) command for a single table.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param CDbCriteria $criteria the query criteria
	 * @param string $alias the alias name of the primary table. Defaults to 't'.
	 * @return CDbCommand query command.
	 */
	public function createCountCommand($table,$criteria,$alias='t')
	{
		$this->ensureTable($table);
		if($criteria->alias!='')
			$alias=$criteria->alias;
		$alias=$this->getSchema()->quoteTableName($alias);
		if(!empty($criteria->group) || !empty($criteria->having))
		{
			$select=is_array($criteria->select) ? implode(', ',$criteria->select) : $criteria->select;
			if($criteria->alias!='')
				$alias=$criteria->alias;
			$sql=($criteria->distinct ? 'SELECT DISTINCT':'SELECT')." {$select} FROM {$table->rawName} $alias";
			$sql=$this->applyJoin($sql,$criteria->join);
			$sql=$this->applyCondition($sql,$criteria->condition);
			$sql=$this->applyGroup($sql,$criteria->group);
			$sql=$this->applyHaving($sql,$criteria->having);
			$sql="SELECT COUNT(*) FROM ($sql) sq";
		}
		else
		{
			if(is_string($criteria->select) && stripos($criteria->select,'count')===0)
				$sql="SELECT ".$criteria->select;
			else if($criteria->distinct)
			{
				if(is_array($table->primaryKey))
				{
					$pk=array();
					foreach($table->primaryKey as $key)
						$pk[]=$alias.'.'.$key;
					$pk=implode(', ',$pk);
				}
				else
					$pk=$alias.'.'.$table->primaryKey;
				$sql="SELECT COUNT(DISTINCT $pk)";
			}
			else
				$sql="SELECT COUNT(*)";
			$sql.=" FROM {$table->rawName} $alias";
			$sql=$this->applyJoin($sql,$criteria->join);
			$sql=$this->applyCondition($sql,$criteria->condition);
		}

		$command=$this->getDBConnection()->createCommand($sql);
		$this->bindValues($command,$criteria->params);
		return $command;
	}


	/**
	 * Binds parameter values for an SQL command.
	 * @param CDbCommand $command database command
	 * @param array $values values for binding (integer-indexed array for question mark placeholders, string-indexed array for named placeholders)
	 */
	public function bindValues($command, $values)
	{
		if(($n=count($values))===0)
			return;
		if(isset($values[0])) // question mark placeholders
		{
			for($i=0;$i<$n;++$i)
            {
				$command->bindValue($i+1,$values[$i]);
            }
		}
		else // named placeholders
		{
			foreach($values as $name=>$value)
			{
				if($name[0]!==':')
					$name=':'.$name;
				$command->bindValue($name,$value);
			}
		}
	}

}
