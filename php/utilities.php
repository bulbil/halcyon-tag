<?php

function br() { echo '<br /><br />'; }

function csv_to_sql($db, $filepath){

	$pages_columns_array = array(
		'pageptr',
		'pagetitle',
		'pagefile',
		'show',
		'year',
		'collection'
	);

	$f = fopen($filepath, 'r');

	while($data = fgetcsv($f, ",")){
		$data_array = array(
			$data[0]=>$data[1],
			$data[2]=>$data[3],
			$data[4]=>$data[5],
			$data[6]=>$data[7],
			"year"=>1964,
			"collection"=> "SC_Halcyon"
		);
		print_r($data_array);
		br();

		$columns = implode(",", $columns_array);
		$query = "INSERT INTO pages($columns) VALUES (?,?,?,?,?,?)";
		
		sql_do($db,$query,$data_array);
	}
}

function sql_do(/*db,  query [[, arg ... ]] */) {
	
	$args = func_get_args();
	if(count($args) < 2) { throw new PDOexception('sql do - missing query'); }
	
	$db = 	array_shift($args);
	
	if(count($args) == 1) {

		$stmt = $db->query(array_shift($args));
		return $stmt;		
	} else {
		$stmt = $db->prepare(array_shift($args));
		$vals_array = array_values($args[0]);
		$f = fopen('log.txt','a');
		fwrite($f, print_r($vals_array,true));
		$stmt->execute($vals_array);
		return $stmt->rowCount();
	}
}

function json_dump($db,$table, $year) {

	$json_columns_array = array(
		'id',
		'pageptr',
		'pagetitle',
		'year',
		'collection'
	);

	$columns = implode(',',$json_columns_array);
	// $query = "SELECT * FROM $table WHERE year = $year";
	$query = "SELECT $columns FROM $table WHERE year = $year AND show = 1";
	$results = sql_do($db, $query);
	
	foreach($results as $row) {

		for($i = 0; $i < 9; $i++){
			unset($row[$i]);
		}
		$json[] = $row;
	}
	if(isset($json)){echo json_encode($json);}
}

function get_pointers( $filename ) {
	
	$fh = fopen($filename, 'r');

	$i = 0;
	while ($row = fgets($fh)) {
		if(strpos($row, 'pageptr')) {
			preg_match('/\d{5,}/', $row, $matches); 
			$pointers[] = $matches[0]; 
		}
	}
	fclose($fh);
	return $pointers;
}

function add_pointers( $db, $pointers ) {

	foreach($pointers as $ptr) sql_do($dbh, $query1, 'SC_Halcyon', $ptr);
}

function db_setup($dbh){
	try {
		// $dbh = new PDO('sqlite:' . 'data/halcyon_tag.db');
		// $query1 = "insert into tags ('collection', pointer) values (?,?)";
		$query1= "DROP TABLE TAGS";
		$query2= "DROP TABLE PAGES";

		$query3= "DELETE FROM TAGS";
		$query3= "DELETE FROM PAGES";
		
		$query4= "CREATE TABLE pages (
			id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
			collection TEXT,			
			pagetitle TEXT, 
			pageptr INTEGER,
			pagefile TEXT,
			year INTEGER,
			show INTEGER
			)";

		$query5= "CREATE TABLE tags (
			id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
			collection TEXT,
			pagetitle TEXT, 
			pageptr INTEGER,
			year INTEGER,
			tag TEXT,
			contributor TEXT,
			timestamp TEXT DEFAULT CURRENT_TIMESTAMP NOT NULL
			)";

		sql_do($dbh, $query1);
		sql_do($dbh, $query4);
		sql_do($dbh, $query5);

	} catch(PDOexception $e) {

		echo $e->getMessage();
	}
}