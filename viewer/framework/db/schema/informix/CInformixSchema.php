<?php
/**
 * CInformixSchema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CInformixSchema is the class for retrieving metadata information from a MySQL database (version 4.1.x and 5.x).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CInformixSchema.php 2799 2011-01-01 19:31:13Z qiang.xue $
 * @package system.db.schema.mysql
 * @since 1.0
 */
class CInformixSchema extends CDbSchema
{
	/**
	 * @var array the abstract column types mapped to physical column types.
	 * @since 1.1.6
	 */
    public $columnTypes=array(
        'pk' => 'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
        'string' => 'varchar(255)',
        'text' => 'text',
        'integer' => 'int(11)',
        'float' => 'float',
        'decimal' => 'decimal',
        'datetime' => 'datetime',
        'timestamp' => 'timestamp',
        'time' => 'time',
        'date' => 'date',
        'binary' => 'blob',
        'boolean' => 'tinyint(1)',
    );


   /**
     * Creates a command builder for the database.
     * This method overrides parent implementation in order to create an Informix specific command builder
     * @return CDbCommandBuilder command builder instance
     */
    protected function createCommandBuilder()
    {   
        return new CInformixCommandBuilder($this);
    }


	/**
	 * Quotes a table name for use in a query.
	 * A simple table name does not schema prefix.
	 * @param string $name table name
	 * @return string the properly quoted table name
	 * @since 1.1.6
	 */
	public function quoteSimpleTableName($name)
	{
		return ''.$name.'';
	}

	/**
	 * Quotes a column name for use in a query.
	 * A simple column name does not contain prefix.
	 * @param string $name column name
	 * @return string the properly quoted column name
	 * @since 1.1.6
	 */
	public function quoteSimpleColumnName($name)
	{
		return ''.$name.'';
	}

	/**
	 * Compares two table names.
	 * The table names can be either quoted or unquoted. This method
	 * will consider both cases.
	 * @param string $name1 table name 1
	 * @param string $name2 table name 2
	 * @return boolean whether the two table names refer to the same table.
	 */
	public function compareTableNames($name1,$name2)
	{
		return parent::compareTableNames(strtolower($name1),strtolower($name2));
	}

	/**
	 * Resets the sequence value of a table's primary key.
	 * The sequence will be reset such that the primary key of the next new row inserted
	 * will have the specified value or 1.
	 * @param CDbTableSchema $table the table schema whose primary key sequence will be reset
	 * @param mixed $value the value for the primary key of the next new row inserted. If this is not set,
	 * the next new row's primary key will have a value 1.
	 * @since 1.1
	 */
	public function resetSequence($table,$value=null)
	{
		if($table->sequenceName!==null)
		{
			if($value===null)
				$value=$this->getDbConnection()->createCommand("SELECT MAX(`{$table->primaryKey}`) FROM {$table->rawName}")->queryScalar()+1;
			else
				$value=(int)$value;
			$this->getDbConnection()->createCommand("ALTER TABLE {$table->rawName} AUTO_INCREMENT=$value")->execute();
		}
	}

	/**
	 * Enables or disables integrity check.
	 * @param boolean $check whether to turn on or off the integrity check.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * @since 1.1
	 */
	public function checkIntegrity($check=true,$schema='')
	{
		$this->getDbConnection()->createCommand('SET FOREIGN_KEY_CHECKS='.($check?1:0))->execute();
	}

	/**
	 * Loads the metadata for the specified table.
	 * @param string $name table name
	 * @return CInformixTableSchema driver dependent table metadata. Null if the table does not exist.
	 */
	protected function loadTable($name)
	{
		$table=new CInformixTableSchema;
		$this->resolveTableNames($table,$name);


		if($this->findColumns($table))
		{
			$this->findConstraints($table);
			return $table;
		}
		else
			return null;
	}

