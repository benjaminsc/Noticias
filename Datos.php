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
       return $data;
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
       return $data;
    }



    function elPulso(){

//DATOS SE EXTRAEN SIN RSS
// METODO QUE EXTRAE LOS LINK DE LA PAG "ULTIMA HORA" DE EL PULSO,
// YA QUE NOS TRAE LAS NOTICIAS MAS ACTUALIZADAS PRESENTANDONOS EL LINK, TITULO ETC. PERO NO LA IMAGEN.
// POR LO TANTO DEBEMOS IR A LA PAG DE LA NOTICIA PARA PODER OBTENERLA.
      date_default_timezone_set("America/Santiago");
      $currentDate = date('Y-m-d');
      $URL="http://www.pulso.cl/ultima-hora/";
      $html = file_get_html($URL); //OBTENEMOS EL HTML DE LA PAG
      $posts = $html->find('div[class=article-container]');// OBTENEMOS EL DIV CON LA CLASE QUE IDENTIFICA A CADA NOTICIA

      $linkList=array();//ARREGLO QUE NOS GUARDARÁ CADA LINK OBTENIDO POSTERIORMENTE
      foreach ($posts as $post) { //RECORREMOS LOS POST PERO PRIMERAMENTE FILTRAREMOS LAS NOTICIAS POR FECHA ACTUAL
        $date = $post->find('address span',1);//OBTENEMOS LA FECHA
        if(!(empty($date))){//VALIDAMOS QUE LAS CLASES div[class=article-container] VENGAN FECHAS
            $date = str_replace('/','-',$date->innertext);// CONVERTIMOS FECHA A INNERTEXT
            $date = date('Y-m-d',strtotime($date));//CONVERTIMOS AL FORMATO QUE DEASEAMOS
            if ($date === $currentDate) {// VALIDAMOS QUE LA FECHA DE LA NOTICIA SEA DE HOY
              $link= $post->find('header h1 a',0)->attr['href'];//OBTENEMOS EL LINK
              $linkList[] = $link;//LO ALMACENAMOS EN UN ARREGLO

            }
              // echo $currentDate.'</br>';

        }else{$date='';}


      }
       // print_r($linkList);
       //YA TENEMOS TODOS LOS LINKS QUE NECESITAMOS, AHORA ENTRAMOS A CADA UNO DE ESOS LINKS
       //PARA OBTENER FINALMENTE LA IMG, ADEMAS DE OBTENER TITULO,FECHA ETC
       $data = array();
       foreach ($linkList as $link) {
          $gethtml = file_get_html($link);//CARGAMOS HTML SEGUN LINK QUE LLEGUE
          $post = $gethtml->find('div[class=article-content]');//LA SECCION DE LA PAG DONDE ESTAN LOS DATOS

          foreach ($post as $value) {//RECORREMOS LA SECCION
            $title = $value->find('header h1',0);
            if (!(empty($title))) {//VALIDAMOS QUE EXISTA Y SI ESTA ENTONCES EXTRAEMOS LOS DATOS
              $img = $value->find('div[class=media] span img',0)->attr['src'];
              $list = array('titulo'=>$title->innertext,'bajada'=>'','link'=>$link,'medio'=>'El Pulso',
              'img'=>$img,'fecha'=>$currentDate,'termino'=>'');//FALTA TERMINO- NO TIENEN BAJADA
              $data[] = $list;//GUARDAMOS EN UNA LISTA

            }else{$title = '';}


          }

       }
       return $data;

    }














?>
