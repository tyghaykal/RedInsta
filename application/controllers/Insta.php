<?php
// session_start();
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';
use InstagramAPI\Instagram;
use InstagramAPI\Request\Live;
use InstagramAPI\Response\Model\User;
use InstagramAPI\Response\Model\Comment;
use SuperClosure\Serializer;

class Insta extends CI_Controller {

	public $ig_data;
	public $lg;

	public function index()
	{
		
		// session_unset();
		// $_SESSION['log'] = 0;
		// $_SESSION['ig'] = null;
		// setcookie('lg', null, time() + (60 * 60 * 24));
		// setcookie('ig', null, time() + (60 * 60 * 24));
		// unset($_SESSION['password']);
		$this->load->view('header');
		$this->load->view('login_page');
		$this->load->view('footer');
		$this->drop();
		unset($_SESSION['flash']['failed']);
	}

	
	public function fa()
	{
		
		$this->load->view('header');
		$this->load->view('fa');
		$this->load->view('footer');
		
		
		
	}

	public function drop(){
		session_destroy();
		setcookie('ig', '', 1,'/');
		setcookie('lg', '', 1,'/');
		if (isset($_SERVER['HTTP_COOKIE'])) {
			$cookies = explode(';', $_SERVER['HTTP_COOKIE']);
			foreach($cookies as $cookie) {
				$parts = explode('=', $cookie);
				$name = trim($parts[0]);
				setcookie($name, '', time()-1000);
				setcookie($name, '', time()-1000, '/');
			}
		}
	}










	public function process(){

		Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;
		$storageConfig = [
            "storage" => "file",
            "basefolder" => "sessions/",
        ];
		
		$Instagram = new \InstagramAPI\Instagram(false, false, $storageConfig);
		$username = $_POST['username'];
		$password = $_POST['pass'];
		
		// dump($username);
		// dump($password);
		
		
		$logged_in = false;
		try {
			$login_resp = $Instagram->login($username, $password);
			
            if ($login_resp !== null && $login_resp->isTwoFactorRequired()) {
                
                $identifier = $login_resp->getTwoFactorInfo()->getTwoFactorIdentifier();
				$_SESSION['faid'] = $identifier;
                $_SESSION["2FA_".$identifier] = [
                    "username" => $username,
                    "password" => $password
				];
				return redirect('fa');
            } else if ($login_resp) {
                $logged_in = true;
            }
		} catch (InstagramAPI\Exception\ChallengeRequiredException $e) {
            $this->_handleCheckpointException($username, $password, null);
        }catch (\Exception $e) {
			// dump($e);
			// die();
			if (strpos($e->getMessage(), "Challenge") !== false) {
				$_SESSION['flash']['failed'] = 'Account need to set 2FA for security, after that you need to open IG and click it was me!';		
				return redirect('/');		
			}
			$err = explode("InstagramAPI\Response\LoginResponse:", $e->getMessage());
			$countERR = count($err);
			$_SESSION['flash']['failed'] = 'Error While Logging in to Instagram: ' . $err[$countERR-1];
			return redirect('/');
			
		}
		// die();
		$this->create_live($Instagram);
	}

	public function process_fa(){
		$security_code = $_POST["twofa-security-code"];
        $twofaid = $_POST["2faid"];

        

        // These variables have been saved to the session after validation
        // There is no need to validate them again here.
        $username = $_SESSION["2FA_".$twofaid]["username"];
		$password = $_SESSION["2FA_".$twofaid]["password"];
		dump($username);
		dump($password);
		dump($security_code);
		dump($twofaid);
		Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;
		$storageConfig = [
            "storage" => "file",
            "basefolder" => "sessions/",
        ];
		
		$Instagram = new \InstagramAPI\Instagram(false, false, $storageConfig);
		dump($Instagram);
		try{
			$resp = $Instagram->finishTwoFactorLogin($username, $password, $twofaid, $security_code);
		} catch (InstagramAPI\Exception\ChallengeRequiredException $e) {
            $this->_handleCheckpointException($username, $password, null);
            $this->jsonecho();
        }
		catch (\Exception $e) {
			dump($e);
			die();
			
			$err = explode("InstagramAPI\Response\LoginResponse:", $e->getMessage());
			$countERR = count($err);
			$_SESSION['flash']['failed'] = 'Error : ' . $err[$countERR-1];
			return redirect('/');
			
		}

		$this->create_live($Instagram);
	}

