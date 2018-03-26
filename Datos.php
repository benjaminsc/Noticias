<?php
require 'simple_html_dom.php';
require 'Conexion_class.php'; //15-03018 NO ESTA EN USO
//debemos hacer distintas funciones ya que la estructura de cada SITIO es diferente


    function DF(){

      // IMPORTANTE: EN ALGUNOS CASOS NO ENTREGA TERMINO O BAJADA YA QUE NO ESTAN EN EL RSS
      date_default_timezone_set("America/Santiago");
      $currentDate = date("Y-m-d");
      $mainURL="https://www.df.cl";
      $URL="https://www.df.cl/noticias/site/list/port/rss.xml";

      $x = simplexml_load_file($URL);
      $data = array();
      foreach($x->channel->item as $entry) { // OBTENEMOS TODOS LOS ITEM DE RSS
        $date = $entry->pubDate;
        $dateConvert=date("Y-m-d", strtotime($date));
          if($dateConvert==='2018-03-23'){
            $title = (string)$entry->title;
            $description = (string)$entry->description;
            $concept = getConcept($title,$description);
          if (!(is_null($concept))) {
            $link = (string)$entry->link;
            $category = getCategory($concept);
            $media = "Diario Financiero";
            $list =  array('titulo'=>$title,'bajada'=>$description,'link'=>$link,
                           'medio'=>$media,'img'=>"",'fecha'=>$currentDate,
                           'termino'=>$concept,'categoria'=>$category);
            $data[]= $list ;
          }else{$concept="";}

        }

      }
       echo json_encode($data); //LISTO PARA GUARDAR NOTICIA EN BD
    }

    function elMostrador(){

      // IMPORTANTE: EN ALGUNOS CASOS NO ENTREGA TERMINO O BAJADA YA QUE NO ESTAN EN EL RSS
      //AL OBTENER EL VALOR DE CADA ATRIBUTO DEBEN PARSEARSE (SOLO EN RSS)
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
          $list =  array('titulo'=>$title,'bajada'=>$description,'link'=>$link,
          'medio'=>$media,'img'=>"",'fecha'=>$currentDate,'termino'=>$concept,'categoria'=>$category);
          $data[]= $list ;
        }else{$concept="";}

        }

      }
       echo json_encode($data);//LISTO PARA GUARDAR EN BD
    }



    function elPulso(){

//DATOS SE EXTRAEN SIN RSS -
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
            if ($date==='2018-03-24') {
              $description = $post->find('p',0);
              if (!(empty($description))) {
                $link = $post->find('header h1 a',0);
                $url = $link->attr['href'];
                $title= $link->innertext;
                $description =$description->plaintext;
                $concept = getConcept($title,$description);
                if (!(is_null($concept))) {
                  $category= getCategory($concept);
                  $media = "El pulso";
                  $list =  array('titulo'=>$title,'bajada'=>$description,'link'=>$url,
                  'medio'=>$media,'img'=>"",'fecha'=>$currentDate,'termino'=>$concept,'categoria'=>$category);
                  $data[]= $list ;
                }
              }elseif(empty($description)){
                $link = $post->find('header h1 a',0);
                $url = $link->attr['href'];
                $title= $link->innertext;
                $description = "";
                $concept = getConcept($title,$description);
                if (!(is_null($concept))) {
                  $category= getCategory($concept);
                  $media = "El pulso";
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
            $currentDate = date('Y-m-d');
            $URL="http://www.bolsadesantiago.com/noticias/Paginas/Datos-Burs%C3%A1tiles-disponibles-en-la-Tienda-Online-de-la-Bolsa-de-Santiago.aspx";
            $html = file_get_html($URL); //OBTENEMOS EL HTML DE LA PAG
            $posts = $html->find('div[class=fila bloqueNoticia]');// OBTENEMOS EL DIV CON LA CLASE QUE IDENTIFICA A CADA NOTICIA
            $data=array();//ARREGLO QUE NOS GUARDARÁ CADA LINK OBTENIDO POSTERIORMENTE
            foreach ($posts as $post) {
              $link = $post->find('a',0);
              $url = $link->attr['href'];
              $date = $post->find('a p span',0);
              if ($url != "#" && !(empty($url))&&!(empty($date))) {
                $date = $date->innertext;
                $dateConvert = date('Y-m-d',strtotime($date));
                if ($dateConvert===$currentDate) {
                  $title = $post->find('a h3',0)->innertext;
                  $description = "";
                  $concept = getConcept($title,$description);
                  if (!(is_null($concept))) {
                    $media = "Bolsa de Comercio-hechosEsenciales";
                    $category = getCategory($concept);
                    $list = array('titulo'=>$title,'bajada'=>"",'link'=>$url,'medio'=>$media,
                    'img'=>"",'fecha'=>$currentDate,'termino'=>$concept);
                    $data[]=$list;

                  }
                }
              }else{$url = "";}
            }
              echo json_encode($data); //LISTO PARA GUARDAR EN BD
      }















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

function getCategory($concept){
  $query = "select categoria from terminos where nombre='".$concept."'";
  $result = Conexion::get_row($query);
  return $result->categoria;
}
Santiago();













?>
