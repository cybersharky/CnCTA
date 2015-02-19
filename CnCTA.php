<?php

/**
 * GNU Public License 3.0
 * Copyright (C) 2014 Gary Coleman <cybershark@gmail.com>
 * 
 * PHP Curl Class for getting game data from Command & Conquer Tiberium Alliances       
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * */
class CnCTA {

    private static $instance;
    private $user;
    private $password;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new CnCTA ();
        }

        return self::$instance;
    }

    public function login($user, $password) {
        $this->user = $user;
        $this->password = $password;
        $this->cookie = apache_getenv("TMP") . '\\' . md5($this->user) . '.txt'; //change to suitable location, tmp path assumes apache server
        $this->agent = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.114 Safari/537.36';
        $_login_fields = array('spring-security-redirect' => '',
            'id' => '',
            'timezone' => '2',
            'j_username' => $this->user,
            'j_password' => $this->password,
            '_web_remember_me' => ''
        );

        // login with account data
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->agent);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, 'https://www.tiberiumalliances.com/j_security_check');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_login_fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
    }

//
//// login end
//
//
    public function LastWorld() {
        $ch = curl_init();
        $urlL = 'https://www.tiberiumalliances.com/game/launch';
        curl_setopt($ch, CURLOPT_URL, $urlL);
        curl_setopt($ch, CURLOPT_REFERER, 'https://www.tiberiumalliances.com/login/auth');
        curl_setopt($ch, CURLOPT_USERAGENT, $this->agent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        if (empty($result)) {
            print('LastWorld Curl result was empty');
            exit();
        } elseif ($result === FALSE) {
            $errno = curl_errno($ch);
            $errormessage = curl_strerror($errno);
            print($errno . ': \t' . $errormessage);
        } else {

            //}

            if (preg_match('/sessionId\" value=\"([^"]+)"/', $result, $match)) {
                $this->sessionid = $match[1];
            } else {
                // didn't find sessionId
            }
// grab last used server
            if (preg_match('/([^"]+)\/index\.aspx/', $result, $match)) {
                // ok...we can make it better ;)
                //$_last_serverId = substr(parse_url($match[1], PHP_URL_PATH), 1);

                $this->sessionserver = $match[1];
                $this->referrer = $match[0];
            }
        }
    }

    //Open Session
    public function OpenSession() {
        $data = array(
            'session' => $this->sessionid,
            'reset' => true,
            'refId' => -1,
            'version' => -1,
            'platformId' => 1
        );
        $url = $this->sessionserver . '/Presentation/Service.svc/ajaxEndpoint/';
        $invalid = "00000000-0000-0000-0000-000000000000";
        $result = $this->getData($url, 'OpenSession', $data);
        //$this->sessionkey = $result->i;
        $tries = 0;
        $maxtries =2;
        while (($result->i == $invalid) && ($tries < $maxtries)) {
            sleep(2); //Lets not flood the server
            $result = $this->getData($url, 'OpenSession', $data);
            $this->sessionkey = $result->i;
            $tries++;
        }

        if ($result->i === $invalid) {
            print 'invalid Session ID:' . $result->i;
            exit();
        
        } else {

            return $this->sessionkey = $result->i;
        }
    }

    public function prepData($endpoint, $data = array()) {
        $data = array_merge(array('session' => $this->sessionkey), $data);
        $url = $this->sessionserver . '/Presentation/Service.svc/ajaxEndpoint/';
        return $this->getData($url, $endpoint, $data);
        //return $data;
    }

    public function getData($url = Null, $endpoint, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . $endpoint);
        curl_setopt($ch, CURLOPT_REFERER, $this->referrer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->agent);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json; charset=utf-8", "Cache-Control: no-cache", "Pragma: no-cache", "X-Qooxdoo-Response-Type: application/json"));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_SLASHES));
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
        $result = curl_exec($ch);
        if (empty($result)) {
            print('getData Curl result was empty');
            exit();
        } elseif ($result === FALSE) {
            $errno = curl_errno($ch);
            $errormessage = curl_strerror($errno);
            print($errno . ': \t' . $errormessage);
        } else {
            $results = json_decode($result);
            return $results;
            //}
        }
        curl_close($ch);
    }

}



// class end
