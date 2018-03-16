

<?php
require 'simple_html_dom.php';
require 'Conexion_class.php'; //15-03018 NO ESTA EN USO
//debemos hacer distintas funciones ya que la estructura de cada SITIO es diferente


    function DF(){

      // IMPORTANTE: EN ALGUNOS CASOS NO ENTREGA TERMINO O BAJADA YA QUE NO ESTAN EN EL RSS

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

      //METODO EN PROCESO - LOS DATOS SE EXTRAEN SIN RSS

      $mainURL="https://www.elpulso.cl";
      $urlContent = file_get_contents($mainURL);

      $dom = new DOMDocument();
      @$dom->loadHTML($urlContent);
      $xpath = new DOMXPath($dom);
      $hrefs = $xpath->evaluate("/html/body//a"); //hacemos un scanner de la pagina para encontrar etiquetas a.
      $data=array();

      for($i = 0; $i < $hrefs->length; $i++){
          $href = $hrefs->item($i);
          $url = $href->getAttribute('href'); // esta varieble trae TODAS las url de la pag.
          if (false !== strpos($url,'/'.$currentDate.'/') && !filter_var($mainURL.$url, FILTER_VALIDATE_URL) === false) {
              $data[$i] = $mainURL.$url;
            }//En esta condicion filtramos las url por fecha actual, ademas, validaremos si la url es valida.

      }
      $resultado = array_unique($data);// si entra alguna url repetida la eliminamos
        print_r($resultado);
    }














?>
