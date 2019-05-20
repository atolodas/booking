<?php
/**
 * 公私钥加解密
 */
namespace app\lib\rsa_key;


class Rsakey
{
    private $private_key = '';
    private $public_key = '';

    public function __construct()
    {
        require_once __DIR__ . '/RsakeyConfig.php';
        $this->private_key = $rsa_config['private_key'];
        $this->public_key = $rsa_config['public_key'];
    }
    /**
     * 公钥加密
     * @param $data 原始数据，字符串，如数组需转化为json
     * @return mixed
     */
    public function public_encrypt($data){
        if(is_array($data)){
            $data = json_encode($data);
        }
        openssl_public_encrypt($data,$encrypted,$this->public_key);//公钥加密
        $encrypted = base64_encode($encrypted);
        return $encrypted;
    }
    /**
     * 私钥解密
     * @param $encrypted 使用公钥加密后的字符串
     * @return mixed
     */
    public function private_decrypt($encrypted){
        openssl_private_decrypt(base64_decode($encrypted),$decrypted,$this->private_key);//私钥解密
        return $decrypted;
    }
    //公钥分段加密
    public function PublicEncrypt($data){
        //openssl_public_encrypt($data,$encrypted,$this->pu_key);//公钥加密
        if(is_array($data)){
            $data = json_encode($data);
        }
        $crypto = '';
        foreach (str_split($data, 117) as $chunk) {
            openssl_public_encrypt($chunk, $encryptData, $this->public_key);
            $crypto .= $encryptData;
        }
        $encrypted = base64_encode($crypto);
        return $encrypted;
    }
    //私钥分段解密
    public function PrivateDecrypt($encrypted)
    {
        $crypto = '';
        $encrypted = base64_decode($encrypted);
        foreach (str_split($encrypted, 128) as $chunk) {
            openssl_private_decrypt($chunk, $decryptData, $this->private_key);
            $crypto .= $decryptData;
        }
        //$encrypted = $this->urlsafe_b64decode($encrypted);
        //openssl_private_decrypt($encrypted,$decrypted,$this->pi_key);
        return $crypto;
    }
    /**
     * 私钥加密
     * @param $data 原始数据，字符串，如数组需转化为json
     * @return mixed
     */
    public function private_encrypt($data){
        if(is_array($data)){
            $data = json_encode($data);
        }
        openssl_private_encrypt($data,$encrypted,$this->private_key);//私钥加密
        $encrypted = base64_encode($encrypted);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
        return $encrypted;
    }
    /**
     * 公钥解密
     * @param $encrypted 使用私钥加密后的字符串
     * @return mixed
     */
    public function public_decrypt($encrypted){
        openssl_public_decrypt(base64_decode($encrypted),$decrypted,$this->public_key);//私钥加密的内容通过公钥可用解密出来
        return $decrypted;
    }

}