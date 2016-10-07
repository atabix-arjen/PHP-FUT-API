<?php
/**
 * @author JKetelaar
 */

namespace JKetelaar\fut\bot\user;

use Curl\Curl;
use JKetelaar\fut\bot\API;
use JKetelaar\fut\bot\config\Comparisons;
use JKetelaar\fut\bot\config\Configuration;
use JKetelaar\fut\bot\config\URL;
use JKetelaar\fut\bot\errors\login\MainLogin;
use JKetelaar\fut\bot\errors\NulledTokenFunction;
use JKetelaar\fut\bot\web\Parser;
use simplehtmldom_1_5\simple_html_dom;

class Login {

    const COOKIE_FILE = '';

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $nucleusId;

    /**
     * @var array
     */
    private $shardInfos;

    /**
     * Login constructor.
     *
     * @param User $user
     */
    public function __construct(User $user) {
        $this->user = $user;
        $this->curl = $this->setupCurl();
    }

    private function setupCurl() {
        $curl = new Curl();

        $curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
        $curl->setOpt(CURLOPT_ENCODING, Configuration::HEADER_ACCEPT_ENCODING);
        $curl->setHeader('Accept-Language', Configuration::HEADER_ACCEPT_LANGUAGE);
        $curl->setHeader('Cache-Control', Configuration::HEADER_CACHE_CONTROL);
        $curl->setHeader('Accept', Configuration::HEADER_ACCEPT);
        $curl->setHeader('DNT', Configuration::HEADER_DNT);
        $curl->setUserAgent(Configuration::HEADER_USER_AGENT);
        $curl->setCookieFile(DATA_DIR . '/cookies.txt');
        $curl->setCookieJar(DATA_DIR . '/cookies.txt');

        return $curl;
    }

    public function login() {
        $loginURL = $this->requestMain();
        $codeURL  = $this->postLoginForm($loginURL);
        $this->postTwoFactorForm($codeURL);

    }

    private function requestMain() {
        $this->curl->get(URL::LOGIN_MAIN);
        if($this->curl->error) {
            throw new MainLogin($this->curl->errorCode, $this->curl->errorMessage);
        }

        $document = Parser::getHTML($this->curl->response);
        $title    = Parser::getDocumentTitle($document);

        if($this->isLoggedInPage($title)) {
            $this->getFUTPage();
            die('');
        }

        if(Parser::getDocumentTitle($document) === Comparisons::MAIN_LOGIN_TITLE) {
            return $this->curl->getInfo(CURLINFO_EFFECTIVE_URL);
        } else {
            throw new MainLogin(261582, 'Page not matching main login (' . $title . ')');
        }
    }

    private function isLoggedInPage($title) {
        return $title === Comparisons::LOGGED_IN_TITLE;
    }

    private function getFUTPage() {
        $this->curl->get(URL::LOGIN_NUCLEUS);

        if($this->curl->error) {
            throw new MainLogin($this->curl->errorCode, $this->curl->errorMessage);
        }

        preg_match('/EASW_ID\W*=\W*\'(\d*)\'/', $this->curl->response, $matches);
        if(sizeof($matches > 1) && ($id = $matches[ 1 ]) != null) {
            $this->nucleusId = $id;
            $this->getShards($id);
        } else {
            throw new MainLogin(295717, 'Could not find EAWS ID');
        }
    }

    private function postLoginForm($url) {
        $this->curl->post(
            $url,
            array_merge(
                Configuration::FORM_LOGIN_DEFAULTS,
                [
                    'email'    => $this->user->getUsername(),
                    'password' => $this->user->getPassword(),
                ]
            )
        );

        if($this->curl->error) {
            throw new MainLogin($this->curl->errorCode, $this->curl->errorMessage);
        }

        $document = Parser::getHTML($this->curl->response);
        $title    = Parser::getDocumentTitle($document);

        if($title === Comparisons::LOGIN_FORM_TITLE) {
            return $this->curl->getInfo(CURLINFO_EFFECTIVE_URL);
        } elseif($title === Comparisons::MAIN_LOGIN_TITLE) {
            throw new MainLogin(295712, 'Login failed');
        } else {
            throw new MainLogin(281658, 'Page not matching login form page (' . $title . ')');
        }
    }

    private function postTwoFactorForm($url) {
        $token = $this->user->getToken();
        if($token != null) {
            $this->curl->post(
                $url,
                array_merge(
                    Configuration::FORM_AUTHENTICATION_CODE_DEFAULTS,
                    [
                        'twofactorCode' => $token,
                    ]
                )
            );

            if($this->curl->error) {
                throw new MainLogin($this->curl->errorCode, $this->curl->errorMessage);
            }

            $document = Parser::getHTML($this->curl->response);
            $title    = Parser::getDocumentTitle($document);

            if($title === Comparisons::LOGGED_IN_TITLE) {
                $this->getFUTPage();
            } elseif($title === Comparisons::MAIN_LOGIN_TITLE) {
                throw new MainLogin(285719, 'Could not login');
            } elseif($title === Comparisons::LOGIN_FORM_TITLE) {
                throw new MainLogin(295712, 'Incorrect verification code');
            } elseif($title === Comparisons::NO_AUTHENTICATOR_FORM_TITLE) {
                throw new MainLogin(224107, 'No authenticator set up');
            } else {
                throw new MainLogin(281752, 'Unknown error/page occurred (' . $title . ')');
            }
        } else {
            throw new NulledTokenFunction();
        }
    }

    /**
     * @param null $shards
     */
    private function getAccountInformation($shards = null){
        if($shards == null){
            $shards = $this->shardInfos;
        }

        $curl = $this->setupCurl();

        $curl->setHeader('X-UT-Route', $shards['clientFacingIpPort']);

        $curl->get(URL::LOGIN_ACCOUNTS);
        var_dump($curl->response);
        die();
    }

    private function getShards($id = null) {
        if ($id == null){
            $id = $this->nucleusId;
        }

        $tempCurl = &$this->curl;
        $tempCurl->setOpt(CURLOPT_HTTPHEADER, [ 'Content-Type:application/json' ]);
        $tempCurl->setHeaders(
            [
                'Easw-Session-Data-Nucleus-Id' => $id,
                'X-UT-Embed-Error'             => Configuration::X_UT_EMBED_ERROR,
                'X-UT-Route'                   => Configuration::X_UT_ROUTE,
                'X-Requested-With'             => Configuration::X_REQUESTED_WITH,
                'Referer'                      => URL::REFERER,
            ]
        );

        $tempCurl->get(URL::LOGIN_SHARDS);

        if($tempCurl->error) {
            throw new MainLogin($this->curl->errorCode, $this->curl->errorMessage);
        }

        if (($response = $tempCurl->response) != null) {
            if (($shards = json_decode(json_encode($tempCurl->response), true)) != null){
                foreach($shards['shardInfo'] as $shard){
                    foreach($shard['platforms'] as $platform){
                        if ($platform === API::getPlatform($this->user->getPlatform())){
                            $this->shardInfos = $shard;
                        }
                        var_dump($platform);
                    }
                }
                die();
                $this->getAccountInformation();
            }else{
                throw new \Exception(289684, 'Could not decode shards');
            }
        }else{
            throw new \Exception(292751, 'No response received for shards');
        }
    }
}