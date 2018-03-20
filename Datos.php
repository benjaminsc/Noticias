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


      $content = file_get_contents($URL);
      $x = new SimpleXmlElement($content);
      $data = array();
      foreach($x->channel->item as $entry) { // OBTENEMOS TODOS LOS ITEM DE RSS

        //ELIMINAMOS LAS NOTICIAS CATEGORIAS TENDENCIAS Y OPINION, LUEGO LISTAMOS LOS DATOS
      if(strcmp($entry->category,"Tendencias") !== 0 || strcmp($entry->category,"Opinión") !== 0){

          $namespaces = $entry->getNameSpaces(true);
            $nodo = $entry->children($namespaces['df']);
            $title = (string)$entry->title;
            $description = (string)$entry->description;
            $link = (string)$entry->link;
            $media = "Diario Financiero";
            $list =  array('titulo'=>$title,'bajada'=>$description,'link'=>$link,
                           'medio'=>$media,'img'=>"",'fecha'=>$currentDate,
                           'termino'=>"");

            }
            $data[]= $list ;
      }
       echo json_encode($data);
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
      if($dateConvert === $currentDate && strcmp($entry->heading,"Opinión") !== 0 ){
        $title = (string)$entry->title;
        $description = (string)$entry->heading;
        $link = (string)$entry->link;
        $media = "El Mostrador";

            $list =  array('titulo'=>$title,'bajada'=>$description,'link'=>$link,
            'medio'=>$media,'img'=>"",'fecha'=>$currentDate,'termino'=>"");// IMPORTANTE : FALTA OBTENER TERMINO
            $data[]= $list ;
            }

      }
       return json_encode($data);
    }



    function elPulso(){

//DATOS SE EXTRAEN SIN RSS -
      date_default_timezone_set("America/Santiago");
      $currentDate = date('Y-m-d');
      $URL="http://www.pulso.cl/ultima-hora/page/3/";
      $html = file_get_html($URL); //OBTENEMOS EL HTML DE LA PAG
      $posts = $html->find('div[class=article-container]');// OBTENEMOS EL DIV CON LA CLASE QUE IDENTIFICA A CADA NOTICIA

      $data=array();//ARREGLO QUE NOS GUARDARÁ CADA LINK OBTENIDO POSTERIORMENTE
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
            $list= array('titulo'=>$title,'bajada'=>"",'link'=>$url,'medio'=>$media,
            'img'=>"",'fecha'=>$date,'termino'=>"");
            $data[] = $list;


          }
        }else{$date = "";}
      }
        echo json_encode($data); //RETORNAMOS UN ARREGLO DE OBJETOS EN JSON
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
          $title = $post->find('a h3',0)->innertext;
          $date = $post->find('a p span',0)->innertext;
          $media = "Bolsa de Comercio-hechosEsenciales";
          $list = array('titulo'=>$title,'bajada'=>"",'link'=>$url,'medio'=>$media,
          'img'=>"",'fecha'=>$date,'termino'=>"");
          $data[]=$list;
           // echo $date."</br>";
        }else{$url = "";}


      }

       echo json_encode($data);

    }


function getTerms(){

$query = "select idterminos,nombre from terminos where padre is not null ";
$result=  Conexion::get_results($query);
$title = "yo voy a jumbo comprar en ";
$description = "jumdbo te da mas ";
$aux = 0;
if ($aux ===0) {
  foreach ($result as  $value) {
      $coincidencia = stristr($title, $value->nombre);
      if ($coincidencia ==! false) {
          $aux = 1;
          echo "el termino es y vengo del 1:".$value->nombre;
          break;

        }
    }
}if ($aux ===0) {
  foreach ($result as  $value) {
      $coincidencia = stristr($description, $value->nombre);
      if ($coincidencia ==! false) {
          echo "el termino es :".$value->nombre;
          break;

        }
    }
}



  }


getTerms();















?>
