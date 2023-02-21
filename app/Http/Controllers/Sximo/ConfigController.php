<?php namespace App\Http\Controllers\sximo;

use App\Http\Controllers\controller;
use App\Models\Core\Groups;
use App\User;
use Illuminate\Http\Request;
use Validator, Input, Redirect;
use App\Models\TblConfiguracion;

class ConfigController extends Controller {

    public function __construct()
    {
    	parent::__construct();
		if( \Auth::check() or \Session::get('gid') != '1')
		{
		//	echo 'redirect';
			return Redirect::to('dashboard');
		};
        $this->data = array(
            'pageTitle' =>  'General Setting',
            'pageNote'  =>  'Configuration Site',

        );

    }

    static function ObtenerConfiguracion(){
    	$larrConfiguracion = array();
		$lobjConfiguracion = TblConfiguracion::All();
		foreach ($lobjConfiguracion as $Configuracion) {
			$larrConfiguracion[$Configuracion->Nombre] = $Configuracion->Valor;
		}
		return $larrConfiguracion;
    }

	public function getIndex()
	{
        $this->data = array(
            'pageTitle' =>  'General Setting',
            'pageNote'  =>  'Configuration Site',

        );
		$this->data['active'] = '';
		$this->data['row'] = self::ObtenerConfiguracion();
		return view('sximo.config.index',$this->data);
	}

	static function SaveConfig($parrConfiguracion){
		//Guardamos los datos en la base de datos
		foreach ($parrConfiguracion as $lstrNombre => $lstrValor) {
		    $lobjConfiguracion = TblConfiguracion::firstOrNew(array('Nombre' => $lstrNombre));
		    $lobjConfiguracion->Nombre = $lstrNombre;
		    $lobjConfiguracion->Valor = $lstrValor;
			$lobjConfiguracion->save();
		}
	}

