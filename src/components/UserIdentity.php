<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
    protected $_id;
    protected $photo;

    /**
     * Authenticates a user.
     * @return boolean whether authentication succeeds.
     */
    public function authenticate()
    {
        $user = User::model()->find('LOWER(username)=?', array(strtolower($this->username)));

        if ($user === null)
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        else if (!$user->validatePassword($this->password))
            $this->errorCode = self::ERROR_PASSWORD_INVALID;
        else {
            $this->_id = $user->id;
            $this->username = $user->username;
            $this->setState('photo', Yii::app()->request->baseUrl.'/images/default.jpg');
            $this->errorCode = self::ERROR_NONE;
        }
        return $this->errorCode == self::ERROR_NONE;
    }

    /**
     * @return integer the ID of the user record
     */
    public function getId()
    {
        return $this->_id;
    }

    private $passphrase = "H2BQdUoA9p786wcvsjwwf2xd4TFphztzgf0VQqcDHuHP6HOgLiDBYFvbiGw2";
    private $time_expire = 604800;

    private function getIdentityData()
    {
        return [
            "id" => $this->_id,
            "username" => $this->username,
            "timestamp" => time()
        ];
    }

    private function setIdentityData($data)
    {
        if (time() - $data['timestamp'] > $this->time_expire) {
            return false;
        }

        $this->_id = $data['id'];
        $this->username = $data['username'];
        return true;
    }

    public function getEncriptedIdentity()
    {

        $cipher = new Cipher($this->passphrase);
        $decryptedtext = $this->getIdentityData();
        $encryptedtext = $cipher->encryptJson($decryptedtext);
        return $encryptedtext;

    }

    public function applyDecriptedIdentity($encryptedtext)
    {
        $cipher = new Cipher($this->passphrase);
        $decryptedtext = $cipher->decryptJson($encryptedtext);
        if (!$decryptedtext) {
            return 'invalid';
        }
        if (!$this->setIdentityData($decryptedtext)) {
            return 'expire';
        }

        return true;

    }


}