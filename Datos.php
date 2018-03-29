<?php
require 'simple_html_dom.php';
require 'Conexion_class.php';

//COMO INTRODUCCION TODOS LOS METODOS SON DISTINTOS, YA QUE, TODOS LOS SITIOS A SCRAPEAR ESTAN DISEÑADOS
//DE FORMA DIFERENTE, POR LO QUE NO SE PUEDE HACER UN UNICO.

    function DF(){ //DIARIO FINANCIERO
//ESTE METODO TIENE RSS, POR LO QUE NOS FACILITA HACER SCRAPING.
// IMPORTANTE: EN ALGUNOS CASOS NO ENTREGA TERMINO O BAJADA YA QUE NO ESTAN EN EL RSS

      date_default_timezone_set("America/Santiago");//establecemos el lugar de hora actual
      $currentDate = date("Y-m-d");//obtenemos la hora
      $mainURL="https://www.df.cl";
      $URL="https://www.df.cl/noticias/site/list/port/rss.xml";//

      $x = simplexml_load_file($URL);//Interpreta un fichero XML en un objeto - le pasamos los elementos xml
      $data = array();//usaremos este array para guardar cada objeto noticia
      foreach($x->channel->item as $entry) { // Recorremos cada "<item>"
        $date = $entry->pubDate;
        $dateConvert=date("Y-m-d", strtotime($date));//En esta linea convertimos la fecha Tue, 27 Mar 2018 17:23:00 GMT a formato 2018-03-27
          if($dateConvert===$currentDate){//necesitamos traer noticias actuales, comparamos la fecha de la noticia con la de hoy.
            $title = (string)$entry->title;//parseamos
            $description = (string)$entry->description;
            $concept = getConcept($title,$description);//el metodo getConcept va en busca de alguna coincidencia con algun termino de bd
            if (!(is_null($concept))) {//si hay coincidencia obtenemos los demas datos y guardamos en un array
              $link = (string)$entry->link;
              $category = getCategory($concept);
              $media = "Diario Financiero";
              // $query = "INSERT INTO historicos(titulo,bajadaNoticia,link,medioOrigen,fecha,categoria,termino) VALUES ('$title','$description','$link','$media','$currentDate','$category','$concept')";
              // Conexion::ex_query($query);
              // $con = new Conexion();
              // $con->ex_query($query);

              $list =  array('titulo'=>$title,'bajada'=>$description,'link'=>$link,//guardamos en una lista el objeto
              'medio'=>$media,'img'=>"",'fecha'=>$currentDate,
              'termino'=>$concept,'categoria'=>$category);
              $data[]= $list ; //almacenamos en esta lista de objetos
            }else{$concept="";}

        }

      }
       echo json_encode($data); //LISTO PARA GUARDAR NOTICIA EN BD
    }

    function elMostrador(){///EL MOSTRADOR -- Sigue el mismo patron que el DF
//ESTE METODO TIENE RSS, POR LO QUE NOS FACILITA HACER SCRAPING.
// IMPORTANTE: EN ALGUNOS CASOS NO ENTREGA TERMINO O BAJADA YA QUE NO ESTAN EN EL RSS

      date_default_timezone_set("America/Santiago");
      $currentDate = date("Y-m-d");
      $mainURL="https://www.elmostrador.cl";
      $URL="http://www.elmostrador.cl/destacado/feed/";
      $x = simplexml_load_file($URL);

      $data = array();
      foreach($x->channel->item as $entry) { // OBTENEMOS TODOS LOS ITEM DE RSS
        $date = $entry->pubDate;
        $dateConvert=date("Y-m-d", strtotime($date));
      if($dateConvert === $currentDate ){
        $title = (string)$entry->title;
        $description = (string)$entry->heading;
        $concept = getConcept($title,$description);
        if (!(is_null($concept))) {
          $link = (string)$entry->link;
          $category = getCategory($concept);
          $media = "El Mostrador";
          // $query = "INSERT INTO historicos(titulo,bajadaNoticia,link,medioOrigen,fecha,categoria,termino) VALUES ('$title','$description','$link','$media','$currentDate','$category','$concept')";
          // Conexion::ex_query($query);
          // $media = "El Mostrador";
          $list =  array('titulo'=>$title,'bajada'=>$description,'link'=>$link,
          'medio'=>$media,'img'=>"",'fecha'=>$currentDate,'termino'=>$concept,'categoria'=>$category);
          $data[]= $list ;
        }else{$concept="";}

        }

      }
       echo json_encode($data);//LISTO PARA GUARDAR EN BD
    }



    function elPulso(){//EL PULSO - SUS NOTICIAS NO TIENEN BAJADA
//ESTE METODO NO TIENE RSS, POR LO QUE DEBEMOS EXTRAER LAS ETIQUETAS
//PARA OBTENER EL CONTENIDO QUE SE REQUIERE. USAREMOS simple_html_dom.
//EL PULSO GUARDA SUS NOTICIAS EN PAGINACIONES POR LO QUE NOS PUEDEN APARECER
//NOTICIAS ACTUALES DESDE LA PAG 1 A LA 4. POR CADA PAGINA EXTRAEREMOS CADA POST QUE ENCUENTRE
//Nota: es importante validar cada objeto que queramos convertir a innertext
//poque de lo contrario puede intentar convertir algo inexistente y darnos un error.
//hicimos 2 if de insercion de noticia, uno con descripcion y el otro sin, por motivo de innertext.

      date_default_timezone_set("America/Santiago");
      $currentDate = date('Y-m-d');
      $URL= array('http://www.pulso.cl/ultima-hora/','http://www.pulso.cl/ultima-hora/page/2/',
      'http://www.pulso.cl/ultima-hora/page/3/', 'http://www.pulso.cl/ultima-hora/page/4/');

      $data=array();//ARREGLO QUE NOS GUARDARÁ CADA LINK OBTENIDO POSTERIORMENTE
      foreach ($URL as  $value) {
        $html = file_get_html($value); //OBTENEMOS EL HTML DE LA PAG
        $posts = $html->find('div[class=article-container]');// OBTENEMOS EL DIV CON LA CLASE QUE IDENTIFICA A CADA NOTICIA
        foreach ($posts as $post) {
          $date = $post->find('address span',1); //EXTRAEMOS LA FECHA
          if (!(empty($date))) {//VALIDAMOS SI LA FECHA EXISTE
            $date = str_replace('/','-',$date->innertext);
            $date = date('Y-m-d',strtotime($date)); //CONVERITMOS LA FECHA AL FORMATO DEASEADO
            if ($date===$currentDate) {
              $description = $post->find('p',0);
              if (!(empty($description))) { //validamos si la noticia tien descripcion para convertirla a plaintext
                $link = $post->find('header h1 a',0);
                $url = $link->attr['href'];
                $title= $link->innertext;
                $description =$description->plaintext;
                $concept = getConcept($title,$description);
                if (!(is_null($concept))) {
                  $category= getCategory($concept);
                  $media = "El pulso";
                  // $query = "INSERT INTO historicos(titulo,bajadaNoticia,link,medioOrigen,fecha,categoria,termino) VALUES ('$title','$description','$url','$media','$currentDate','$category','$concept')";
                  // Conexion::ex_query($query);
                  $list =  array('titulo'=>$title,'bajada'=>$description,'link'=>$url,
                  'medio'=>$media,'img'=>"",'fecha'=>$currentDate,'termino'=>$concept,'categoria'=>$category);
                  $data[]= $list ;
                }
              }elseif(empty($description)){//si la noticia no tiene descripcion no la convertimos a plaintext
                $link = $post->find('header h1 a',0);
                $url = $link->attr['href'];
                $title= $link->innertext;
                $description = "";
                $concept = getConcept($title,$description);
                if (!(is_null($concept))) {
                  $category= getCategory($concept);
                  $media = "El pulso";
                  // $query = "INSERT INTO historicos(titulo,bajadaNoticia,link,medioOrigen,fecha,categoria,termino) VALUES ('$title','$description','$url','$media','$currentDate','$category','$concept')";
                  // Conexion::ex_query($query);
                  $list =  array('titulo'=>$title,'bajada'=>$description,'link'=>$url,
                  'medio'=>$media,'img'=>"",'fecha'=>$currentDate,'termino'=>$concept,'categoria'=>$category);
                  $data[]= $list ;


                }
              }
            }else{$date="";}
          }else{$date="";}
        }
      }
      echo json_encode($data);//LISTO PARA GUARDAR EN BD
  }

    function Santiago(){
      //DATOS SE EXTRAEN SIN RSS -
      date_default_timezone_set("America/Santiago");
      $mainURL="https://www.bolsadesantiago.com";
      $currentDate = date('Y-m-d');
      $url = "http://www.bolsadesantiago.com/labolsa/Paginas/Hechos-Esenciales.aspx?RequestHechosEsenciales=1&hdnPag=1&hdnDia=dia&hdnMes=mes&hdnAno=ano&Nemo=";
      $json = file_get_html($url);
      $obj = json_decode($json,true);
      $posts = $obj['ListHechos'];
      $data = array();
      foreach ($posts as $value) {
        $date = $value['FechaString'];
        $dateConvert = date('Y-m-d',strtotime($date));
        if ($currentDate===$dateConvert) {
          $title = $value['Titulo'];
          $description = "";
          $concept = getConcept($title,$description);
          if (!(is_null($concept))) {
            $category = getCategory($concept);
            $media = 'bolsa de Santiago';
            $link = $value['UrlAdjunto'];
            $url = $mainURL.$link;
            $list =  array('titulo'=>$title,'bajada'=>$description,'link'=>$url,
            'medio'=>$media,'img'=>"",'fecha'=>$currentDate,'termino'=>$concept,'categoria'=>$category);
            $data[]= $list ;
          }
        }
      }
    echo json_encode($data);

    }



