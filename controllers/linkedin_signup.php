 <?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Linkedin_signup extends CI_Controller {

   function __construct(){
 
        parent::__construct();
        $this->load->helper('url'); 
        $this->load->model('User');
               
     }
 
        function index(){
            
            
            echo '<p>Adapted and simplified from https://github.com/gauravtechie/Codeigniter-linkedin</p>';
            
            echo '<p>-------------------------------------------------------------------------</p>';
            echo '<p>1) Make sure OAuth.php and linkedin.php files are in application/libraries folder</p>';
            echo '<p>2) If you don\'t have the files. Download from http://code.google.com/p/simple-linkedinphp/downloads/list</p>';
            echo '<p>3) Rename linkedin_3.2.0.class.php to linkedin.php if you prefer</p>';
            echo '<p>-------------------------------------------------------------------------</p>';
            
            echo '<form id="linkedin_connect_form" action="linkedin_signup/initiate" method="post">';
            echo '<input type="submit" value="Login with LinkedIn" />';
            echo '</form>';
  
        }
        
        function initiate(){
            
                // setup before redirecting to Linkedin for authentication.
                 $linkedin_config = array(
                     'appKey'       => 'xxxxxxxx',
                     'appSecret'    => 'xxxxxxxx',
                     'callbackUrl'  => 'xxx=url=xxx/linkedin_signup/data/'
                 );
                
                $this->load->library('linkedin', $linkedin_config);
                $this->linkedin->setResponseFormat(LINKEDIN::_RESPONSE_JSON);
                $token = $this->linkedin->retrieveTokenRequest();
                
                $this->session->set_flashdata('oauth_request_token_secret',$token['linkedin']['oauth_token_secret']);
                $this->session->set_flashdata('oauth_request_token',$token['linkedin']['oauth_token']);
                
                $link = "https://api.linkedin.com/uas/oauth/authorize?oauth_token=". $token['linkedin']['oauth_token'];  
                redirect($link);
        }
        
        function cancel() {
            
            // See https://developer.linkedin.com/documents/authentication
            // You need to set the 'OAuth Cancel Redirect URL' parameter to <your URL>/linkedin_signup/cancel

            echo 'Linkedin user cancelled login';            
        }
        
        function logout() {
                session_unset();
                $_SESSION = array();
                echo "Logout successful";
        }
        
        function data(){
  
                 $linkedin_config = array(
                     'appKey'       => 'zu0i26cgsfy6',
                     'appSecret'    => 'PN94zDi126GFupc4',
                     'callbackUrl'  => 'http://code-appstack.rhcloud.com/index.php/linkedin_signup/data/'
                 );
                
                $this->load->library('linkedin', $linkedin_config);
                $this->linkedin->setResponseFormat(LINKEDIN::_RESPONSE_JSON);
                 
                $oauth_token = $this->session->flashdata('oauth_request_token');
                $oauth_token_secret = $this->session->flashdata('oauth_request_token_secret');
  
                $oauth_verifier = $this->input->get('oauth_verifier');
                $response = $this->linkedin->retrieveTokenAccess($oauth_token, $oauth_token_secret, $oauth_verifier);
                
                // ok if we are good then proceed to retrieve the data from Linkedin
                if($response['success'] === TRUE) {
                    
                // From this part onward it is up to you on how you want to store/manipulate the data 
                $oauth_expires_in = $response['linkedin']['oauth_expires_in'];
                $oauth_authorization_expires_in = $response['linkedin']['oauth_authorization_expires_in'];
                
                $response = $this->linkedin->setTokenAccess($response['linkedin']);
                $profile = $this->linkedin->profile('~:(id,first-name,last-name,picture-url,location,headline,email-address)');
                $profile_connections = $this->linkedin->profile('~/connections:(id,first-name,last-name,picture-url,industry)');
                $user = json_decode($profile['linkedin']);
                print_r($user);
                $user_array = array('linkedin_id' => $user->id , 'second_name' => $user->lastName , 'profile_picture' => $user->pictureUrl , 'first_name' => $user->firstName, 'headline' => $user->headline,  'location' => $user->location,'email-address' => $user->emailAddress);

                if($this->User->isMember($user->id) == 0 ){
                    $data = array(
                        'email' =>$user->emailAddress,
                        'name'  =>$user->firstName." ".$user->lastName,
                        'imgurl'=>$user->pictureUrl,
                        'linkedin_id'=>$user->id,
                        'publicurl' => '#',
                        'coins' => '0'
                    );
                    $this->User->save($data);
                }
                //uid
                $this->session->set_userdata('user_data', $data);
                redirect('/home');
                
                } else {
                  // bad token request, display diagnostic information
                  echo "Request token retrieval failed:<br /><br />RESPONSE:<br /><br />" . print_r($response, TRUE);
                }         
        }
}
 
        