	/**
	 * Generates various kinds of table names.
	 * @param CInformixTableSchema $table the table instance
	 * @param string $name the unquoted table name
	 */
	protected function resolveTableNames($table,$name)
	{
		$parts=explode('.',str_replace('`','',$name));
		if(isset($parts[1]))
		{
			$table->schemaName=$parts[0];
			$table->name=$parts[1];
			$table->rawName=$this->quoteTableName($table->schemaName).'.'.$this->quoteTableName($table->name);
		}
		else
		{
			$table->name=$parts[0];
			$table->rawName=$this->quoteTableName($table->name);
		}
	}

	/**
	 * Collects the table column metadata.
	 * @param CInformixTableSchema $table the table metadata
	 * @return boolean whether the table exists in the database
	 */
	protected function findColumns($table)
	{
		$sql='select colname FROM systables, syscolumns WHERE systables.tabid = syscolumns.tabid and tabname =   "'.$table->name.'"';
        $sql='select colname, coltype || "_" || collength coltype , part1 + part2 + part3 + part4 + part5 primary, sysdefaults.default
            from systables, syscolumns, outer ( sysconstraints, sysindexes ), outer sysdefaults
            where tabname = "'.$table->name.'"
            and sysconstraints.tabid = systables.tabid
            and sysindexes.idxname = sysconstraints.idxname
            and constrtype = "P"
            and syscolumns.tabid = systables.tabid
            and (
            part1 = syscolumns.colno
            or part2 = syscolumns.colno
            or part3 = syscolumns.colno
            or part4 = syscolumns.colno
            or part5 = syscolumns.colno
            )
            and sysdefaults.tabid = systables.tabid
            and sysdefaults.colno = syscolumns.colno;
            ';

		try
		{
			$columns=$this->getDbConnection()->createCommand($sql)->queryAll();
		}
		catch(Exception $e)
		{
			return false;
		}
		foreach($columns as $column)
		{
			$c=$this->createColumn($column);
			$table->columns[$c->name]=$c;
			if($c->isPrimaryKey)
			{
				if($table->primaryKey===null)
					$table->primaryKey=$c->name;
				else if(is_string($table->primaryKey))
					$table->primaryKey=array($table->primaryKey,$c->name);
				else
					$table->primaryKey[]=$c->name;
				if($column['coltype'] == 262)
					$table->sequenceName='';
			}
		}
		return true;
	}

	/**
	 * Creates a table column.
	 * @param array $column column metadata
	 * @return CDbColumnSchema normalized column metadata
	 */
	protected function createColumn($column)
	{
		$c=new CInformixColumnSchema;
		$c->name=$column['colname'];
		$c->rawName=$this->quoteColumnName($c->name);
		$c->allowNull='YES'; // PPP Fix later $column['Null']==='YES';
        if ( $column['primary'] > 0 ) 
		    $c->isPrimaryKey=true;
        else
		    $c->isPrimaryKey=false;
		$c->isForeignKey=false;
        $column['AutoIncrement'] = false;
        if ( $column['coltype'] == 262 )
            $column['AutoIncrement'] = true;
		$c->init($column['coltype'],$column['default']);
		return $c;
	}

	/**
	 * @return float server version.
	 */
	protected function getServerVersion()
	{
		$version=$this->getDbConnection()->getAttribute(PDO::ATTR_SERVER_VERSION);
		$digits=array();
		preg_match('/(\d+)\.(\d+)\.(\d+)/', $version, $digits);
		return floatval($digits[1].'.'.$digits[2].$digits[3]);
	}

