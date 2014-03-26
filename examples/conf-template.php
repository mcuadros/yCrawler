<?php

//Archivo de configuración de las reglas del "yCrawler"
//-- Configurado para cada partner
//
// [-Rule-]
//      Una directiva de este tipo, tiene el formato:
//          $this->createNOMBREDIRECTIVAItem([ String nombre_captura ,] String regla);
//      con estas directivas se obtendrá un array de ocurrencias que cumplan con la 'regla'
//      la directiva devolverá una referencia al propio [-Rule-], con lo que podrán modificarse con "method chaining"
//
//      'String nombre_captura' : [opcional] Nombre bajo el que se guardará el resultado de la aplicación de la directiva
//      'String regla'  : Regla bajo la que se hacen las búsquedas, pueden ser:
//          { xpath | regExp | texto_plano }
//
//      @TIPOS de [-Rule-]
//      El tipo de 'String regla', de una directiva cualquiera -$item-, se indica con:
//          $item->setType('xpath'); [por defecto]
//              El resultado se obtiene parseando el DOM mediante una regla xPath tomada de 'String regla'.
//          $item->setType('regexp');
//              El resultado se obtiene parseando el TEXTO del Documento, mediante una ExpresiónRegular tomada de 'String regla'.
//          $item->setType('literal');
//              El resultado se obtiene directamente del texto de la 'String regla'
//
//      @MODIFICADORES de [-Rule-]
//      Los resultados arrojados por la directiva pueden modificarse, o desencadenar otras acciones:
//          $item->setModifier(String nombre_modificador [, Mixed params ]* );
//      Se pueden aplicar, en cadena, muchos modificadores, que se irán ejecutando por orden de introducción.
//      Cada modificador, se aplica sobre todos los elementos devueltos por la captura de la directiva
//
//      Ejemplos de modificadores:
//          $item->setModifier('boolean' [, true | false ] );
//              Devuelve true, si se capturó algo y el segundo parámetro es true
//              Devuelve false, si no se capturó nada y el segundo parámetro es true
//              Devuelve false, si se capturó algo y el segundo parámetro es false
//              Devuelve true, si no se capturó nada y el segundo parámetro es false
//          $item->setModifier({ 'int' | 'float' });
//              Hace casting a Integer, o a Float
//          $item->setModifier('html');
//              Extrae el contenido HTML
//          $item->setModifier({ 'strtotime' | 'timeplusnow' } );
//              'strtotime' : Convierte la fecha a timestamp
//              'timeplusnow' : Devuelve un timestamp fruto de sumar los segundos pasados, al timestamp actual
//          $item->setModifier('join', String glue);
//              Devuelve un array con un único elemento (String), resultado de unir todos los resultados de la directiva con 'String glue'
//          $item->setModifier('image');
//              Devuelve las rutas de las imágenes capturadas por la directiva, buscando en el 'src', estilo, 'href'...
//          $item->setModifier('callback', Array(&$this, String nombre_callback) );
//              Envía el resultado de la directiva, y el propio Documento a la función 'String nombre_callback'
//
//
//      Ejemplos de estas directivas serían:
//
//          Links que se indexarán
//          @default: no especificado >> se capturarán todos los enlaces
//          --debe de devolver un Array de URIs válidos, no de elementos DOM
//          $this->createLinksItem(String regla_de_links_a_indexar);
//
//          Determinación de si el Documento es indexable (Document:isIndexable),
//          @default: no especificado >> se seguirán todos los enlaces de la página
//          --Si no se encuentra ninguna positiva, se ignorarán todos los enlaces de la página
//          --Si se encuentra alguna negativa, se ignorarán todos los enlaces de la página
//          $this->createLinkFollowItem(String regla_positiva);
//          $this->createLinkFollowItem(String regla_negativa,false);
//
//          Determinación de si se debe parsear el Documento
//          @default: no especificado >> no se parseará el Documento
//          --Si no se encuentra ninguna positiva, no se parseará el Documento
//          --Si se encuentra alguna negativa, no se parseará el Documento
//          $this->createVerifyItem(String regla_positiva);
//          $this->createVerifyItem(String regla_negativa,false);
//
//          Reglas para la captura de los campos del Documento
//          --Obtiene un nuevo Array con los resultados de la directiva, bajo a clave 'String nombre_captura'
//          $this->createValueItem(String nombre_captura, String regla);
//
// [-Group-]
//      Un grupo es un conjunto de [-Rule-], identificados bajo una misma clave (la clave_del_grupo)
//      Una directiva de este tipo, tiene el formato:
//          $grup = $this->createValueGroup(String clave_del_grupo);
//          $grup->createItem(String nombre_subItem_1, String regla_1);
//          $grup->createItem(String nombre_subItem_2, String regla_2);
//          $grup->createItem(String nombre_subItem_3, String regla_3);
//      se obtendrá un (unico) array con todas las ocurrencias de los diferentes [-item-] que lo forman
//      la directiva devolverá una referencia al propio [-Group-], con lo que podrán modificarse con "method chaining"
//
//      'String clave_del_grupo' : [opcional] Clave bajo la que se guardará el resultado de la interpretación del grupo

