<?php

/**
 * A JSON POST to a courier API: TLS verified, a timeout, the answer decoded (an error answer throws)
 */
class CourierAPI
{
    /**
     * @param string $url
     * @param array $data the JSON body
     * @param int $timeout seconds
     * @return array the decoded answer
     * @throws Exception
     */
    public static function Post(string $url, array $data, int $timeout = 60) : array
    {
        $curl = curl_init($url);
        curl_setopt_array($curl, array(
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array("Content-Type: application/json"),
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ));
        $body = curl_exec($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);

        if ($body === false) throw new Exception("Courier API not reachable: $error");
        if ($status !== 200) throw new Exception("Courier API answered HTTP $status");

        $json = json_decode((string)$body, true);
        if (!is_array($json)) throw new Exception("Courier API: not a JSON answer");
        if (isset($json["error"])) {
            $message = is_array($json["error"]) ? (string)($json["error"]["message"] ?? "") : (string)$json["error"];
            throw new Exception("Courier API error: $message");
        }
        return $json;
    }
}
