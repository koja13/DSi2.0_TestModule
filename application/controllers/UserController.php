<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class UserController extends CI_Controller{
	
	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->model('UserModel');
	}
	
	// otvara Sign In stranu automatski
	public function index()
	{
		if(($this->session->userdata('user_name')!=""))
		{
			$this->welcome();
		}
		else{
			$data['title']= 'Sign In | DSi2.0';
			$this->load->view('HeaderView',$data);
			$this->load->view("SignInView.php", $data);
			$this->load->view('FooterView',$data);
		}
	}
	
	// otvara stranu za logovanje korisnika, sign in stranu
	public function login()
	{
		$username=$this->input->post('user_name');
		$password=md5($this->input->post('pass'));
	
		$result=$this->UserModel->login($username,$password);
		if($result)
		{
			$this->welcome();
		}
		else
		{
			//$this->index();
			if(($this->session->userdata('user_name')!=""))
			{
				$this->welcome();
			}
			else{
				$data['title']= 'Sign In | DSi2.0';
				$data['error_message']= "Login failed. Try again!";
				$this->load->view('HeaderView',$data);
				$this->load->view("SignInView.php", $data);
				$this->load->view('FooterView',$data);
			}
				
		}
	
	}
	
	// otvara stranu za logovanje nakon uspesnog registorovanja i ispisuje da je registovanje uspesno
	public function thanks()
	{
		$data['title']= 'Sign In | DSi2.0';
		$data['message'] = "Thanks for registering!";
		$this->load->view('HeaderView',$data);
		$this->load->view("SignInView.php", $data);
		//$this->load->view('thank_view.php', $data);
		$this->load->view('FooterView',$data);
	}
	
	// otvara stranu za registrovanje
	public function registration()
	{
		$this->load->library('form_validation');
		// field name, error message, validation rules
		$this->form_validation->set_rules('user_name', 'User Name', 'trim|required|min_length[4]|xss_clean|is_unique[user.username]');
		$this->form_validation->set_rules('email_address', 'Your Email', 'trim|required|valid_email|is_unique[user.email]');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[4]|max_length[32]');
		$this->form_validation->set_rules('con_password', 'Password Confirmation', 'trim|required|matches[password]');
	
		if($this->form_validation->run() == FALSE)
		{
			//$this->index();
			$this->register();
			//echo validation_errors();
		}
		else
		{
			$this->UserModel->add_user();
			$this->thanks();
		}
	}
	
	// redirektuje na stranu za registrovanje
	public function register()
	{
		if(($this->session->userdata('user_name')!=""))
		{
			$this->welcome();
		}
		else{
			$data['title']= 'Registration | DSi2.0';
			$this->load->view('HeaderView',$data);
			$this->load->view("RegistrationView.php", $data);
			$this->load->view('FooterView',$data);
		}
	}
	
	public function getUserDataFB()
	{
		$name = $_POST['name'];
		$username = $_POST['username'];
		$email = $_POST['email'];
		$account_type = $_POST['account_type'];
		
		$this->UserModel->addUserFB($name, $username, $email, $account_type);
	}
	
	
	// otvara stranu koja sledi nakon logovanja - Welcome stranu
	public function welcome()
	{
		$data['title']= 'Welcome | DSi2.0';
		$this->load->view('HeaderView',$data);
		$this->load->view('WelcomeView.php', $data);
		$this->load->view('FooterView',$data);
	}
	
	// otvara stranu sa lekcijama, glavni pogled
	public function start()
	{
		$data['title']= 'DSi2.0';
		$this->load->view('HeaderView',$data);
		$this->load->view('MainView.php', $data);
		$this->load->view('FooterView',$data);
	}
	
	// fja koja odgovara na ajax poziv klijenta, upisuje u bazu koja rec na koju je prevucena
	public function getUserActions()
	{
		$currentLessionNumber = $_POST['currentLessionNumber'];
		$subject = $_POST['subject'];
		$object = $_POST['object'];
		$currentDateTime = $_POST['currentDateTime'];
		
		$this->UserModel->saveUserActions($currentLessionNumber, $subject, $object, $currentDateTime);
	}
	
	// fja koja odgovara na ajax poziv klijenta, upisuje u bazu akciju koju je korisnik obavio, next, prev,...
	public function getUserActionsLessions()
	{
		$currentLessionNumber = $_POST['currentLessionNumber'];
		$action = $_POST['action'];
		$next_prev_lession_number = $_POST['next_prev_lession_number'];
		$currentDateTime = $_POST['currentDateTime'];
		
		$this->UserModel->saveUserActionsLessions($currentLessionNumber, $action, $next_prev_lession_number, $currentDateTime);
	}
	
	// otvara stranu koja sledi nakon citanja lekcija, welcome strana za kviz
	public function startQuiz()
	{
		$data['title']= 'Start quiz | DSi2.0';
		$this->load->view('HeaderView',$data);
		$this->load->view('welcomeViewQuiz.php', $data);
		$this->load->view('FooterView',$data);
	}
	
	// otvara stranu sa kvizom
	public function quiz()
	{
		$data=$this->UserModel->getQuestions();
		
		$data['title']= 'Quiz | DSi2.0';
		$this->load->view('HeaderQuizView',$data);
		$this->load->view('QuizView.php', $data);
		$this->load->view('FooterView',$data);
	}
	
	// fja koja odgovara na ajax poziv klijenta, upisuje u bazu rezultate kviza
	public function getQuizResults()
	{
		$userAnswers = $_POST['userAnswers'];
		$currentDateTime = $_POST['currentDateTime'];
		$message = $this->UserModel->saveQuizResults($userAnswers);
		//$this->UserModel->saveUserActionsLessions(null, "finish_quiz", null, $currentDateTime);
		
		echo $message;
	}
	
	// fja koja otvara stranu sa rezultatima kviza
	public function QuizResultPage()
	{
		$data = $this->UserModel->getResults();
		
		$data['title'] = 'Quiz results | DSi2.0';
		$this->load->view('HeaderQuizView',$data);
		$this->load->view('QuizResultsView.php', $data);
		$this->load->view('FooterView',$data);
	}
	
	//fja koja izloguje korisnika, obrise podatke iz sesije i pokrene sign in stranu
	public function logout()
	{
		// upisivanje informacije o logout-u u bazu
		$this->UserModel->saveUserActionsLessions(null, "logged_out", null, date('Y-m-d H:i:s'));
		
		$newdata = array(
		'user_id'   =>'',
		'user_name'  =>'',
		'user_email'     => '',
		'logged_in' => FALSE,
		);
		
		
		$this->session->unset_userdata($newdata);
		$this->session->sess_destroy();
		$this->index();
	}
}
?>