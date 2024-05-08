
<?php
session_start();
include_once('../../sys/conexao.php');
include_once('../../sys/funcao.php');
include_once('../../sys/crud-adm.php');
include_once('../../sys/checa_login_adm.php');

#expulsa user
checa_login_adm();

# Função para atualizar/inserir jogos de slots no banco de dados
function att_game_slots_providers($gameCode, $gameName, $gameType, $imageUrl, $provedor){
	global $mysqli;
	$stmt = $mysqli->prepare("SELECT * FROM games WHERE game_code = ? AND game_name = ? AND provider = ?");
	if (!$stmt) {
	    die('Erro na preparação da consulta: ' . $mysqli->error);
	}
	$stmt->bind_param("sss", $gameCode, $gameName, $provedor);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0){
		$row = $result->fetch_assoc();
		$id = $row['id'];
		$sql = $mysqli->prepare("UPDATE games SET game_type=?, banner=? WHERE id=?");
		if (!$sql) {
		    die('Erro na preparação da consulta: ' . $mysqli->error);
		}
		// Extrair apenas o link do banner
		$imageUrl = json_decode($imageUrl, true)['en'];
		$sql->bind_param("isi", $gameType, $imageUrl, $id);
		if($sql->execute()) {
			$r_data = 1;
		} else {
			$r_data = 0;
			echo "Erro ao executar a atualização no banco de dados: " . $mysqli->error;
		}
	} else {
		$sql1 = $mysqli->prepare("INSERT INTO games (game_code, game_name, game_type, banner, provider) VALUES (?, ?, ?, ?, ?)");
		if (!$sql1) {
		    die('Erro na preparação da consulta: ' . $mysqli->error);
		}
		// Extrair apenas o link do banner
		$imageUrl = json_decode($imageUrl, true)['en'];
		$sql1->bind_param("ssiss", $gameCode, $gameName, $gameType, $imageUrl, $provedor);
		if($sql1->execute()){
			$r_data = 1;
		} else {
			$r_data = 0;
			echo "Erro ao executar a inserção no banco de dados: " . $mysqli->error;
		}
	}
	
	return $r_data;
}



# Função para obter a lista de jogos de slots
function obterListaJogosSlots($provedor) {
    global $data_fiverscan;
    
    $postArray = [
        "method" => "GetVendorGames",
        "agentCode"=> $data_fiverscan['agent_code'],
        "token"=> $data_fiverscan['agent_token'],
        "vendorCode"=> $provedor
    ];
    
    // Converter os dados para o formato JSON
    $jsonData = json_encode($postArray);
    
    // Configurar o cabeçalho da solicitação
    $headerArray = ['Content-Type: application/json'];
    
    // Iniciar a sessão cURL
    $ch = curl_init();
    
    // Configurar as opções da sessão cURL
    curl_setopt($ch, CURLOPT_URL, $data_fiverscan['url']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Executar a solicitação cURL e obter a resposta
    $response = curl_exec($ch);
    
    // Verificar se ocorreu algum erro durante a solicitação
    if ($response === false) {
        die('Erro ao fazer a solicitação cURL: ' . curl_error($ch));
    }
    
    // Fechar a sessão cURL
    curl_close($ch);
    
    // Decodificar a resposta JSON
    $data = json_decode($response, true);
    
    // Verificar se a decodificação foi bem-sucedida
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        die('Erro na decodificação JSON: ' . json_last_error_msg());
    }
    
    // Verificar o status da resposta
    if ($data['status'] != 0) {
        die('Erro na resposta da API: ' . $data['msg']);
    }
    
    // Retornar os jogos de slots
    return $data['vendorGames'];
}

# Capta dados do formulário (se necessário)
if (isset($_POST['_csrf']) && isset($_POST['code'])) {
    $provider = PHP_SEGURO($_POST['code']);
    
    if (!empty($data_fiverscan['agent_code']) && !empty($data_fiverscan['agent_token'])) {
        // Chamar a função para obter a lista de jogos de slots
        $games = obterListaJogosSlots($provider);
        
        if (!empty($games)) {
            $count = 0;
            $success_count = 0;
            
            // Iterar sobre os jogos e inserir/atualizar no banco de dados
            foreach ($games as $game) {
                $gameCode = $game['gameCode'];
                $gameNameJson = json_decode($game['gameName'], true);
                
                // Extrair o nome do jogo em português se estiver disponível, caso contrário, extrair em inglês
                $gameName = isset($gameNameJson['pt']) ? $gameNameJson['pt'] : $gameNameJson['en'];
                
                $gameType = $game['gameType'];
                $imageUrl = $game['imageUrl'];
                
                $count++;
                
                // Verificar se o imageUrl está vazio
                if (!empty($imageUrl)) {
                    // Chamar a função para inserir/atualizar o jogo no banco de dados
                    $success_count += att_game_slots_providers($gameCode, $gameName, $gameType, $imageUrl, $provider);
                } else {
                    // Caso o imageUrl esteja vazio, não realizar a inserção/atualização
                    $success_count++;
                }
            }



            if ($count == $success_count) {
                echo "<div class='alert alert-success' role='alert'><i class='fa fa-check-circle'></i> Dados atualizados com sucesso.</div><script>  setTimeout('window.location.href=\"".$painel_adm_provedores_games."\";', 3000); </script>";
            } else {
                echo "<div class='alert alert-warning' role='alert'><i class='fa fa-exclamation-circle'></i> Houve um problema ao atualizar os dados dos jogos.</div><script>  setTimeout('window.location.href=\"".$painel_adm_provedores_games."\";', 3000); </script>";
            }
        } else {
            echo "<div class='alert alert-warning' role='alert'><i class='fa fa-exclamation-circle'></i> Não foram encontrados jogos para este provedor.</div><script>  setTimeout('window.location.href=\"".$painel_adm_provedores_games."\";', 3000); </script>";
        }
    }
}
?>