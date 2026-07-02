<?php
/*
Standar Class Autoload
1. File harus ada di project/include/autoload/Classes/
2. Nama class = nama file
3. Tidak boleh ada class name kembar di semua subdir
*/

class DeviceExtension {

    /**
     * Cek status device ke server Auth
     */
    static function Cek($va) {
        $prevExtStatus = GetSetting("cSession_Extension", "2");
        $vaResponse = ["cError" => "ok", "cLogout" => ""];
        if ($va['cToken'] !='') {
            $cToken = GetTokenTransaksi::Get("");
            $cRequest = HttpHelper::Send(
                Svr::GetConfig("base_url_auth") . "/app/extension/v1/cek_device",
                ["device_id" => $va['cDeviceID'], "token" => $va['cToken']],
                "POST",$cToken
            );
            $vaRequest = json_decode($cRequest, true);
            SaveSetting("cSession_DeviceID", $va['cDeviceID']);

            if (isset($vaRequest['response_code'])) {
                if ($vaRequest['response_code'] == "200" && isset($vaRequest['data']['status'])) {
                    if ($vaRequest['data']['status']) {
                        $vaJWT = JWT::decode($vaRequest["data"]["cKey"]);
                        SaveSetting("cSession_vaExtension", $vaJWT['data']);
                        SaveSetting("cSession_KeyData", $vaRequest["data"]["cKey"]);
                        SaveSetting("cSession_Extension", 1);
                        $vaResponse['cLogout'] = "login";
                    } else {
                        if (GetSetting("cSession_UserName") != "") {
                            User::Delete();
                        }
                        SaveSetting("cSession_Extension", 3);
                        $vaResponse['cLogout'] = "login";
                    }
                } else if ($vaRequest['response_code'] == "400") {
                    SaveSetting("cSession_Extension", 0);
                }else if($vaRequest['response_code'] == "401"){
                  SaveSetting("cSession_Extension", 0);
                	$vaResponse['cLogout'] = "disconnect";
								}
            }
        } else {
            SaveSetting("cSession_Extension", 0);
        }
			
        // Jika ada perubahan status extension atau user login tapi extension invalid
        if ($prevExtStatus != GetSetting("cSession_Extension", "2") ||
            (GetSetting("cSession_UserName") != "" && GetSetting("cSession_Extension") == 0)) {

            if (GetSetting("cSession_UserName") != "" && GetSetting("cSession_Extension") == 0) {
                User::Delete();
            }
            $vaResponse['cError'] = "reload";
        }

        return $vaResponse;
    }

    /**
     * Handler AJAX dan Page load
     */
    static function Set() {
        // Handler untuk AJAX (fetch)
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ajax_check_device"])) {
            $cDeviceID = $_POST["device_id"] ?? null;
            $cToken    = $_POST["token"] ?? null;
            $va = [
                "cDeviceID" => $cDeviceID,
                "cToken"    => $cToken
            ];

            $vaResponse= self::Cek($va);
						SaveSetting("cSession_ExtensionCek","loaded");
            
            header("Content-Type: application/json");
            echo json_encode($vaResponse);
					  session_write_close();
					  exit() ;
        }

         if (GetSetting("cSession_ExtensionCek") == "" && $_SERVER['REQUEST_METHOD'] == "GET" && strpos($_SERVER['REQUEST_URI'], 'api/user') === false ) {
					  echo '<script>
							(() => {
									let extensionReady = false;
									let extensionData = null;

									// Listener pesan dari ekstensi
									window.addEventListener("message", (event) => {
											if (event.origin !== location.origin) return;

											const message = event.data;
											if (message?.data?.type === "extReady") {
													extensionReady = true;
													extensionData = message.data;
													sendToServer(extensionData);
											}
									});

									// Fungsi kirim ke server
									function sendToServer(data = {}) {
											fetch("", {
													method: "POST",
													headers: { "Content-Type": "application/x-www-form-urlencoded" },
													body: new URLSearchParams({
															ajax_check_device: 1,
															device_id: data.device_id ?? "",
															token: data.access_token ?? ""
													})
											})
											.then(r => r.json())
											.then(() => location.reload())
											.catch(err => console.error("Request gagal:", err));
									}

									// Timeout: kalau 10 detik tidak ada respon dari ekstensi → paksa jalan
									setTimeout(() => {
											if (!extensionReady) {
													console.warn("Ekstensi tidak terpasang, lanjut paksa.");
													sendToServer(); // kirim tanpa device_id/token
											}
									}, 10000);
							})();
							</script>';

        }
    }
}
