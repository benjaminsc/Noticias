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

        //ELIMINAMOS LAS NOTICIAS CATEGORIAS TENDENCIAS Y OPINION, LUEGO LISTAMOS LOS DATOS
      if($dateConvert===$currentDate){

          $namespaces = $entry->getNameSpaces(true);
            $nodo = $entry->children($namespaces['df']);
            $title = (string)$entry->title;
            $description = (string)$entry->description;
            $link = (string)$entry->link;
            $concept = getConcept($title,$description);
            $media = "Diario Financiero";
            $list =  array('titulo'=>$title,'bajada'=>$description,'link'=>$link,
                           'medio'=>$media,'img'=>"",'fecha'=>$currentDate,
                           'termino'=>$concept);
            $data[]= $list ;
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

        //FILTRAMOS LAS NOTICIAS POR FECHA ACTUAL Y QUE NO SEAN OPINIONES
      if($dateConvert === $currentDate ){
        $title = (string)$entry->title;
        $description = (string)$entry->heading;
        $link = (string)$entry->link;
        $concept = getConcept($title,$description);
        $media = "El Mostrador";


            $list =  array('titulo'=>$title,'bajada'=>$description,'link'=>$link,
            'medio'=>$media,'img'=>"",'fecha'=>$currentDate,'termino'=>$concept);
            $data[]= $list ;
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
            if ($date===$currentDate) {//VALIDAMOS QUE LA FECHA DE LA NOTICIA SEA ACTUAL Y EXTRAEMOS LOS DATOS
              $link = $post->find('header h1 a',0);
              $url = $link->attr['href'];
              $title = $link->innertext;
              $media= "El Pulso";
              $description = ""; //ESTA NOTICIA NO TIENE DESCRIPCION PERO DEBEMOS PASARLE LA VARIABLE IGUALMENTE
              $concept = getConcept($title,$description);
              $list= array('titulo'=>$title,'bajada'=>"",'link'=>$url,'medio'=>$media,
              'img'=>"",'fecha'=>$date,'termino'=>$concept);
              $data[] = $list;
            }
          }else{$date = "";}

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
              if ($url != "#" && !(empty($url))) {
                $date = $post->find('a p span',0)->innertext;
                $dateConvert = date('Y-m-d',strtotime($date));
                if ($dateConvert===$currentDate) {
                  $title = $post->find('a h3',0)->innertext;
                  $description = "";
                  $concept = getConcept($title,$description);
                  $media = "Bolsa de Comercio-hechosEsenciales";
                  $list = array('titulo'=>$title,'bajada'=>"",'link'=>$url,'medio'=>$media,
                  'img'=>"",'fecha'=>$currentDate,'termino'=>$concept);
                  $data[]=$list;

                }

              }else{$url = "";}
            }
              echo json_encode($data); //LISTO PARA GUARDAR EN BD
    }

    function Emol(){

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
            if ($date===$currentDate) {//VALIDAMOS QUE LA FECHA DE LA NOTICIA SEA ACTUAL Y EXTRAEMOS LOS DATOS
              $link = $post->find('header h1 a',0);
              $url = $link->attr['href'];
              $title = $link->innertext;
              $media= "El Pulso";
              $description = ""; //ESTA NOTICIA NO TIENE DESCRIPCION PERO DEBEMOS PASARLE LA VARIABLE IGUALMENTE
              $concept = getConcept($title,$description);
              $list= array('titulo'=>$title,'bajada'=>"",'link'=>$url,'medio'=>$media,
              'img'=>"",'fecha'=>$date,'termino'=>$concept);
              $data[] = $list;
            }
          }else{$date = "";}

        }

      }
      echo json_encode($data);//LISTO PARA GUARDAR EN BD

    }




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
            $con = new Conexion();
            $result = $con->get_row($query);
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
          $con = new Conexion();
          $result = $con->get_row($query);
          return $result->nombre;
          break;
        }

      }
    }
  }
  if ($aux===0) {//SI AUX SIGUE EN 0 ENTONCES NO HUBIERON TERMINOS .
    return "Sin terminos";
  }


}

















?>
