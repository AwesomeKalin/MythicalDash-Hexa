<?php 
namespace MythicalDash\Pterodactyl;
use MythicalDash\SettingsManager;
use MythicalDash\Logger;

class Connection {

    public static string $pterodactyl_url;
    public static string $pterodactyl_api_key;
    /**
     * Init the connection to the panel!
     */
    public static function initializeSettings() {
        self::$pterodactyl_url = SettingsManager::getSetting("PterodactylURL");
        self::$pterodactyl_api_key = SettingsManager::getSetting("PterodactylAPIKey");
    }
    /**
     * Check if the connection to panel is a success
     * 
     * @return bool
     */
    public static function checkConnection() : bool
    {
        self::initializeSettings();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => self::$pterodactyl_url . "/api/application/nests",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer ' . self::$pterodactyl_api_key
            )
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            Logger::log("Pterodactyl Panel Connection Error","Failed to connect to the Pterodactyl Panel: ".$err.$response);
            return false; 
        } else {
            $responseData = json_decode($response, true);
            $check = isset($responseData['object']) && $responseData['object'] === 'list';
            if ($check) {
                return true;
            } else {
                Logger::log("Pterodactyl Panel Connection Error","Failed to connect to the Pterodactyl Panel: ".$response);
                return false;
            }
        }
    }
}
?>