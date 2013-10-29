<?php
/**
 * CInformixColumnSchema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CInformixColumnSchema class describes the column meta data of a MySQL table.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CInformixColumnSchema.php 2799 2011-01-01 19:31:13Z qiang.xue $
 * @package system.db.schema.mysql
 * @since 1.0
 */
class CInformixColumnSchema extends CDbColumnSchema
{

    public $isSerial   = false;

	/**
	 * Extracts the PHP type from DB type.
	 * @param string $dbType DB type
	 */
	protected function extractType($dbType)
	{
        $coltype = preg_replace("/_.*/", "", $dbType);
        $colsize = preg_replace("/.*_/", "", $dbType);
    
        $this->size = (int)$colsize;
        $this->allowNull = true;

        switch ( (int)$coltype )
        {
            case 262:
                $this->isSerial = true;
                $this->type = "integer";
                $this->allowNull = false;
                break;

            case 258:
                $this->type = "integer";
                $this->allowNull = false;
                break;

            case 2:
                $this->type = "integer";
                $this->allowNull = true;
                break;

            case 10:
                $this->type = "datetime year to second";
                break;

            case 14:
                $this->type = "interval hour to second";
                break;

            case 5:
                //$this->type = "decimal(16)";
			    $this->type='double';
                break;

            case 256:
                //$this->type = "char";
			    $this->type='string';
                $this->allowNull = false;
                break;

            case 0:
                //$this->type = "char";
			    $this->type='string';
                $this->allowNull = true;
                break;

            case 1:
                $this->allowNull = true;
                $this->type = "integer";
                break;

            case 257:
                $this->allowNull = false;
                $this->type = "smallint";
                break;

            default:
                break;
        }
	}

	/*
	 * Extracts the default value for the column.
	 * The value is typecasted to correct PHP type.
	 * @param mixed $defaultValue the default value obtained from metadata
	 */
	protected function extractDefault($defaultValue)
	{
		if($this->dbType==='timestamp' && $defaultValue==='CURRENT_TIMESTAMP')
			$this->defaultValue=null;
		else
			parent::extractDefault($defaultValue);
	}

	/**
	 * Extracts size, precision and scale information from column's DB type.
	 * @param string $dbType the column's DB type
	 */
	protected function extractLimit($dbType)
	{
		$this->size = preg_replace("/.*_/", "//", $dbType);
	}
}
