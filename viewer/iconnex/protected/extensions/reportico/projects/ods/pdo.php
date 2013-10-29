<?php 

try {
		if ($this->pdo = new PDO("informix:host=10.9.1.254; service=5130; database=centurion; server=centlive_tcp; protocol=onsoctcp;", "informix", "informix"))
		{
			return true;
		}
		else
		{
			return false;
		}
}
catch ( PDOException $ex )
{
    var_dump($ex);
}
?>

