<?php

include('utilities.php');


$params = $_GET;
$dbh = new PDO('sqlite:' . '../data/halcyon_tag.db');

if($params){

	$f = fopen('../data/log.txt', 'a');
	$keys = array_keys($params);
	$first_key =  array_shift($keys);
	switch($first_key){
		case('year'):

			json_dump($dbh, 'pages', $params[$first_key]); break;

		case('save'):

			$input = file_get_contents('php://input');
			$json_array = json_decode($input, true);

			$columns_array = array_keys($json_array);
			$columns = implode(',', $columns_array);

			$tags = explode(',', $json_array['tag']);
			unset($json_array['id']);
			unset($json_array['tag']);

			foreach($tags as $tag){
				$data_array = [];
				$data_array = $json_array;
				$data_array['tag'] = $tag;

				sql_insert_values($dbh, 'tags', $data_array);
			}
			fclose($f);
			break;

		case('csv'):
			$filepath = '../csv/' . $params[$first_key] . '_pages_mod.csv';
			echo($filepath);
			csv_to_sql($dbh, $filepath, $params[$first_key]);
			break;
		case('testsave'):
			echo 'T E S T S A V E';
			br();
			$query = 'SELECT * FROM tags';
			$results = sql_do($dbh,$query);
			foreach($results as $row) {
				$json[] = $row;
			}
			if(isset($json)){echo json_encode($json);}
			break;

		case('db_setup'):
			db_setup($db);
			break;

		case('db_reset'):
			db_reset($dbh,$params[$first_key]);
			break;
	}
}