	static function postSave( Request $request )
	{

		$rules = array(
			'cnf_appname'=>'required|min:2',
			'cnf_appdesc'=>'required|min:2',
			'cnf_comname'=>'required|min:2',
			'cnf_email'=>'required|email',
		);
		$validator = Validator::make($request->all(), $rules);
		if (!$validator->fails())
		{

			$logo = '';
			if(!is_null(Input::file('logo')))
			{

				$file = Input::file('logo');
			 	$destinationPath = public_path().'/sximo/customer/'.($request->input('cnf_theme_sourcing')?$request->input('cnf_theme_sourcing'):'default').'/';
				$filename = $file->getClientOriginalName();
				$extension =$file->getClientOriginalExtension(); //if you need extension of the file
				$logo = 'logo.'.$extension;
				$uploadSuccess = $file->move($destinationPath, $logo);

				$larrConfiguracion['CNF_LOGO'] = ($logo !=''  ? $logo : CNF_LOGO );

			}

			$logolight = '';
			if(!is_null(Input::file('logolight')))
			{

				$file = Input::file('logolight');
			 	$destinationPath = public_path().'/sximo/customer/'.($request->input('cnf_theme_sourcing')?$request->input('cnf_theme_sourcing'):'default').'/';
				$filename = $file->getClientOriginalName();
				$extension =$file->getClientOriginalExtension(); //if you need extension of the file
				$logolight = 'logolight.'.$extension;
				$uploadSuccess = $file->move($destinationPath, $logolight);

				$larrConfiguracion['CNF_LOGO_LIGHT'] = ($logolight !=''  ? $logolight : CNF_LOGO_LIGHT );

			}

			$background = '';
			if(!is_null(Input::file('background')))
			{

				$file = Input::file('background');
			 	$destinationPath = public_path().'/sximo/customer/'.($request->input('cnf_theme_sourcing')?$request->input('cnf_theme_sourcing'):'default').'/';
				$filename = $file->getClientOriginalName();
				$extension =$file->getClientOriginalExtension(); //if you need extension of the file
				$background = 'background.'.$extension;
				$uploadSuccess = $file->move($destinationPath, $background);

				$larrConfiguracion['CNF_BACKGROUND'] = ($background !=''  ? $background : CNF_BACKGROUND );
			}

			$favicon = '';
			if(!is_null(Input::file('favicon')))
			{

				$file = Input::file('favicon');
			 	$destinationPath = public_path().'/sximo/customer/'.(CNF_THEME_SOURCING?CNF_THEME_SOURCING:'default').'/';
				$filename = $file->getClientOriginalName();
				$extension =$file->getClientOriginalExtension(); //if you need extension of the file
				$favicon = 'favicon.'.$extension;
				$uploadSuccess = $file->move($destinationPath, $favicon);

				$larrConfiguracion['CNF_FAVICON'] = ($favicon !=''  ? $favicon : CNF_FAVICON );
			}

			$larrConfiguracion['CNF_APPNAME'] = $request->input('cnf_appname');
			$larrConfiguracion['CNF_APPDESC'] = $request->input('cnf_appdesc');
			$larrConfiguracion['CNF_COMNAME'] = $request->input('cnf_comname');
			$larrConfiguracion['CNF_EMAIL'] = $request->input('cnf_email');
			$larrConfiguracion['CNF_METAKEY'] = $request->input('cnf_metakey');
			$larrConfiguracion['CNF_METADESC'] = $request->input('cnf_metadesc');
			$larrConfiguracion['CNF_MULTILANG'] = !is_null($request->input('cnf_multilang')) ? 1 : 0 ;
			$larrConfiguracion['CNF_LANG'] = $request->input('cnf_lang');
			$larrConfiguracion['CNF_THEME'] = $request->input('cnf_theme');
			$larrConfiguracion['CNF_THEME_SOURCING'] = $request->input('cnf_theme_sourcing');
			$larrConfiguracion['CNF_MODE'] = (!is_null($request->input('cnf_mode')) ? 'local' : 'production' );
			$larrConfiguracion['CNF_MAIL'] = (defined('CNF_MAIL') ? CNF_MAIL:'phpmail');
			$larrConfiguracion['CNF_DATE'] = (!is_null($request->input('cnf_date')) ? $request->input('cnf_date') : 'Y-m-d' );
      $larrConfiguracion['CNF_EMAILAR'] = !is_null($request->input('cnf_emailar')) ? 1 : 0 ;							

			self::SaveConfig($larrConfiguracion);

			return Redirect::to('sximo/config')->with('messagetext','Setting Has Been Save Successful')->with('msgstatus','success');
		} else {
			return Redirect::to('sximo/config')->with('messagetext', 'The following errors occurred')->with('msgstatus','success')
			->withErrors($validator)->withInput();
		}

	}




	public function getEmail()
	{

		$regEmail = base_path()."/resources/views/user/emails/registration.blade.php";
		$resetEmail = base_path()."/resources/views/user/emails/auth/reminder.blade.php";
		$lodnuevotema = base_path()."/resources/views/user/emails/lod_nuevotema.blade.php";
		$lodnuevocomentario = base_path()."/resources/views/user/emails/lod_nuevocomentario.blade.php";
		$this->data = array(
			'groups'	=> Groups::all(),
			'pageTitle'	=> 'Email Setting',
			'pageNote'	=> 'Email Template',
			'regEmail' 	=> file_get_contents($regEmail),
			'resetEmail'	=> 	file_get_contents($resetEmail),
			'lodnuevotema'	=> 	file_get_contents($lodnuevotema),
			'lodnuevocomentario'	=> 	file_get_contents($lodnuevocomentario),
			'active'		=> 'email',
		);
		return view('sximo.config.email',$this->data);

	}

