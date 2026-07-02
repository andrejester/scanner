<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class HttpHelper {
  static function Send($cURL, $cMessage = [], $requestType = 'POST',$cTokenTransaksi='') {
		// Menyiapkan header
		$cRequestTime = (new DateTime())->format('c');
		$cHeaders = apache_request_headers();
		$cHeaders = array_change_key_case($cHeaders);
		$cAuthorization = isset($cHeaders["authorization"]) ? $cHeaders["authorization"] : "$cTokenTransaksi";
		$vaHeaders = array("Authorization: $cAuthorization");
		// Inisialisasi cURL
		$ch = curl_init($cURL);

		// Mengatur opsi dasar cURL
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		 // Jika ada file di $_FILES, tambahkan ke $cMessage
		if (!empty($_FILES)) {
				foreach ($_FILES as $key => $file) {
						if (is_uploaded_file($file['tmp_name'])) {
								$cMessage[$key] = new CURLFile(
										$file['tmp_name'], 
										mime_content_type($file['tmp_name']), 
										$file['name']
								);
						}
				}
		}

		// Mengatur opsi berdasarkan jenis permintaan
		switch (strtoupper($requestType)) {
				case 'GET':
						curl_setopt($ch, CURLOPT_HTTPGET, true);
						// GET request biasanya tidak mengirimkan body, jadi $cMessage diabaikan
						break;

				case 'POST':
						//curl_setopt($ch, CURLOPT_POST, true);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $cMessage);
						if(!is_array($cMessage)){
							$vaHeaders[] = "Content-Type: application/json";
							$vaHeaders[] = "Content-Length: ".strlen($cMessage);

						}
						break;

				case 'PUT':
						$cMessage = http_build_query($cMessage) ;
						curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
						curl_setopt($ch, CURLOPT_POSTFIELDS, $cMessage);
						break;

				case 'DELETE':
						curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
						curl_setopt($ch, CURLOPT_POSTFIELDS, $cMessage);
						break;

				default:
						// Jika requestType tidak dikenali, beri notifikasi atau fallback
						throw new InvalidArgumentException('Invalid request type: ' . $requestType);
		}
		curl_setopt($ch, CURLOPT_USERAGENT, "Assist Gateway Dashboard/1.0.0");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $vaHeaders);
		// Eksekusi cURL

		$cResponse = curl_exec($ch);//adasdas
		// Cek error
		if (curl_errno($ch)) {
				$cResponse = 'cURL error: ' . curl_error($ch);
		}
		// Tutup cURL
		curl_close($ch);

		return $cResponse;
	}
}
