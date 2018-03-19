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
            $list =  array('titulo'=>$entry->title,'bajada'=>$entry->description,'link'=>$entry->link,
                           'medio'=>'Diario Financiero','img'=>$mainURL.$nodo->foto600,'fecha'=>$currentDate,
                           'termino'=>$nodo->tagnames);

            }
            $data[]= $list ;
      }
       return json_encode($data);
    }

    function elMostrador(){

      // IMPORTANTE: EN ALGUNOS CASOS NO ENTREGA TERMINO O BAJADA YA QUE NO ESTAN EN EL RSS
      date_default_timezone_set("America/Santiago");
      $currentDate = date("Y-m-d");
      $mainURL="https://www.elmostrador.cl";
      $URL="http://www.elmostrador.cl/destacado/feed/";


      $content = file_get_contents($URL);
      $x = new SimpleXmlElement($content);

      $data = array();
      foreach($x->channel->item as $entry) { // OBTENEMOS TODOS LOS ITEM DE RSS
        $date = $entry->pubDate;
        $dateConvert=date("Y-m-d", strtotime($date));

        //FILTRAMOS LAS NOTICIAS POR FECHA ACTUAL Y QUE NO SEAN OPINIONES
      if($dateConvert === $currentDate && strcmp($entry->heading,"Opinión") !== 0 ){

            $list =  array('titulo'=>$entry->title,'bajada'=>$entry->heading,'link'=>$entry->link,
                           'medio'=>'El Mostrador','img'=>$entry->image,'fecha'=>$currentDate,
                           'termino'=>"");// IMPORTANTE : FALTA OBTENER TERMINO

            }
            $data[]= $list ;
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
            $list= array('titulo'=>$title,'bajada'=>"",'link'=>$url,
                        'medio'=>"El Pulso",'img'=>"",'fecha'=>$date);
            $data[] = $list;


          }
        }else{$date = "";}
      }
        return json_encode($data); //RETORNAMOS UN ARREGLO DE OBJETOS EN JSON
    }
















?>
