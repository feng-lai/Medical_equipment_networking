<?php
/*
 +----------------------------------------------------------------------
 + Title        : AccessToken 控制器
 + Author       : Randy_chen
 + Version      : V1.0.0.1
 + Initial-Time : 2021/11/3 22:06
 + Last-time    : 2021/11/3 22:06+ chen2
 + Desc         : AccessToken
 +----------------------------------------------------------------------
*/

namespace agora\src;

class AccessToken {
    const Privileges = [
        "kJoinChannel"        => 1,
        "kPublishAudioStream" => 2,
        "kPublishVideoStream" => 3,
        "kPublishDataStream"  => 4,
        "kRtmLogin"           => 1000,
    ];

    public $appID, $appCertificate, $channelName, $uid;
    public                                        $message;

    function __construct() {
        $this->message = new Message();
    }

    static function init($appID, $appCertificate, $channelName, $uid) {
        $accessToken = new AccessToken();

        if (
            !$accessToken->is_nonempty_string("appID", $appID) || !$accessToken->is_nonempty_string("appCertificate", $appCertificate) || !$accessToken->is_nonempty_string("channelName", $channelName)) {
            return null;
        }

        $accessToken->appID = $appID;
        $accessToken->appCertificate = $appCertificate;
        $accessToken->channelName = $channelName;

        $accessToken->setUid($uid);
        $accessToken->message = new Message();
        return $accessToken;
    }

    function is_nonempty_string($name, $str) {
        if (is_string($str) && $str !== "") {
            return true;
        }
        echo $name . " check failed, should be a non-empty string";
        return false;
    }

    function setUid($uid) {
        if ($uid === 0) {
            $this->uid = "";
        } else {
            $this->uid = $uid . '';
        }
    }

    static function initWithToken($token, $appCertificate, $channel, $uid) {
        $accessToken = new AccessToken();
        if (!$accessToken->extract($token, $appCertificate, $channel, $uid)) {
            return null;
        }
        return $accessToken;
    }

    function extract($token, $appCertificate, $channelName, $uid) {
        $ver_len = 3;
        $appid_len = 32;
        $version = substr($token, 0, $ver_len);
        if ($version !== "006") {
            echo 'invalid version ' . $version;
            return false;
        }

        if (
            !$this->is_nonempty_string("token", $token) || !$this->is_nonempty_string("appCertificate", $appCertificate) || !$this->is_nonempty_string("channelName", $channelName)) {
            return false;
        }

        $appid = substr($token, $ver_len, $appid_len);
        $content = (base64_decode(substr($token, $ver_len + $appid_len, strlen($token) - ($ver_len + $appid_len))));

        $pos = 0;
        $len = unpack("v", $content . substr($pos, 2))[1];
        $pos += 2;
        $sig = substr($content, $pos, $len);
        $pos += $len;
        $crc_channel = unpack("V", substr($content, $pos, 4))[1];
        $pos += 4;
        $crc_uid = unpack("V", substr($content, $pos, 4))[1];
        $pos += 4;
        $msgLen = unpack("v", substr($content, $pos, 2))[1];
        $pos += 2;
        $msg = substr($content, $pos, $msgLen);

        $this->appID = $appid;
        $message = new Message();
        $message->unpackContent($msg);
        $this->message = $message;

        //non reversable values
        $this->appCertificate = $appCertificate;
        $this->channelName = $channelName;
        $this->setUid($uid);
        return true;
    }

    function addPrivilege($key, $expireTimestamp) {
        $this->message->privileges[$key] = $expireTimestamp;
        return $this;
    }

    function build() {
        $msg = $this->message->packContent();
        $val = array_merge(unpack("C*", $this->appID), unpack("C*", $this->channelName), unpack("C*", $this->uid), $msg);

        $sig = hash_hmac('sha256', implode(array_map("chr", $val)), $this->appCertificate, true);

        $crc_channel_name = crc32($this->channelName) & 0xffffffff;
        $crc_uid = crc32($this->uid) & 0xffffffff;

        $content = array_merge(unpack("C*", $this->packString($sig)), unpack("C*", pack("V", $crc_channel_name)), unpack("C*", pack("V", $crc_uid)), unpack("C*", pack("v", count($msg))), $msg);
        $version = "006";
        $ret = $version . $this->appID . base64_encode(implode(array_map("chr", $content)));
        return $ret;
    }

    function packString($value) {
        return pack("v", strlen($value)) . $value;
    }
}
