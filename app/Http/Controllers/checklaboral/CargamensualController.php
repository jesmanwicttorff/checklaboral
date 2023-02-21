<?php namespace App\Http\Controllers\checklaboral;

use App\Http\Controllers\controller;
use App\Models\checklaboral\Cargamensual;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ; 


class CargamensualController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();	
	public $module = 'cargamensual';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();
		
		$this->model = new Cargamensual();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);
	
		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'cargamensual',
			'return'	=> self::returnUrl()
			
		);
		\App::setLocale(CNF_LANG);
		if (defined('CNF_MULTILANG') && CNF_MULTILANG == '1') {

		$lang = (\Session::get('lang') != "" ? \Session::get('lang') : CNF_LANG);
		\App::setLocale($lang);
		}  
		
		
	}

	public function getIndex( Request $request )
	{

		if($this->access['is_view'] ==0) 
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

		$lobjDatos = \DB::table('tbl_contratistas')
		->select('tbl_contratistas.NombreCarpeta', 'tbl_contratistas.RazonSocial', 'tbl_contrato.cont_numero', 'tbl_contrato.contrato_id')
		->leftjoin('tbl_contrato','tbl_contrato.IdContratista', '=','tbl_contratistas.IdContratista')
		->whereIn('tbl_contrato.contrato_id',session('sesion_contratos'))
		->get();

		if ($lobjDatos){
			$this->data['Directorio'] = self::CargaDirectorio($lobjDatos[0]->contrato_id);
		}else{
			$this->data['Directorio'] = array();
		}

		$this->data['lobjContratos'] = $lobjDatos;
		
		return view('checklaboral.cargamensual.index',$this->data);
	}	



	function getUpdate(Request $request, $id = null)
	{
	
		if($id =='')
		{
			if($this->access['is_add'] ==0 )
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
		}	
		
		if($id !='')
		{
			if($this->access['is_edit'] ==0 )
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
		}				
				
		$this->data['access']		= $this->access;
		return view('checklaboral.cargamensual.form',$this->data);
	}	

	public function getShow( $id = null)
	{
	
		if($this->access['is_detail'] ==0) 
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');
					
		
		$this->data['access']		= $this->access;
		return view('checklaboral.cargamensual.view',$this->data);	
	}	

	function postSave( Request $request)
	{
		
	
	}	

	public function postDelete( Request $request)
	{
		
		if($this->access['is_remove'] ==0) 
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');
		
	}
	public function getCargadirectorio( Request $request ){
		return self::CargaDirectorio($request->input('contrato_id'), $request->input('idtipodocumento'));
	}
	public function Cargadirectorio($pintIdContrato, $lintIdTipoDocumento = "" ){

			$larrData = array(); 
			$anio = date("Y");
			$mes = date("m");
			$lintIdContrato = $pintIdContrato;
			$lintIdTipoDocumento = $lintIdTipoDocumento;
			$lobjDatos = \DB::table('tbl_contratistas') 
		    ->select('tbl_contratistas.Rut', 'tbl_contratistas.NombreCarpeta', 'tbl_contratistas.RazonSocial', 'tbl_contrato.cont_numero', 'tbl_contrato.contrato_id') 
		    ->leftjoin('tbl_contrato','tbl_contrato.IdContratista', '=','tbl_contratistas.IdContratista') 
		    ->where('tbl_contrato.contrato_id', '=', $lintIdContrato) 
		    ->first();
		    if ($lobjDatos){
			    $lintIdContrato = $lobjDatos->cont_numero;
				$lstrRuta = "controllaboral/uploads/".$lobjDatos->NombreCarpeta."/";
				if ($lintIdTipoDocumento){
					$lstrNombre = $anio.$mes."_".$lintIdTipoDocumento."_";
				}else{
					$lstrNombre = $anio.$mes."_";
				}
				$arch = $lstrRuta.$lstrNombre."*.*";
				foreach (glob($arch) as $nombre_fichero) {
					$lstrNombreArchivo = str_replace($lstrRuta,"",$nombre_fichero);
					
					$pstrPeriodo = substr($lstrNombreArchivo,0,strpos($lstrNombreArchivo,"_"));
					$lstrNombreArchivo = substr($lstrNombreArchivo,strpos($lstrNombreArchivo,"_")+1);

					$pstrIdTipoDocumento = substr($lstrNombreArchivo,0,strpos($lstrNombreArchivo,"_"));
					$lstrNombreArchivo = substr($lstrNombreArchivo,strpos($lstrNombreArchivo,"_")+1);
					
					$pstrRut = substr($lstrNombreArchivo,0,strpos($lstrNombreArchivo,"_"));
					$lstrNombreArchivo = substr($lstrNombreArchivo,strpos($lstrNombreArchivo,"_")+1);

					$pstrContrato = substr($lstrNombreArchivo,0,strpos($lstrNombreArchivo,"_"));
					$lstrNombreArchivo = substr($lstrNombreArchivo,strpos($lstrNombreArchivo,"_")+1);

					$pstrContro = substr($lstrNombreArchivo,0,strpos($lstrNombreArchivo,"_"));
					$lstrNombreArchivo = substr($lstrNombreArchivo,strpos($lstrNombreArchivo,"_")+1);

					$pstrNombre = $lstrNombreArchivo;

					if (empty($lintIdContrato)){
						$larrData[$pstrIdTipoDocumento][] = array("nombre"=>$pstrNombre,"url"=>$nombre_fichero);
					}else{
						if ($lintIdContrato==$pstrContrato){
							$larrData[$pstrIdTipoDocumento][] = array("nombre"=>$pstrNombre,"url"=>$nombre_fichero);	
						}
					}
				}
			}
			
			return $larrData;
	}			

   private function codeToMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "El archivo excede el límite definido en la directiva upload_max_filesize";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "El archvo excede el límite definido en la directiva MAX_FILE_SIZE";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "El archivo se subió parcialmente";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "El archivo no se subió al servidor";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Directorio temporal no definido";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "No se puede escribir en el disco";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "La extensión del archivo no soportada";
                break;

            default:
                $message = "Error desconocido";
                break;
        }
        return $message;
    }


	public function postFileuploadparser ( ){

		if (isset($_POST["contrato_id"])){
			$lintIdContratoPost = $_POST["contrato_id"];
		}else{
			$lintIdContratoPost = "";
		}

		$lobjDatos = \DB::table('tbl_contratistas') 
	    ->select('tbl_contratistas.NombreCarpeta', 'tbl_contratistas.RazonSocial', 'tbl_contrato.cont_numero', 'tbl_contrato.contrato_id', 'tbl_contratistas.Rut') 
	    ->leftjoin('tbl_contrato','tbl_contrato.IdContratista', '=','tbl_contratistas.IdContratista') 
	    ->where('tbl_contrato.contrato_id', '=', $lintIdContratoPost) 
	    ->first();

	    if ($lobjDatos){

			$ruta = $lobjDatos->NombreCarpeta;
			$rand = rand(1000,100000000);

			if(isset($lobjDatos->cont_numero)){
				$cont = $lobjDatos->cont_numero; // The file number
			}else{
				$cont = "";
			}

			if(isset($lobjDatos->Rut)){
				$lstrRut = $lobjDatos->Rut; // The file number
			}else{
				$lstrRut = "";
			}

			if(isset($_POST["numArch"])){
				$numArch = $_POST["numArch"]; // The file number
			}else{
				$numArch = "";
			}

			$fileName = $_FILES["file1"]["name"]; // The file name
			$fileTmpLoc = $_FILES["file1"]["tmp_name"]; // File in the PHP tmp folder
			$fileType = $_FILES["file1"]["type"]; // The type of file it is
			$fileSize = $_FILES["file1"]["size"]; // File size in bytes
			// Verify file size - 5MB maximum
			$maxsize = 50 * 1024 * 1024;
			if($fileSize > $maxsize) return ("<center><img src=assets/images/ko.png> Error: El tamaño del archivo supera el límite establecido en la aplicación.</center>");

			$fileErrorMsg = $_FILES["file1"]["error"]; // 0 for false... and 1 for true
			// Use
			$men = "";

			if ($_FILES['file1']['error'] === UPLOAD_ERR_OK) {
				//uploading successfully done
				$men = "OK!";
			} else {
				//$men = UploadException($_FILES['file1']['error']);
				$men = $_FILES['file1']['error'];
$maxUpload      = (int)(ini_get('upload_max_filesize'));
$maxPost        = (int)(ini_get('post_max_size'));
				echo $men."|".$maxUpload."|".$maxPost;
			} 


			if (!$fileTmpLoc) { // if file not chosen
			    echo "<img src='images/ko.png' width='24px'>".$men;
			    exit();
			}
			$anio = date("Y");
			$mes = date("m");
			$final="controllaboral/uploads/$ruta/$anio$mes"."_".$numArch."_".$lstrRut."_".$cont."_".$rand."_".$fileName;
			$final=str_replace(' ', '_', $final);
			if(move_uploaded_file($fileTmpLoc, $final)){
				return "<center><img src='".asset('images/ok.png')."' width='24px'></center>";
			} else {
				if (strlen($men)!="OK!"){
					$men = 'Error: al mover el archivo: '.$final; 
				}
				return "<center><img src='images/ko.png' width='24px'>$men</center>";
			}
			return "<center><img src='".asset('images/ok.png')."' height='24px'></center>";
		}else{
			echo "<pre>Aquí: ";
			echo $lintIdContratoPost;
			echo "</pre>";
			return "<center><img src=assets/images/ko.png> Error: No se puede cargar el archivo.</center>";
		}

	}
}