	public function create_live($ig){
		try {
			if (!$ig->isMaybeLoggedIn) {
				echo("Couldn't Login! Exiting!");
				exit();
			}
			echo("Logged In! Creating Livestream...<br>");
			$stream = $ig->live->create();
			// var_dump($stream);
			$broadcastId = $stream->getBroadcastId();
			$ig->live->start($broadcastId);
			// Switch from RTMPS to RTMP upload URL, since RTMPS doesn't work well.
			$streamUploadUrl = preg_replace(
				'#^rtmps://([^/]+?):443/#ui',
				'rtmp://\1:80/',
				$stream->getUploadUrl()
			);

			//Grab the stream url as well as the stream key.
			$split = preg_split("[" . $broadcastId . "]", $streamUploadUrl);

			$streamUrl = $split[0];
			$streamKey = $broadcastId . $split[1];

			echo("<br>================================ Stream URL ================================<br>" . $streamUrl . "<br>================================ Stream URL ================================<br>");

			echo("<br>======================== Current Stream Key ========================<br>" . $streamKey . "<br>======================== Current Stream Key ========================<br>");

			echo("<br>^^ Please Start Streaming in OBS/Streaming Program with the URL and Key Above ^^<br>");

			
				echo("<br>You are using Windows! Therefore, your system supports the viewing of comments and likes!\nThis window will turn into the comment and like view and console output.\nA second window will open which will allow you to dispatch commands!");
				

		} catch (\Exception $e) {
			echo 'Error While Creating Livestream: ' . $e->getMessage() . "\n";
		}
	}

	private function _handleCheckpointException($username, $password, $proxy = null)
    {
        
        
        // Login via web
        $chk_web = false;
		dump($username);
		dump($password);
        $cookies_dir = "sessions/".$username;
        $Checkpoint = new \Checkpoint(false, $proxy, $cookies_dir);
        $Checkpoint->doFirstStep();
		dump($Checkpoint);
		$chk_login_resp = $Checkpoint->login($username, $password);
		dump($chk_login_resp);
        $this->resp->checkpoint_url = $chk_login_resp->checkpoint_url;
        $this->resp->chk_login_resp = $chk_login_resp;
		dump($chk_login_resp);
		die();

        if (isset($chk_login_resp->checkpoint_url)) {
            $chk_choice_resp = $Checkpoint->selectChoice(\Checkpoint::EMAIL);
            $this->resp->chk_choice_resp = $chk_choice_resp;

            if (isset($chk_choice_resp->status) && $chk_choice_resp->status == "ok") {
                // Checkpoint URL found
                // Use the new method to bypass the checkpoint with
                // security code send to the email address (or SMS in the future)
                $chk_web = true;
                $this->resp->checkpoint_required = true;
                $this->resp->identifier = uniqid();

                if ($chk_choice_resp->fields->form_type == "phone_number") {
                    $this->resp->msg = __(
                        "Enter the code sent to your number ending in %s", 
                        $chk_choice_resp->fields->contact_point);
                } else {
                    $this->resp->msg = __(
                        "Enter the 6-digit code sent to the email address %s", 
                        $chk_choice_resp->fields->contact_point);
                }

                $_SESSION["CHECKPOINT_".$this->resp->identifier] = [
                    "checkpoint" => serialize($Checkpoint),
                    "username" => $username,
                    "proxy" => $proxy
                ];
            }
        }

        if (!$chk_web) {
            // Checkpoint URL not found
            // Use the classic method to bypass the checkpoint
            $this->resp->msg = __("Please goto <a href='http://instagram.com' target='_blank'>instagram.com</a> and pass checkpoint!");
        }
    }
}
