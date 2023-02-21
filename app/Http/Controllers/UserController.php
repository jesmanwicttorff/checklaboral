<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Socialize;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect;
use Mail;

class UserController extends Controller
{


	protected $layout = "layouts.main";

	public function __construct()
	{
		parent::__construct();
	}

	public function getRegister()
	{

		if (CNF_REGIST == 'false') :
			if (\Auth::check()) :
				return Redirect::to('')->with('message', \SiteHelpers::alert('success', \Lang::get('core.login_ready')));
			else :
				return Redirect::to('user/login');
			endif;

		else :

			return view('user.register');
		endif;
	}

	public function postCreate(Request $request)
	{
		//cambia largo de contraseña desde 12 caracteres a 20 caracteres.
		$rules = array(
			'firstname' => 'required|min:2',
			'lastname' => 'required|min:2',
			'email' => 'required|email|unique:tb_users',
			'password' => 'required|between:6,20|confirmed',
			'password_confirmation' => 'required|between:6,20'
		);

		if (CNF_RECAPTCHA == 'true') $rules['recaptcha_response_field'] = 'required|recaptcha';

		$validator = Validator::make($request->all(), $rules);

		//se agrega validación de campo contraseña
		if ($validator->passes() && $request["password"] === $request["password_confirmation"]) {
			$code = rand(10000, 10000000);

			$authen = new User;
			$authen->first_name = $request->input('firstname');
			$authen->last_name = $request->input('lastname');
			$authen->email = trim($request->input('email'));
			$authen->activation = $code;
			$authen->group_id = 15;
			$authen->password = \Hash::make($request->input('password'));
			if (CNF_ACTIVATION == 'auto') {
				$authen->active = '1';
			} else {
				$authen->active = '0';
			}
			$authen->save();

			$data = array(
				'firstname'	=> $request->input('firstname'),
				'lastname'	=> $request->input('lastname'),
				'email'		=> $request->input('email'),
				'password'	=> $request->input('password'),
				'code'		=> $code

			);
			if (CNF_ACTIVATION == 'confirmation') {

				$to = $request->input('email');
				$subject = "[ " . CNF_APPNAME . " ] REGISTRATION ";


				if (defined('CNF_MAIL') && CNF_MAIL == 'swift') {
					Mail::send('user.emails.registration', $data, function ($message) {
						$message->to($to)->subject($subject);
					});
				} else {

					$message = view('user.emails.registration', $data);
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
					$headers .= 'From: ' . CNF_APPNAME . ' <' . CNF_EMAIL . '>' . "\r\n";
					mail($to, $subject, $message, $headers);
				}

				$message = \Lang::get('core.login_register_thanks') . " . " . \Lang::get('core.login_check_active');
			} elseif (CNF_ACTIVATION == 'manual') {
				$message = \Lang::get('core.login_register_thanks') . " . " . \Lang::get('core.login_validate_active');
			} else {
				$message = \Lang::get('core.login_register_thanks') . " . " . \Lang::get('core.login_account_active');
			}


			return Redirect::to('user/login')->with('message', \SiteHelpers::alert('success', $message));
		} else {
			return Redirect::to('user/register')->with(
				'message',
				\SiteHelpers::alert('error', \Lang::get('core.note_error'))
			)->withErrors($validator)->withInput();
		}
	}

	public function getActivation(Request $request)
	{
		$num = $request->input('code');
		if ($num == '')
			return Redirect::to('user/login')->with('message', \SiteHelpers::alert('error', \Lang::get('core.login_invalid_active')));

		$user =  User::where('activation', '=', $num)->get();
		if (count($user) >= 1) {
			\DB::table('tb_users')->where('activation', $num)->update(array('active' => 1, 'activation' => ''));
			return Redirect::to('user/login')->with('message', \SiteHelpers::alert('success', \Lang::get('core.login_account_active')));
		} else {
			return Redirect::to('user/login')->with('message', \SiteHelpers::alert('error', \Lang::get('core.login_invalid_active')));
		}
	}

