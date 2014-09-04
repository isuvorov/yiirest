<?php



class Cipher
{
    private $securekey, $iv;

    function __construct($textkey)
    {
        $this->securekey = hash('sha256', $textkey, TRUE);
        $this->iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
    }

    function encrypt($input)
    {
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->securekey, $input, MCRYPT_MODE_ECB, $this->iv));
    }

    function encryptJson($object)
    {
        return $this->encrypt(json_encode($object));
    }

    function decrypt($input)
    {
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->securekey, base64_decode($input), MCRYPT_MODE_ECB, $this->iv));
    }

    function decryptJson($input)
    {
        return json_decode($this->decrypt($input), 1);
    }
}
