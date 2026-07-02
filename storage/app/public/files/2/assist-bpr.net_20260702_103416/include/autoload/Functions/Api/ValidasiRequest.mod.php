<?php
/* 
Standart Function Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Functions/
2. Pastikan Nama Function Sama Dengan Nama File
3. Tidak Boleh Ada nama Function Kembar di semua Subdir nya
4. Cara Memanggil Function: 
									udf::ValidasiRequest(...parameters) 
*/
function ValidasiRequest($data, $rules,$lUpdate = false,$lFound=true) {
	$errors = [];

	$rules = array_change_key_case($rules, CASE_UPPER);
	if(!$lUpdate){
		foreach ($rules as $field => $rule) {
			// Check if the field is required
			if (isset($rule['required']) && $rule['required']) {
					if (!array_key_exists($field, $data) || empty(trim($data[$field]))) {
							$errors[] = "The field '$field' is required and cannot be empty.";
							continue; // Skip further checks for this field
					}
			}

			// Validate the type of the field
			if (isset($data[$field])) {
					switch ($rule['type']) {
							case 'string':
									if (!is_string($data[$field])) {
											$errors[] = "The field '$field' must be a string.";
									}
									break;

							case 'number':
									if (!is_numeric($data[$field])) {
											$errors[] = "The field '$field' must be a number.";
									}
									break;

							case 'date':
									$date = DateTime::createFromFormat('Y-m-d', $data[$field]);
									if (!$date || $date->format('Y-m-d') !== $data[$field]) {
											$errors[] = "The field '$field' must be a valid date in 'Y-m-d' format.";
									}
									break;

							default:
									$errors[] = "The field '$field' has an invalid type specified.";
									break;
					}
			}
			// Additional validation based on rules
			if (isset($rule['values']) && is_array($rule['values'])) {
					if (!in_array($data[$field], $rule['values'])) {
							$valuesList = implode(", ", $rule['values']);
							$errors[] = "The field '$field' must be one of the following values: $valuesList.";
					}
			}
			// Validate length if specified
			if (isset($rule['length'])) {
					$length = strlen($data[$field]);
					if ($length != $rule['length']) {
							$errors[] = "The field '$field' must be exactly {$rule['length']} characters long.";
					}
			}
		}	
	}
	if($lFound){
		foreach($data as $key => $value){
			if(!isset($rules[$key])){
				if($key != "DEVICEID" && $key != 'PLATFORM' && $key !='VERSIAPLIKASI' && $key !='MTI' && $key !='CODE' && $key !='PRODUK'){
					$errors[] = "The field '$key' not found !" ;
				}
			}
		}	
	}
	return [
			'valid' => empty($errors),
			'errors' => $errors
	];
}