	public function getLogin()
	{

		if (\Auth::check()) {
			return Redirect::to('')->with('message', \SiteHelpers::alert('success', 'Youre already login'));
		} else {
			$this->data['socialize'] =  config('services');
			return View('user.login', $this->data);
		}
	}

	public function postSignin(Request $request)
	{

		$rules = array(
			'email' => 'required|email',
			'password' => 'required',
		);
		if (CNF_RECAPTCHA == 'true') $rules['captcha'] = 'required|captcha';
		$validator = Validator::make(Input::all(), $rules);
		if ($validator->passes()) {

			$remember = (!is_null($request->get('remember')) ? 'true' : 'false');

			if (\Auth::attempt(array('email' => $request->input('email'), 'password' => $request->input('password')), $remember)) {
				if (\Auth::check()) {

					$row = \DB::table('tb_users')
						->select('tb_users.*', 'tb_groups.level')
						->join('tb_groups', 'tb_groups.group_id', '=', 'tb_users.group_id')
						->where('tb_users.id', '=', \Auth::user()->id)
						->first();

					if ($row->active == '0') {
						// inactive
						if ($request->ajax() == true) {
							return response()->json(['status' => 'error', 'message' => \Lang::get('core.account_blocked')]);
						} else {
							\Auth::logout();
							return Redirect::to('user/login')->with('message', \SiteHelpers::alert('error', \Lang::get('core.account_blocked')));
						}
					} else if ($row->active == '2') {

						if ($request->ajax() == true) {
							return response()->json(['status' => 'error', 'message' => \Lang::get('core.account_blocked')]);
						} else {
							// BLocked users
							\Auth::logout();
							return Redirect::to('user/login')->with('message', \SiteHelpers::alert('error', \Lang::get('core.account_blocked')));
						}
					} else {
						\DB::table('tb_users')->where('id', '=', $row->id)->update(array('last_login' => date("Y-m-d H:i:s")));
						\Session::put('uid', $row->id);
						\Session::put('pid', $request->input('password'));
						\Session::put('gid', $row->group_id);
						\Session::put('level', $row->level);
						if ($row->level == 6) { // si el nivel es 6 es un usuario contratista
							$lobjContratista = \DB::table('tbl_contratistas')
								->where('tbl_contratistas.entry_by_access', '=', $row->id)
								->first();
							if ($lobjContratista) {
								\Session::put('RUT', $lobjContratista->RUT);
								\Session::put('contratista', $lobjContratista->RazonSocial);
							}
						}
						\Session::put('eid', $row->email);
						\Session::put('ll', $row->last_login);
						\Session::put('fid', $row->first_name . ' ' . $row->last_name);
						if (\Session::get('lang') == '') {
							\Session::put('lang', 'es');
						}

						if ($request->ajax() == true) {
							if (CNF_FRONT == 'false') :
								return response()->json(['status' => 'success', 'url' => url('dashboard')]);
							else :
								return response()->json(['status' => 'success', 'url' => url('')]);
							endif;
						} else {
							if (CNF_FRONT == 'false') :
								return Redirect::to('dashboard');
							else :
								return Redirect::to('');
							endif;
						}
					}
				}
			} else {

				if ($request->ajax() == true) {
					return response()->json(['status' => 'error', 'message' => \Lang::get('core.login_error_pass_user')]);
				} else {

					return Redirect::to('user/login')
						->with('message', \SiteHelpers::alert('error', \Lang::get('core.login_error_pass_user')))
						->withInput();
				}
			}
		} else {

			if ($request->ajax() == true) {
				return response()->json(['status' => 'error', 'message' => \Lang::get('core.note_error')]);
			} else {

				return Redirect::to('user/login')
					->with('message', \SiteHelpers::alert('error', \Lang::get('core.note_error')))
					->withErrors($validator)->withInput();
			}
		}
	}

