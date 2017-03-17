<?php
/**
 * Contains the component used for encrypting and decrypting data.
 *
 * @link http://www.creationgears.com/
 * @copyright Copyright (c) 2015 Nicola Puddu
 * @license http://www.gnu.org/copyleft/gpl.html
 * @package nickcv/yii2-encrypter
 * @author Nicola Puddu <n.puddu@outlook.com>
 */
namespace dezmont765\yii2bundle\components;

use yii\base\Component;

/**
 * Encrypter is the class that is used to encrypt and decrypt the data.
 *
 * @author Nicola Puddu <n.puddu@outlook.com>
 * @version 1.0
 */
class Encryption extends Component
{
    /**
     * 128 bites cypher method used by the openssl functions.
     */
    const AES128 = 'aes-128-cbc';
    /**
     * 256 bites cypher method used by the openssl functions.
     */
    const AES256 = 'aes-256-cbc';
    /**
     * Size in bites of the IV required by the cypher methods.
     */
    const IV_LENGTH = 16;

    /**
     * Contains the global password used to encrypt and decrypt.
     *
     * @var string
     */
    private static $_globalPassword = 'encryption-is-so-strong-in-2017';


    /**
     * Encrypts a string.
     *
     * @param string $string the string to encrypt
     * @return string the encrypted string
     */
    public static function encode($string) {
        $encryptedString = openssl_encrypt($string, self::getCypherMethod(), self::$_globalPassword, true,
                                           random_bytes(self::IV_LENGTH));
        $encryptedString = base64_encode($encryptedString);
        return $encryptedString;
    }


    /**
     * Decrypts a string.
     * False is returned in case it was not possible to decrypt it.
     *
     * @param string $string the string to decrypt
     * @return string the decrypted string
     */
    public static function decode($string) {
        $decodedString = $string;
        $decodedString = base64_decode($decodedString);
        return openssl_decrypt($decodedString, self::getCypherMethod(), self::$_globalPassword, true,
                               random_bytes(self::IV_LENGTH));
    }


    /**
     * Returns the cypher method based on the current configuration.
     *
     * @return string the cypher method
     */
    private static function getCypherMethod() {
        return self::AES128;
    }
}