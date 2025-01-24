<?php

class BringApi {
    private $bringRestURL = "https://api.getbring.com/rest/";
    private $translations = null;
    private $headers;
    private $addHeaders;
    private $bringUUID;
    private $bringListUUID;

    public function __construct($uuid, $bringuuid, $useLogin = false) {
        if ($useLogin) {
            list($this->bringUUID, $this->bringListUUID) = $this->login($uuid, $bringuuid);
        } else {
            $this->bringUUID = $uuid;
            $this->bringListUUID = $bringuuid;
        }

        $this->headers = [
            'X-BRING-API-KEY' => 'cof4Nc6D8saplXjE3h3HXqHH8m7VU2i1Gs0g85Sp',
            'X-BRING-CLIENT' => 'android',
            'X-BRING-USER-UUID' => $this->bringUUID,
            'X-BRING-VERSION' => '303070050',
            'X-BRING-COUNTRY' => 'de'
        ];

        $this->addHeaders = $this->headers;
        $this->addHeaders['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
    }

    public function login($email, $password) {
        $params = ['email' => $email, 'password' => $password];
        $response = $this->request('GET', $this->bringRestURL . "bringlists", $params);

        if (!isset($response['uuid'], $response['bringListUUID'])) {
            throw new Exception("Authentication failed: Invalid email or password.");
        }

        return [$response['uuid'], $response['bringListUUID']];
    }

    public function getItems($locale = null) {
        $url = $this->bringRestURL . "bringlists/" . $this->bringListUUID;
        $items = $this->request('GET', $url);

        if ($locale) {
            $transl = $this->loadTranslations($locale);
            foreach (['purchase', 'recently'] as $type) {
                // Nur Arrays durchlaufen
                if (isset($items[$type]) && is_array($items[$type])) {
                    foreach ($items[$type] as &$item) {
                        $item['name'] = isset($transl[$item['name']]) ? $transl[$item['name']] : $item['name'];
                    }
                }
            }
        }

        return $items;
    }


    public function getItemsDetail() {
        $url = $this->bringRestURL . "bringlists/" . $this->bringListUUID . "/details";
        return $this->request('GET', $url);
    }

    public function purchaseItem($item, $specification) {
        $body = "&purchase=$item&recently=&specification=$specification&remove=&sender=null";
        $url = $this->bringRestURL . "bringlists/" . $this->bringListUUID;
        return $this->request('PUT', $url, [], $body);
    }

    public function recentItem($item) {
        $body = "&purchase=&recently=$item&specification=&remove=&sender=null";
        $url = $this->bringRestURL . "bringlists/" . $this->bringListUUID;
        return $this->request('PUT', $url, [], $body);
    }

    public function removeItem($item) {
        $body = "&purchase=&recently=&specification=&remove=$item&sender=null";
        $url = $this->bringRestURL . "bringlists/" . $this->bringListUUID;
        return $this->request('PUT', $url, [], $body);
    }

    public function loadProducts() {
        $url = $this->bringRestURL . "bringproducts";
        return $this->request('GET', $url);
    }

    public function loadFeatures() {
        $url = $this->bringRestURL . "bringusers/" . $this->bringUUID . "/features";
        return $this->request('GET', $url);
    }

    public function loadLists() {
        $url = $this->bringRestURL . "bringusers/" . $this->bringUUID . "/lists";
        return $this->request('GET', $url);
    }

    public function getUsersFromList($listUUID) {
        $url = $this->bringRestURL . "bringlists/" . $listUUID . "/users";
        return $this->request('GET', $url);
    }

    public function getUserSettings() {
        $url = $this->bringRestURL . "bringusersettings/" . $this->bringUUID;
        return $this->request('GET', $url);
    }

    public function loadTranslations($locale) {
        if (!$this->translations) {
            $url = "https://web.getbring.com/locale/articles.$locale.json";
            $this->translations = $this->request('GET', $url);
        }
        return $this->translations;
    }

    public function loadCatalog($locale) {
        $url = "https://web.getbring.com/locale/catalog.$locale.json";
        return $this->request('GET', $url);
    }

    private function request($method, $url, $params = [], $body = null) {
        $ch = curl_init();

        if ($method === 'GET' && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = [];
        if (is_array($this->headers)) {
            foreach ($this->headers as $key => $value) {
                $headers[] = "$key: $value";
            }
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception("Request Error: " . curl_error($ch));
        }

        curl_close($ch);

        return json_decode($response, true);
    }
}

?>
