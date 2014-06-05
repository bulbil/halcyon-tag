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
			$input = json_decode($input);
			unset($input)['id'];

			$columns_array = array_keys($input);
			$columns = implode(',', $columns);

			$tags = explode(',', $input['tag']);
			fwrite($f, print_r($tags, true));

			foreach($tags as $tag){
				$data_array = $input;
				$data_array['tag'] = $tag;

				$query = "INSERT INTO tags($columns) VALUES (?,?,?,?,?)";
				fwrite($f, $query);
				fwrite($f, print_r($data_array, true));
			}
			$input = print_r($input,true);
			fclose($f);

			// sql_do($dbh, $query, $input);
			break;

		case('db_setup'):

		case('csv'):
			$filepath = '../csv/' . $params[$first_key] . '_pages_mod.csv';
			echo($filepath);
			csv_to_sql($dbh, $filepath);
			break;
	}
}