	/**
	 * Collects the foreign key column details for the given table.
	 * @param CInformixTableSchema $table the table metadata
	 */
	protected function findConstraints($table)
	{
        $sql = 'select sysconstraints_a.constrname,
                syscolumns_a.colname referring,
                systables_b.tabname,
                syscolumns_b.colname refers_to
            from systables systables_a, systables systables_b,
                sysconstraints sysconstraints_a, sysconstraints sysconstraints_b,
                sysconstraints sysconstraints_c,
                sysreferences,
                sysindexes sysindexes_a, sysindexes sysindexes_b,
                syscolumns syscolumns_a, syscolumns syscolumns_b
            where systables_a.tabname = "' . $table->rawName . '"' .
            'and sysconstraints_a.tabid = systables_a.tabid
            and sysconstraints_a.constrtype = "R"
            and sysreferences.constrid = sysconstraints_a.constrid
            and sysreferences.ptabid = systables_b.tabid
            and sysconstraints_b.constrid = sysreferences.primary
            and sysindexes_b.idxname = sysconstraints_b.idxname
            and syscolumns_b.tabid = sysindexes_b.tabid
            and syscolumns_b.colno = sysindexes_b.part1
            and sysconstraints_c.tabid = systables_a.tabid
            and sysconstraints_c.constrtype = "R"
            and sysreferences.constrid = sysconstraints_c.constrid
            and sysreferences.ptabid = systables_b.tabid
            and sysindexes_a.idxname = sysconstraints_c.idxname
            and syscolumns_a.tabid = sysindexes_a.tabid
            and syscolumns_a.colno = sysindexes_a.part1';

		$row = $this->getDbConnection()->createCommand($sql)->queryAll();
        foreach ($row as $constraint)
        {
            $constrname = trim($constraint["constrname"]);
            $tabname = trim($constraint["tabname"]);
            $refersto = trim($constraint["refers_to"]);
            $referring = trim($constraint["referring"]);
            $table->foreignKeys[$refersto] = array($tabname, $referring);
            if (isset($table->columns[$referring]))
                $table->columns[$referring]->isForeignKey = true;
        }
	}

	/**
	 * Returns all table names in the database.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * If not empty, the returned table names will be prefixed with the schema name.
	 * @return array all table names in the database.
	 * @since 1.0.2
	 */
	protected function findTableNames($schema='')
	{
        
        ini_set("memory_limit","900M");
		if($schema==='')
			return $this->getDbConnection()->createCommand('SELECT tabname from systables')->queryColumn();
		$names=$this->getDbConnection()->createCommand('SHOW TABLES FROM '.$this->quoteTableName($schema))->queryColumn();
		foreach($names as &$name)
			$name=$schema.'.'.$name;
		return $names;
	}

	/**
	 * Builds a SQL statement for renaming a column.
	 * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
	 * @param string $name the old name of the column. The name will be properly quoted by the method.
	 * @param string $newName the new name of the column. The name will be properly quoted by the method.
	 * @return string the SQL statement for renaming a DB column.
	 * @since 1.1.6
	 */
	public function renameColumn($table, $name, $newName)
	{
		$db=$this->getDbConnection();
		$row=$db->createCommand('SHOW CREATE TABLE '.$db->quoteTableName($table))->queryRow();
		if($row===false)
			throw new CDbException(Yii::t('yii','Unable to find "{column}" in table "{table}".',array('{column}'=>$name,'{table}'=>$table)));
		if(isset($row['Create Table']))
			$sql=$row['Create Table'];
		else
		{
			$row=array_values($rows);
			$sql=$row[1];
		}
		if(preg_match_all('/^\s*`(.*?)`\s+(.*?),?$/m',$sql,$matches))
		{
			foreach($matches[1] as $i=>$c)
			{
				if($c===$name)
				{
					return "ALTER TABLE ".$db->quoteTableName($table)
						. " CHANGE ".$db->quoteColumnName($name)
						. ' '.$db->quoteColumnName($newName).' '.$matches[2][$i];
				}
			}
		}

		// try to give back a SQL anyway
		return "ALTER TABLE ".$db->quoteTableName($table)
			. " CHANGE ".$db->quoteColumnName($name).' '.$newName;
	}

	/**
	 * Builds a SQL statement for dropping a foreign key constraint.
	 * @param string $name the name of the foreign key constraint to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose foreign is to be dropped. The name will be properly quoted by the method.
	 * @return string the SQL statement for dropping a foreign key constraint.
	 * @since 1.1.6
	 */
	public function dropForeignKey($name, $table)
	{
		return 'ALTER TABLE '.$this->quoteTableName($table)
			.' DROP FOREIGN KEY '.$this->quoteColumnName($name);
	}
}
