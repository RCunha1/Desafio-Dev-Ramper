<?php
//Variavéis
$linhas_arquivo;
$linhas_codigo; 
$linhas_em_branco;
$linhas_comentadas;
$status_comentario = 0;
$observacao;
$arquivo_analisado = array();

//Função verifica a linha
function verifica_linha($linha){

    //Seta o status para 4           
    $status = 4;
        
    //Percorre e verifica a linha 
    for($i=0;$i<(strlen($linha)-1);$i++){                                      

        //Verifica se tem */ na linha e seta o status
        if(trim($linha[$i]).trim($linha[$i+1]) == '*/'){
            $status = 1;                                

        //Verifica se tem /* na linha e seta o status              
        }else if($status != 3 && trim($linha[$i]).trim($linha[$i+1]) == '/*'){
            $status = 4;          

        //Verifica se tem // na linha e seta o status
        }else if(($status != 4 && $status != 5) && trim($linha[$i]).trim($linha[$i+1]) == '//'){
            $status = 3;                    

        //Verifica se há caracteres após o */ na linha e seta o status
        }else if($status == 1 && trim($linha[$i]).trim($linha[$i+1]) != ' '){   
            $status = 5;                    
        }                                              
    }    

    //Verifica o valor do status e atualiza as variaveis globais      
    if($status > 0 && $status < 4){
        $GLOBALS['observacao'] = 'Linha comentada: ';                   
        $GLOBALS['status_comentario'] = 0;                                  
        $GLOBALS['linhas_comentadas']++;                                

    //Verifica o valor do status e atualiza as variaveis globais
    }else if($status == 4 ){
        $GLOBALS['observacao'] = 'Linha comentada: ';            
        $GLOBALS['status_comentario'] = 1;                              
        $GLOBALS['linhas_comentadas']++;                                

    //Verifica o valor do status e atualiza as variaveis globais
    }else if($status == 5 || $status == 0){
        $GLOBALS['observacao'] = 'Linha código: ';
        $GLOBALS['status_comentario'] = 0;                
        $GLOBALS['linhas_codigo']++;                                               
    }     

    //Retorno da função
    return;                                     
}

//Recebe o arquivo
$nome = $_FILES['arquivo']['name'];

//Recebe a extensão do arquivo
$extensao = strrchr($_FILES['arquivo']['name'], '.');

if($extensao === '.java'){

    //Recebe o conteúdo do arquivo pelo nome temporário
    $linhas = explode("\n", file_get_contents($_FILES['arquivo']['tmp_name']));

    //Loop para analisar o conteúdo do arquivo
    foreach($linhas as $key => $linha){
        
        //Verifica se a linha inicia com /*  
        if(substr(trim($linha),0,2)== "/*" || trim($linha) == '/*'){

            //Função para percorrer e verificar a linha
            verifica_linha($linha);         

        //Verifica a variavel de status caso esteja comentada com /*
        }else if($status_comentario == 1){

            //Verifica se a linha inicia */
            if(substr(trim($linha),0,2) == '*/' || trim($linha) == '*/'){
                $linhas_comentadas++; 
                $status_comentario = 0;    
                $observacao = 'Linha comentada: ';
            
            //Verifica se a linha esta em branco 
            }else if(trim($linha) == ''){
                $linhas_comentadas++;     
                $observacao = 'Linha em branco comentada: ';   
            
            //Verifica se a linha inicia com *
            }else if(trim($linha) == '*' || substr(trim($linha),0,1) == '*'){
                $linhas_comentadas++;   
                $observacao = 'Linha comentada: ';            
            }else{
                
                //Função para percorrer e verificar a linha
                verifica_linha($linha);         
            }    

        //Verifica se a linha esta em branco
        }else if(trim($linha) == ''){
            $linhas_em_branco++;
            $observacao = 'Linha em branco: ';

        //Verifica se a linha esta comentada com //
        }else if(substr(trim($linha),0,2) == "//" || trim($linha) == '//'){
            $linhas_comentadas++;        
            $observacao = 'Linha comentada: ';

        //Caso for falsas as verificações anteriores esta linha é considerada programavel
        }else{
            $observacao = 'Linha código: ';
            $linhas_codigo++;
        }

        $arquivo_analisado[] = array('observacao' =>$observacao,'conteudo' => $linha);
        
        $linhas_arquivo++;
    
    }
}

?>

<!doctype html>
<html lang="pt">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS --> 
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    

    <title>Análise Arquivo</title>
  </head>
  <div class="container">
    <body>
        <h1>Desafio  Dev Ramper!</h1>
	    
     <?php
     if ($extensao === '.java'){
        echo '<br><p>Resultado do arquivo análisado!</p>';
        echo '<table class="table table-striped table-hover">';
        echo '<thead class="thead-dark">';
        echo ' <tr class="row">';
        echo '   <th class="col-xs-1 col-sm-1 col-md-1 col-lg-1">Linha</th>';
        echo '   <th class="col-xs-3 col-sm-3 col-md-3 col-lg-3">Observação linha</th>';
        echo '   <th class="col-xs-8 col-sm-8 col-md-8 col-lg-8">Conteúdo linha</th>';        
        echo ' </tr>';
        echo '     </thead>';
        echo '<tbody>';

        foreach($arquivo_analisado as $key => $arquivo){
            echo '<tr class="row">';
            echo '<th class="col-xs-1 col-sm-1 col-md-1 col-lg-1">'.($key+1).'</th>';
            echo '<th class="col-xs-3 col-sm-3 col-md-3 col-lg-3">'.$arquivo['observacao'].'</th>';
            echo '<th class="col-xs-8 col-sm-8 col-md-8 col-lg-8">'.$arquivo['conteudo'].'</th>';
            echo '</tr>';
        }
    
        // Imprime 
        echo '</br> Orientações ========================================= </br>';
        echo '<p> Consideram-se linhas comentadas as seguintes: ';
        echo '</br> --- Linhas que iniciam com "//" ';
        echo '</br> --- Linhas que iniciam com "/*" ';
        echo '</br> --- Linhas em branco entre "/*" e "*/" ';
        echo '</br> --- Linhas com conteúdo entre "/*" e "*/" ';
        echo '</br> --- Linhas que iniciam com "*/" e não possuem texto até o fim da linha ';
        echo '</br> --- Linhas que contenham em uma parte de seu conteúdo "*/" e não possuem texto até o fim da linha </br>';

        echo '</br> ========================================= </br>';
        echo '</br> Resultado verificação do arquivo: ' .$nome.'</br>';
        echo '</br> --- Total de linhas de código: '.$linhas_codigo;
        echo '</br> --- Total de linhas em branco: '.$linhas_em_branco;
        echo '</br> --- Total de linhas comentadas: '.$linhas_comentadas;
        echo '</br> --- Total de linhas no arquivo: '.$linhas_arquivo.'</br></br>';
        echo 'Total de linhas processadas: '.$linhas_codigo.' + '.$linhas_em_branco.' + '.$linhas_comentadas.' = '
        .($linhas_codigo+$linhas_em_branco+$linhas_comentadas).'</br>';
        
    }else{
        echo '<br><p> Selecione um arquivo do tipo .java </p>';         
    }

    echo '</br><a href="index.html"><button>Voltar e selecionar outro arquivo!</button></a>';
     ?>
      
     </tbody>
    </table>
    </body>
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>  
</html>