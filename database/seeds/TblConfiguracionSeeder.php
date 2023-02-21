<?php

use Illuminate\Database\Seeder;

class TblConfiguracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        $lobjcnfappname = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_APPNAME'));		
		$lobjcnfappname->Nombre = 'CNF_APPNAME';
		$lobjcnfappname->Descripcion = 'Nombre aplicación';
		$lobjcnfappname->Valor = 'Sourcing One';
		$lobjcnfappname->entry_by = 1;
		$lobjcnfappname->save();

		$lobjcnfappdesc = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_APPDESC'));		
		$lobjcnfappdesc->Nombre = 'CNF_APPDESC';
		$lobjcnfappdesc->Descripcion = 'Descripción aplicación';
		$lobjcnfappdesc->Valor = 'Gestión y control de contratistas';
		$lobjcnfappdesc->entry_by = 1;
		$lobjcnfappdesc->save();

		$lobjcnfcomname = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_COMNAME'));		
		$lobjcnfcomname->Nombre = 'CNF_COMNAME';
		$lobjcnfcomname->Descripcion = 'Nombre compañia';
		$lobjcnfcomname->Valor = 'Sourcing';
		$lobjcnfcomname->entry_by = 1;
		$lobjcnfcomname->save();

		$lobjcnfemail = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_EMAIL'));		
		$lobjcnfemail->Nombre = 'CNF_EMAIL';
		$lobjcnfemail->Descripcion = 'Email del sistema';
		$lobjcnfemail->Valor = 'sistema@sourcing.cl';
		$lobjcnfemail->entry_by = 1;
		$lobjcnfemail->save();

		$lobjcnmetakey = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_METAKEY'));		
		$lobjcnmetakey->Nombre = 'CNF_METAKEY';
		$lobjcnmetakey->Descripcion = 'Metakey';
		$lobjcnmetakey->Valor = 'sourcing, soluciones, gestion de contratistas';
		$lobjcnmetakey->entry_by = 1;
		$lobjcnmetakey->save();

		$lobjcnmetadesc = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_METADESC'));		
		$lobjcnmetadesc->Nombre = 'CNF_METADESC';
		$lobjcnmetadesc->Descripcion = 'Metakey';
		$lobjcnmetadesc->Valor = 'Solución para la gestión y control de contratos';
		$lobjcnmetadesc->entry_by = 1;
		$lobjcnmetadesc->save();

		$lobjcnfgroup = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_GROUP'));		
		$lobjcnfgroup->Nombre = 'CNF_GROUP';
		$lobjcnfgroup->Descripcion = '';
		$lobjcnfgroup->Valor = '15';
		$lobjcnfgroup->entry_by = 1;
		$lobjcnfgroup->save();	

		$lobjcnfactivation = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_ACTIVATION'));		
		$lobjcnfactivation->Nombre = 'CNF_ACTIVATION';
		$lobjcnfactivation->Descripcion = '';
		$lobjcnfactivation->Valor = 'auto';
		$lobjcnfactivation->entry_by = 1;
		$lobjcnfactivation->save();	

		$lobjcnfmultilang = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_MULTILANG'));		
		$lobjcnfmultilang->Nombre = 'CNF_MULTILANG';
		$lobjcnfmultilang->Descripcion = 'Multi lenguaje';
		$lobjcnfmultilang->Valor = '1';
		$lobjcnfmultilang->entry_by = 1;
		$lobjcnfmultilang->save();	

		$lobjcnflang = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_LANG'));		
		$lobjcnflang->Nombre = 'CNF_LANG';
		$lobjcnflang->Descripcion = 'Lenguaje principal';
		$lobjcnflang->Valor = 'es';
		$lobjcnflang->entry_by = 1;
		$lobjcnflang->save();	

		$lobjcnfregist = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_REGIST'));		
		$lobjcnfregist->Nombre = 'CNF_REGIST';
		$lobjcnfregist->Descripcion = '';
		$lobjcnfregist->Valor = 'true';
		$lobjcnfregist->entry_by = 1;
		$lobjcnfregist->save();	

		$lobjcnffront = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_FRONT'));		
		$lobjcnffront->Nombre = 'CNF_FRONT';
		$lobjcnffront->Descripcion = '';
		$lobjcnffront->Valor = 'false';
		$lobjcnffront->entry_by = 1;
		$lobjcnffront->save();

		$lobjcnfrecaptcha = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_RECAPTCHA'));		
		$lobjcnfrecaptcha->Nombre = 'CNF_RECAPTCHA';
		$lobjcnfrecaptcha->Descripcion = '';
		$lobjcnfrecaptcha->Valor = 'false';
		$lobjcnfrecaptcha->entry_by = 1;
		$lobjcnfrecaptcha->save();

		$lobjcnftheme = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_THEME'));		
		$lobjcnftheme->Nombre = 'CNF_THEME';
		$lobjcnftheme->Descripcion = 'Estilo visual del sitio web';
		$lobjcnftheme->Valor = 'default';
		$lobjcnftheme->entry_by = 1;
		$lobjcnftheme->save();

		$lobjcnfthemesourcing = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_THEME_SOURCING'));		
		$lobjcnfthemesourcing->Nombre = 'CNF_THEME_SOURCING';
		$lobjcnfthemesourcing->Descripcion = 'Estilo visual de la aplicacion';
		$lobjcnfthemesourcing->Valor = 'default';
		$lobjcnfthemesourcing->entry_by = 1;
		$lobjcnfthemesourcing->save();

		$lobjcnfrecaptchapublickey = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_RECAPTCHAPUBLICKEY'));		
		$lobjcnfrecaptchapublickey->Nombre = 'CNF_RECAPTCHAPUBLICKEY';
		$lobjcnfrecaptchapublickey->Descripcion = '';
		$lobjcnfrecaptchapublickey->Valor = '';
		$lobjcnfrecaptchapublickey->entry_by = 1;
		$lobjcnfrecaptchapublickey->save();

		$lobjcnfrecaptchaprivatekey = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_RECAPTCHAPRIVATEKEY'));		
		$lobjcnfrecaptchaprivatekey->Nombre = 'CNF_RECAPTCHAPRIVATEKEY';
		$lobjcnfrecaptchaprivatekey->Descripcion = '';
		$lobjcnfrecaptchaprivatekey->Valor = '';
		$lobjcnfrecaptchaprivatekey->entry_by = 1;
		$lobjcnfrecaptchaprivatekey->save();

		$lobjcnfmode = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_MODE'));		
		$lobjcnfmode->Nombre = 'CNF_MODE';
		$lobjcnfmode->Descripcion = 'Ambiente de ejecución';
		$lobjcnfmode->Valor = 'production';
		$lobjcnfmode->entry_by = 1;
		$lobjcnfmode->save();

		$lobjcnflogo = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_LOGO'));		
		$lobjcnflogo->Nombre = 'CNF_LOGO';
		$lobjcnflogo->Descripcion = 'Logo de la aplicacion';
		$lobjcnflogo->Valor = 'logo.png';
		$lobjcnflogo->entry_by = 1;
		$lobjcnflogo->save();

		$lobjcnflogolight = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_LOGO_LIGHT'));		
		$lobjcnflogolight->Nombre = 'CNF_LOGO_LIGHT';
		$lobjcnflogolight->Descripcion = 'Logo segundario de la aplicacion';
		$lobjcnflogolight->Valor = 'logolight.png';
		$lobjcnflogolight->entry_by = 1;
		$lobjcnflogolight->save();

		$lobjcnfbackground = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_BACKGROUND'));		
		$lobjcnfbackground->Nombre = 'CNF_BACKGROUND';
		$lobjcnfbackground->Descripcion = 'Imagen de fondo para el login';
		$lobjcnfbackground->Valor = 'background.jpg';
		$lobjcnfbackground->entry_by = 1;
		$lobjcnfbackground->save();

		$lobjcnffavicon = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_FAVICON'));		
		$lobjcnffavicon->Nombre = 'CNF_FAVICON';
		$lobjcnffavicon->Descripcion = 'Favicon de la aplicación';
		$lobjcnffavicon->Valor = 'favicon.ico';
		$lobjcnffavicon->entry_by = 1;
		$lobjcnffavicon->save();

		$lobjcnffavicon = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_ALLOWIP'));		
		$lobjcnffavicon->Nombre = 'CNF_ALLOWIP';
		$lobjcnffavicon->Descripcion = '';
		$lobjcnffavicon->Valor = '';
		$lobjcnffavicon->entry_by = 1;
		$lobjcnffavicon->save();

		$lobjcnffavicon = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_RESTRICIP'));		
		$lobjcnffavicon->Nombre = 'CNF_RESTRICIP';
		$lobjcnffavicon->Descripcion = '';
		$lobjcnffavicon->Valor = '';
		$lobjcnffavicon->entry_by = 1;
		$lobjcnffavicon->save();

		$lobjcnffavicon = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_MAIL'));		
		$lobjcnffavicon->Nombre = 'CNF_MAIL';
		$lobjcnffavicon->Descripcion = '';
		$lobjcnffavicon->Valor = 'phpmail';
		$lobjcnffavicon->entry_by = 1;
		$lobjcnffavicon->save();

		$lobjcnfdate = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_DATE'));		
		$lobjcnfdate->Nombre = 'CNF_DATE';
		$lobjcnfdate->Descripcion = ' Formato de Fecha ';
		$lobjcnfdate->Valor = 'm/d/y';
		$lobjcnfdate->entry_by = 1;
		$lobjcnfdate->save();

		$lobjcnfmodulopersonas = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_MODULO_PERSONAS'));		
		$lobjcnfmodulopersonas->Nombre = 'CNF_MODULO_PERSONAS';
		$lobjcnfmodulopersonas->Descripcion = 'Modulo de personas';
		$lobjcnfmodulopersonas->Valor = 1;
		$lobjcnfmodulopersonas->entry_by = 1;
		$lobjcnfmodulopersonas->save();

		$lobjcnfmodulopartidas = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_MODULO_ACCESOS_PERSONAS'));		
		$lobjcnfmodulopartidas->Nombre = 'CNF_MODULO_ACCESOS_PERSONAS';
		$lobjcnfmodulopartidas->Descripcion = 'Modulo de accesos para personas';
		$lobjcnfmodulopartidas->Valor = 1;
		$lobjcnfmodulopartidas->entry_by = 1;
		$lobjcnfmodulopartidas->save();

		$lobjcnfmodulopartidas = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_MODULO_PARTIDAS'));		
		$lobjcnfmodulopartidas->Nombre = 'CNF_MODULO_PARTIDAS';
		$lobjcnfmodulopartidas->Descripcion = 'Modulo de partidas';
		$lobjcnfmodulopartidas->Valor = 1;
		$lobjcnfmodulopartidas->entry_by = 1;
		$lobjcnfmodulopartidas->save();

		$lobjcnfmodulopartidas = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_MODULO_ACTIVOS'));		
		$lobjcnfmodulopartidas->Nombre = 'CNF_MODULO_ACTIVOS';
		$lobjcnfmodulopartidas->Descripcion = 'Modulo de activos';
		$lobjcnfmodulopartidas->Valor = 1;
		$lobjcnfmodulopartidas->entry_by = 1;
		$lobjcnfmodulopartidas->save();

		$lobjcnfmodulopartidas = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_MODULO_ACCESOS_ACTIVOS'));		
		$lobjcnfmodulopartidas->Nombre = 'CNF_MODULO_ACCESOS_ACTIVOS';
		$lobjcnfmodulopartidas->Descripcion = 'Modulo de accesos para activos';
		$lobjcnfmodulopartidas->Valor = 1;
		$lobjcnfmodulopartidas->entry_by = 1;
		$lobjcnfmodulopartidas->save();

		$lobjcnfmodulopartidas = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_MODULO_FISICO'));		
		$lobjcnfmodulopartidas->Nombre = 'CNF_MODULO_FISICO';
		$lobjcnfmodulopartidas->Descripcion = 'Modulo para el reporte';
		$lobjcnfmodulopartidas->Valor = 1;
		$lobjcnfmodulopartidas->entry_by = 1;
		$lobjcnfmodulopartidas->save();

		$lobjcnfmodulopartidas = App\Models\TblConfiguracion::firstOrNew(array('Nombre' => 'CNF_PAIS'));		
		$lobjcnfmodulopartidas->Nombre = 'CNF_PAIS';
		$lobjcnfmodulopartidas->Descripcion = 'Identificador de Regionalización';
		$lobjcnfmodulopartidas->Valor = 'AR';
		$lobjcnfmodulopartidas->entry_by = 1;
		$lobjcnfmodulopartidas->save();
    }
}
