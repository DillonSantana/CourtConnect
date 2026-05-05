<?php
function connectDB()
{
	// Local Server
	$host="localhost";
	$db="courtconnect";
	$user="root";
	$pwd="";


	// Production Server
	/*
	$host="localhost";
	$db="u668523202_courtconnect";
	$user="Tennis";
	$pwd="ClubTennisIsAwesome7";
	*/
    

	$attr="mysql:host=$host;dbname=$db";
	$opts=[
		PDO::ATTR_ERRMODE			=>PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES	=>False
	];
  try{
  		$pdo=new PDO($attr,$user,$pwd, $opts);
  		return $pdo;
  }
  catch(PDOException $e)	
  	{
  		throw new Exception($e->getMessage(), (int)$e->getCode()); 	
  	}
}
?>