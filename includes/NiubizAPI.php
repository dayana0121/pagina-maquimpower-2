<?php
class NiubizAPI {
    // CAMBIAR A 'prod' PARA SALIR A VIVO
    private $env = 'dev'; // 'dev' o 'prod'

    // CREDENCIALES (Cámbialas por las que te envió Niubiz al correo)
    private $user = 'tu_usuario_integracion@comercio.com';
    private $password = 'tu_password_integracion'; 
    private $merchantId = 'tu_merchant_id'; 

    private $url_security;
    private $url_session;
    private $url_auth;

    public function __construct() {
        if ($this->env === 'dev') {
            $this->url_security = "https://apisandbox.vnforapps.com/api.security/v1/security";
            $this->url_session  = "https://apisandbox.vnforapps.com/api.ecommerce/v2/ecommerce/token/session/";
            $this->url_auth     = "https://apisandbox.vnforapps.com/api.authorization/v3/authorization/ecommerce/";
        } else {
            $this->url_security = "https://api.vnforapps.com/api.security/v1/security";
            $this->url_session  = "https://api.vnforapps.com/api.ecommerce/v2/ecommerce/token/session/";
            $this->url_auth     = "https://api.vnforapps.com/api.authorization/v3/authorization/ecommerce/";
        }
    }

    // 1. OBTENER TOKEN DE SEGURIDAD (Dura 15 min)
    public function getSecurityToken() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url_security);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Basic " . base64_encode($this->user . ":" . $this->password)
        ]);
        
        $response = curl_exec($ch);
        
        if(curl_errno($ch)) throw new Exception(curl_error($ch));
        
        curl_close($ch);
        return $response; // Devuelve string simple
    }

    // 2. CREAR SESIÓN (Vincula el monto a la transacción)
    public function createSession($amount, $token) {
        $body = [
            "channel" => "web",
            "amount" => (float)$amount,
            "antifraud" => [
                "clientIp" => $_SERVER['REMOTE_ADDR'],
                "merchantDefineData" => [
                    "MDD4" => "integracion_maquimpower"
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url_session . $this->merchantId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: " . $token
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
        
        $json = json_decode($response, true);
        
        if (isset($json['sessionKey'])) {
            return $json['sessionKey'];
        } else {
            throw new Exception("Error Niubiz Session: " . $response);
        }
    }

    public function getMerchantId() {
        return $this->merchantId;
    }
}
?>