	public function getProfile()
	{

		if (!\Auth::check()) return redirect('user/login');


		$info =	User::find(\Auth::user()->id);
		$this->data = array(
			'pageTitle'	=> 'My Profile',
			'pageNote'	=> 'View Detail My Info',
			'info'		=> $info,
		);
		return view('user.profile', $this->data);
	}

	public function postSaveprofile(Request $request)
	{
		if (!\Auth::check()) return Redirect::to('user/login');
		$rules = array(
			'first_name' => 'required|min:2',
			'last_name' => 'required|min:2',
		);

		if ($request->input('email') != \Session::get('eid')) {
			$rules['email'] = 'required|email|unique:tb_users';
		}

		if (!is_null(Input::file('avatar'))) $rules['avatar'] = 'mimes:jpg,jpeg,png,gif,bmp';


		$validator = Validator::make($request->all(), $rules);

		if ($validator->passes()) {


			if (!is_null(Input::file('avatar'))) {
				$file = $request->file('avatar');
				$destinationPath = './uploads/users/';
				$filename = $file->getClientOriginalName();
				$extension = $file->getClientOriginalExtension(); //if you need extension of the file
				$newfilename = \Session::get('uid') . '.' . $extension;
				$uploadSuccess = $request->file('avatar')->move($destinationPath, $newfilename);
				if ($uploadSuccess) {
					$data['avatar'] = $newfilename;
				}
			}

			$user = User::find(\Session::get('uid'));
			$user->first_name 	= $request->input('first_name');
			$user->last_name 	= $request->input('last_name');
			$user->email 		= $request->input('email');
			if (isset($data['avatar']))  $user->avatar  = $newfilename;
			$user->save();

			$newUser = User::find(\Session::get('uid'));

			\Session::put('fid', $newUser->first_name . ' ' . $newUser->last_name);

			return Redirect::to('user/profile')->with('messagetext', 'El perfil ha sido guardado!')->with('msgstatus', 'success');
		} else {
			return Redirect::to('user/profile')->with('messagetext', \Lang::get('core.note_error'))->with('msgstatus', 'error')
				->withErrors($validator)->withInput();
		}
	}