	function postEmail( Request $request)
	{

		//print_r($_POST);exit;
		$rules = array(
			'regEmail'		=> 'required|min:10',
			'resetEmail'		=> 'required|min:10',
			'lodnuevotema'		=> 'required|min:10',
			'lodnuevocomentario'		=> 'required|min:10'
		);
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes())
		{
			$regEmailFile = base_path()."/resources/views/user/emails/registration.blade.php";
			$resetEmailFile = base_path()."/resources/views/user/emails/auth/reminder.blade.php";
			$lstrLONuevoTema = base_path()."/resources/views/user/emails/lod_nuevotema.blade.php";
			$lstrLONuevoComunicacion = base_path()."/resources/views/user/emails/lod_nuevocomentario.blade.php";

			$fp=fopen($regEmailFile,"w+");
			fwrite($fp,$_POST['regEmail']);
			fclose($fp);

			$fp=fopen($resetEmailFile,"w+");
			fwrite($fp,$_POST['resetEmail']);
			fclose($fp);

			$fp=fopen($lstrLONuevoTema,"w+");
			fwrite($fp,$_POST['lodnuevotema']);
			fclose($fp);

			$fp=fopen($lstrLONuevoComunicacion,"w+");
			fwrite($fp,$_POST['lodnuevocomentario']);
			fclose($fp);

			return Redirect::to('sximo/config/email')->with('messagetext', 'Email Has Been Updated')->with('msgstatus','success');

		}	else {

			return Redirect::to('sximo/config/email')->with('messagetext', 'The following errors occurred')->with('msgstatus','success')
			->withErrors($validator)->withInput();
		}

	}

	public function getSecurity()
	{

		$this->data = array(
			'groups'	=> Groups::all(),
			'pageTitle'	=> 'Login And Security',
			'pageNote'	=> 'Login Configuration and Setting',
			'active'	=> 'security'

		);

		$this->data['row'] = self::ObtenerConfiguracion();
		return view('sximo.config.security',$this->data);

	}




	public function postLogin( Request $request)
	{

		$rules = array(

		);
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {

			$larrConfiguracion['CNF_GROUP'] = $request->input('CNF_GROUP');
			$larrConfiguracion['CNF_ACTIVATION'] = $request->input('CNF_ACTIVATION');
			$larrConfiguracion['CNF_REGIST'] = (!is_null($request->input('CNF_REGIST')) ? 'true':'false');
			$larrConfiguracion['CNF_FRONT'] = (!is_null($request->input('CNF_FRONT')) ? 'true':'false');
			$larrConfiguracion['CNF_RECAPTCHA'] = (!is_null($request->input('CNF_RECAPTCHA')) ? 'true':'false');
			$larrConfiguracion['CNF_ALLOWIP'] = $request->input('CNF_ALLOWIP');
			$larrConfiguracion['CNF_RESTRICIP'] = $request->input('CNF_RESTRICIP');
			$larrConfiguracion['CNF_MAIL'] = (!is_null($request->input('CNF_MAIL')) ? $request->input('CNF_MAIL'):'phpmail');
			$larrConfiguracion['CNF_DATE'] = (defined('CNF_DATE') ? CNF_DATE: 'Y-m-d' );

			self::SaveConfig($larrConfiguracion);

			return Redirect::to('sximo/config/security')->with('messagetext','Setting Has Been Save Successful')->with('msgstatus','success');
		} else {
			return Redirect::to('sximo/config/security')->with('messagetext', 'The following errors occurred')->with('msgstatus','error')
			->withErrors($validator)->withInput();
		}
	}

	public function getLog( $type = null)
	{


		$this->data = array(
			'pageTitle'	=> 'Clear caches',
			'pageNote'	=> 'Remove Current Caches',
			'active'	=> 'log'
		);
		return view('sximo.config.log',$this->data);
	}


	public function getClearlog()
	{

		$dir = base_path()."/storage/logs";
		foreach(glob($dir . '/*') as $file) {
			if(is_dir($file))
			{
				//removedir($file);
			} else {

				unlink($file);
			}
		}

		$dir = base_path()."/storage/framework/views";
		foreach(glob($dir . '/*') as $file) {
			if(is_dir($file))
			{
				//removedir($file);
			} else {

				unlink($file);
			}
		}

		return Redirect::to('sximo/config/log')->with('messagetext','Cache has been cleared !')->with('msgstatus','success');
	}

	function removeDir($dir) {
		foreach(glob($dir . '/*') as $file) {
			if(is_dir($file))
				removedir($file);
			else
				unlink($file);
		}
		rmdir($dir);
	}

	public function getTranslation( Request $request, $type = null)
	{
		if(!is_null($request->input('edit')))
		{
			$file = (!is_null($request->input('file')) ? $request->input('file') : 'core.php');
			$files = scandir(base_path()."/resources/lang/".$request->input('edit')."/");

			//$str = serialize(file_get_contents('./protected/app/lang/'.$request->input('edit').'/core.php'));
			$str = \File::getRequire(base_path()."/resources/lang/".$request->input('edit').'/'.$file);


			$this->data = array(
				'pageTitle'	=> 'Translation',
				'pageNote'	=> 'Add Multilangues Option',
				'stringLang'	=> $str,
				'lang'			=> $request->input('edit'),
				'files'			=> $files ,
				'file'			=> $file ,
			);
			$template = 'edit';

		} else {

			$this->data = array(
				'pageTitle'	=> 'Translation',
				'pageNote'	=> 'Add Multilangues Option',
			);
			$template = 'index';

		}

		return view('sximo.config.translation.'.$template,$this->data);
	}

	public function getAddtranslation()
	{
		return view("sximo.config.translation.create");
	}

	public function postAddtranslation( Request $request)
	{
		$rules = array(
			'name'		=> 'required',
			'folder'	=> 'required|alpha',
			'author'	=> 'required',
		);
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {

			$template = base_path();

			$folder = $request->input('folder');
			mkdir( $template."/resources/lang/".$folder ,0777 );

			$info = json_encode(array("name"=> $request->input('name'),"folder"=> $folder , "author" => $request->input('author')));
			$fp=fopen(  $template.'/resources/lang/'.$folder.'/info.json',"w+");
			fwrite($fp,$info);
			fclose($fp);

			$files = scandir( $template .'/resources/lang/en/');
			foreach($files as $f)
			{
				if($f != "." and $f != ".." and $f != 'info.json')
				{
					copy( $template .'/resources/lang/en/'.$f, $template .'/resources/lang/'.$folder.'/'.$f);
				}
			}
			return Redirect::to('sximo/config/translation')->with('messagetect','New Translation has been added !')->with('msgstatus','success');	;

		} else {
			return Redirect::to('sximo/config/translation')->with('messagetext','Failed to add translation !' )->with('msgstatus','error')->withErrors($validator)->withInput();
		}

	}

	public function postSavetranslation( Request $request)
	{
		$template = base_path();

		$form  	= "<?php \n";
		$form 	.= "return array( \n";
		foreach($_POST as $key => $val)
		{
			if($key !='_token' && $key !='lang' && $key !='file')
			{
				if(!is_array($val))
				{
					$form .= '"'.$key.'"			=> "'.strip_tags($val).'", '." \n ";

				} else {
					$form .= '"'.$key.'"			=> array( '." \n ";
					foreach($val as $k=>$v)
					{
							$form .= '      "'.$k.'"			=> "'.strip_tags($v).'", '." \n ";
					}
					$form .= "), \n";
				}
			}

		}
		$form .= ');';
		//echo $form; exit;
		$lang = $request->input('lang');
		$file	= $request->input('file');
		$filename = $template .'/resources/lang/'.$lang.'/'.$file;
	//	$filename = 'lang.php';
		$fp=fopen($filename,"w+");
		fwrite($fp,$form);
		fclose($fp);
		return Redirect::to('sximo/config/translation?edit='.$lang.'&file='.$file)
		->with('messagetext','Translation has been saved !')->with('msgstatus','success');

	}

	public function getRemovetranslation( $folder )
	{
		self::removeDir( base_path()."/resources/lang/".$folder);
		return Redirect::to('sximo/config/translation')->with('messagetext','Translation has been removed !')->with('msgstatus','success');

	}


}