// function Emol(){
//
// //   $currentDate = date('Y-m-d');
// //   $URL="http://www.emol.com/economia/";
// //   $html = file_get_html($URL); //OBTENEMOS EL HTML DE LA PAG
// //   $posts = $html->find('div[id=col_center_420px]');// OBTENEMOS EL DIV CON LA CLASE QUE IDENTIFICA A CADA NOTICIA
// // var_dump($html);
// //   foreach ($posts as $value) {
// //     echo "string";
// //   }
// $url = file_get_contents('http://www.emol.com/economia/');
//
// $dom = new DOMDocument();
// @$dom->loadHTML($url);
//
// $xpath = new DOMXPath($dom);
// $hrefs = $xpath->evaluate("/html/body//a");
//
// for ($i = 0; $i < $hrefs->length; $i++) {
// 	$href = $hrefs->item($i);
// 	echo $href->getAttribute('href').'<br />';
// }
// }













//--------------------------- 0 ----------------------------------------------------------------------------
function getConcept($title,$description){
  //IMPORTANTE: TERMINO HIJO PERTENCE A UN TERMINO PADRE, POR LO QUE EL HIJO TIENE UN IDPADRE
//ENCONTRAMOS EL TERMINO DENTRO DE LA NOTICIA, YA SEA EL TERMINO PADRE O HIJO
$query = "select nombre,padre from terminos;";//SELECCIONAMOS TODOS LOS TERMINOS
$results=  Conexion::get_results($query);
$aux = 0;//CREAMOS AUX PARA SABER SI EL TERMINO FUE ENCONTRADO EN TITULO O DESCRIPCION, O SI NO ESTÁ.
$coincidencia="";
if ($aux ===0) {//REVISAMOS EL TITULO
  foreach ($results as  $value) {
      $coincidencia = stristr($title, $value->nombre);//COMPARAMOS EL TITULO DE ENTRADA CON TODOS LOS TERMINOS
      if ($coincidencia ==! false) {//SI COINCIDE AUX SERA 1 Y NO HABRA QUE REVISAR LA DESCRIPCION
          $aux = 1;
          if (is_null($value->padre)) {//SI EL VALOR PADRE DEL TERMINO ES NULO, ENTONCES, EL TERMINO  ES PADRE Y SE GUARDA
            return $value->nombre;
          }else{//SI EL VALOR PADRE NO ES NULL EL TERMINO HIJO DEBE BUSCAR AL TERMINO PADRE

            $query = "select nombre from terminos where idterminos=".$value->padre.";";
            $result = Conexion::get_row($query);
            return $result->nombre;//SE RETORNA EL VALOR PADRE
            break;
          }

        }
    }
}if ($aux ===0 && $description!="") {//REVISAMOS LA DESCRIPCION
  foreach ($results as  $value) {
      $coincidencia = stristr($description, $value->nombre);
      if ($coincidencia ==! false) {//MISMO QUE EL ANTERIOR PERO CON DESCRIPCION
        $aux = 1;
        if (is_null($value->padre)) {
          return $value->nombre;
        }else{
          $query = "select nombre from terminos where idterminos=".$value->padre.";";
          $result = Conexion::get_row($query);
          return $result->nombre;
          break;
        }

      }
    }
  }
  if ($aux===0) {//SI AUX SIGUE EN 0 ENTONCES NO HUBIERON TERMINOS .
    return null;
  }


}

function getCategory($concept){//obtenemos categoria segun termino padre obtenido de getConcept
  $query = "select categoria from terminos where nombre='".$concept."'";
  $result = Conexion::get_row($query);
  return $result->categoria;
}















?>