//      @MODIFICADORES de [-Group-]
//      Un grupo acepta los mismos modificadores que [-Rule-], arriba descritos.
//          $grup->setModifier(String nombre_modificador [, Mixed params ]* );
//      Se pueden aplicar, en cadena, muchos modificadores, que se irán ejecutando por orden de introducción.
//      Cada modificador, se aplica sobre todos los elementos devueltos por el resultados final de la interpretación del [-Group-]
//
//

namespace Config;
use Yunait;

class conf-template extends Yunait\Parser
{
    public function initialize()
    {
        //Dirección de envío de la oferta
        $this->setSubmitURL('http://192.168.1.145:8080/importer/receiver');

        //Dirección de comienzo del indexado
        $this->setStartupURL('http://www.ejemplo.ext');

        //Ruta de donde obtener las "first_url", y expresión regular para obtener las URI
        $this->requestStartupURLs(
                'http://www.ejemplo.ext/script.php',
                Array('param1'=>'val1','param1'=>'val2'),
                '~regExp_to_find_uris~'
        );

        //TODO: [IMPLEMENTAR] Configuración del "login" cuando el partner lo requiere
        //-- default: no especificado
        $this->configLogin('https://www.ejemplo.ext/login.php','params_to_send_to_login');

        //TODO: [IMPLEMENTAR] Elimina todos los parámetros (_GET) de la URI, que no estén marcados para "preservar"
        //-- default: no especificado >> se mantendrán todos los parámetros
        //-- false : se mantendrán todos los parámetros
        //-- true|Array('') : eliminará todos los parámetros
        //-- Array('user','page') : preservará sólo 'user' y 'page'
        $this->createLinksFilter(false|true|Array('param_to_preserve1','param_to_preserve2'));

        //[-Rule-] Links que se indexarán
        //debe de devolver un Array de URIs válidos, no de elementos DOM
        $this->createLinksItem('regla_de_links_a_indexar')->setAttribute('href');

        //[-Rule-] Reglas que determinan si el Documento es indexable (Document:isIndexable),
        //Si no se encuentra ninguna positiva, se ignorarán todos los enlaces de la página
        //Si se encuentra alguna negativa, se ignorarán todos los enlaces de la página
        //-- default: no especificado >> se seguirán los enlaces de la página
        $this->createLinkFollowItem('regla_positiva');
        $this->createLinkFollowItem('regla_negativa',false);

    // Parámetros de lectura de oferta

        //[-Rule-] Reglas que determinan si se debe parsear la oferta buscando
        $this->createVerifyItem('regla_positiva');
        $this->createVerifyItem('regla_negativa',false);

        //[-Rule-] Reglas para la captura de los campos de la oferta
        $this->createValueItem('referencia_empresa', 'regla');   //ID de la oferta en ese partner
        $this->createValueItem('url',                'regla');   //url estática
        $this->createValueItem('titulo',             'regla');   //Título
        $this->createValueItem('descripcion',        'regla');   //Descripción de la oferta
        $this->createValueItem('incluye',            'regla');   //Lo que incluye de la oferta
        $this->createValueItem('condiciones',        'regla');   //Condiciones de la oferta

        $this->createValueItem('imagen',             'regla')->setModifier('image');  //Imágen de la oferta

        $this->createValueItem('nombre_negocio',     'regla');   //Empresa que realiza la oferta
        $this->createValueItem('tag_original',       'regla');   //Tags que aplican a la oferta

        $this->createValueItem('precio_final',       'regla');   //Precio de la oferta
        $this->createValueItem('precio_inicial',     'regla');   //Precio antes de la oferta
        $this->createValueItem('descuento',          'regla');   //Descuento aplicado

        $this->createValueItem('fecha_inicial',      'regla');   //Fecha de finalización
        $this->createValueItem('fecha_final',        'regla');   //Fecha de finalización
        $this->createValueItem('fecha_validez',      'regla');   //Valided del cupón

        $this->createValueItem('municipio',          'regla');   //Municipio al que se limita la oferta, o '', si está definico el 'municipios_global'
        $this->createValueItem('municipios_global',  'unset'|'todas')->setType('literal'); //'unset', si la oferta se limita a un municipio; 'todas', si la oferta es global

        $this->createValueItem('direccion',          'regla');   //Dirección de la oferta
        $this->createValueItem('tlf',                'regla');   //Teléfono del negocio
        $this->createValueItem('lat',                'regla');   //Latitud de la oferta
        $this->createValueItem('lng',                'regla');   //Longitud de la oferta

        $this->createValueItem('personas_apuntadas', 'regla');   //Cuántos están apuntados
        $this->createValueItem('personas_necesarias','regla');   //Cuántos están apuntados
        $this->createValueItem('personas',           'regla');   //Longitud de la oferta
        $this->createValueItem('noches',             'regla');   //Longitud de la oferta
        $this->createValueItem('incluye_vuelo',      'regla');   //Longitud de la oferta
        $this->createValueItem('incluye_envio',      'regla');   //Longitud de la oferta

        //[-Group-] Grupo de reglas 'Rule'
        $grup = $this->createValueGroup('nombre_grupo');
        $grup->createItem('subItem_1', 'regla1');
        $grup->createItem('subItem_2', 'regla2');
        $grup->createItem('subItem_3', 'regla3');

    }

    private function callbackFunction(&$result,$document)
    {
        //en $result se obtiene referencia al Array que captura el [-Rule-]
        //en $document se obtiene el propio [-Document-] en proceso
    }
}