	public function postSavepassword(Request $request)
	{
		$rules = array(
			'password' => 'required|between:6,20',
			'password_confirmation' => 'required|between:6,20'
		);
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes() && $request["password"] === $request["password_confirmation"]) {
			$user = User::find(\Session::get('uid'));
			$user->password = \Hash::make($request->input('password'));
			$user->save();

			return Redirect::to('user/profile')->with('message', \SiteHelpers::alert('success', 'La contraseña a sido guardada!'));
		} else {
			return Redirect::to('user/profile')->with(
				'message',
				\SiteHelpers::alert('error', \Lang::get('core.note_error'))
			)->withErrors($validator)->withInput();
		}
	}

	public function getReminder()
	{

		return view('user.remind');
	}

	public function postRequest(Request $request)
	{
		\Auth::logout();
		\Session::flush();
		$GLOBALS['to'] = '';
		$GLOBALS['subject'] = '';
		$rules = array(
			'credit_email' => 'required|email'
		);

		$validator = Validator::make(Input::all(), $rules);
		if ($validator->passes()) {

			$user =  User::where('email', '=', $request->input('credit_email'));
			if ($user->count() >= 1) {
				$user = $user->get();
				$user = $user[0];
				$data = array('token' => $request->input('_token'));
				$GLOBALS['to'] = $request->input('credit_email');
				$GLOBALS['subject'] = "[ " .CNF_APPNAME." ] Restaurar contraseña Plataforma Check ";

				if (defined('CNF_MAIL') && CNF_MAIL == 'swift') {
					Mail::send('user.emails.auth.reminder', $data, function ($message) {
			    		$message->from(CNF_EMAIL)->to($GLOBALS['to'])->subject($GLOBALS['subject']);
			    	});

				}  else {


					$message = view('user.emails.auth.reminder', $data);
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
					$headers .= 'From: ' . CNF_APPNAME . ' <' . CNF_EMAIL . '>' . "\r\n";
					mail($to, $subject, $message, $headers);
				}


				$affectedRows = User::where('email', '=', $user->email)
					->update(array('reminder' => $request->input('_token')));

				return Redirect::to('user/login')->with('message', \SiteHelpers::alert('success', 'Por favor revise su correo'));
				\Auth::logout();
				\Session::flush();
			} else {
				\Auth::logout();
				\Session::flush();
				return Redirect::to('user/login?reset')->with('message', \SiteHelpers::alert('error', 'No se puede encontrar su correo'));
			}
			\Auth::logout();
			\Session::flush();
		} else {
			\Auth::logout();
			\Session::flush();
			return Redirect::to('user/login?reset')->with(
				'message',
				\SiteHelpers::alert('error', \Lang::get('core.note_error'))
			)->withErrors($validator)->withInput();
		}
	}

	public function getReset($token = '')
	{
		if (\Auth::check()) return Redirect::to('dashboard');

		$user = User::where('reminder', '=', $token);
		if ($user->count() >= 1) {
			$this->data['verCode'] = $token;
			return view('user.remind', $this->data);
		} else {
			return Redirect::to('user/login')->with('message', \SiteHelpers::alert('error', 'No se puede encontrar su codigo de activación'));
		}
	}

	public function postDoreset(Request $request, $token = '')
	{
		$rules = array(
			'password' => 'required|alpha_num|between:6,20|confirmed',
			'password_confirmation' => 'required|alpha_num|between:6,20'
		);
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {

			$user =  User::where('reminder', '=', $token);
			if ($user->count() >= 1) {
				$data = $user->get();
				$user = User::find($data[0]->id);
				$user->reminder = '';
				$user->password = \Hash::make($request->input('password'));
				$user->save();
			}

			return Redirect::to('user/login')->with('message', \SiteHelpers::alert('success', 'La contraseña a sido guardada!'));
		} else {
			return Redirect::to('user/reset/' . $token)->with(
				'message',
				\SiteHelpers::alert('error', \Lang::get('core.note_error'))
			)->withErrors($validator)->withInput();
		}
	}

	public function getLogout()
	{
		$currentLang = \Session::get('lang');
		\Auth::logout();
		\Session::flush();
		\Session::put('lang', $currentLang);
		return Redirect::to('')->with('message', \SiteHelpers::alert('info', 'Usted no se ha deslogeado!'));
	}

	function getSocialize($social)
	{
		return Socialize::with($social)->redirect();
	}

	function getAutosocial($social)
	{
		$user = Socialize::with($social)->user();
		$user =  User::where('email', $user->email)->first();
		return self::autoSignin($user);
	}


	function autoSignin($user)
	{

		if (is_null($user)) {
			return Redirect::to('user/login')
				->with('message', \SiteHelpers::alert('error', 'Usted aun no se a registrado '))
				->withInput();
		} else {

			Auth::login($user);
			if (Auth::check()) {
				$row = User::find(\Auth::user()->id);

				if ($row->active == '0') {
					// inactive
					Auth::logout();
					return Redirect::to('user/login')->with('message', \SiteHelpers::alert('error', 'Su cuenta no esta activa'));
				} else if ($row->active == '2') {
					// BLocked users
					Auth::logout();
					return Redirect::to('user/login')->with('message', \SiteHelpers::alert('error', 'Su cuenta esta bloqueada'));
				} else {
					Session::put('uid', $row->id);
					Session::put('gid', $row->group_id);
					Session::put('eid', $row->group_email);
					Session::put('fid', $row->first_name . ' ' . $row->last_name);
					if (CNF_FRONT == 'false') :
						return Redirect::to('dashboard');
					else :
						return Redirect::to('');
					endif;
				}
			}
		}
	}
}
