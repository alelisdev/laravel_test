<?php
	include_once('conexao.php');
	include_once('funcao.php');
	#=====================================================#
	# DATA CONFIG
	function data_config(){
		global $mysqli;
		$qry = "SELECT * FROM config WHERE id=1";
		$res = mysqli_query($mysqli,$qry);
		$data = mysqli_fetch_assoc($res );
		return $data;
	}
	$dataconfig = data_config();
	#=====================================================#
	# DATA CONFIG
	function data_fiverscanPanel(){
		global $mysqli;
		$qry = "SELECT * FROM fiverscan WHERE id=1";
		$res = mysqli_query($mysqli,$qry);
		$data = mysqli_fetch_assoc($res );
		return $data;
	}
	$data_fiverscanpanel = data_fiverscanPanel();
	#=====================================================#
	function balance_apiFiver(){
    global $data_fiverscanpanel;
    $balance = 0;

    $postArray = [
        'method' => 'GetAgentInfo', 
        'agentCode' => $data_fiverscanpanel['agent_code'], 
        'token' => $data_fiverscanpanel['agent_token']
    ];

    $jsonData = json_encode($postArray);
    $headerArray = ['Content-Type: application/json'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $data_fiverscanpanel['url']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $res = curl_exec($ch);

    // Verifique se houve algum erro durante a solicitação
    if (curl_errno($ch)) {
        $status = 'erro';
    } else {
        // Decodifique o JSON retornado
        $responseData = json_decode($res, true);

        // Verifique se a decodificação foi bem-sucedida e se a resposta está correta
        if ($responseData !== null && isset($responseData['status']) && $responseData['status'] === 0 && isset($responseData['msg']) && $responseData['msg'] === 'Success') {
            // Verifique se o saldo está presente na resposta
            if (isset($responseData['balance'])) {
                $balance = $responseData['balance'];
            }
        }
    }

    curl_close($ch);
    return $balance;
}
$saldoapi_fiverscan = balance_apiFiver();

	#=====================================================#
	# DATA CONFIG SUITPAY
	function data_suitpay(){
		global $mysqli;
		$qry = "SELECT * FROM suitpay WHERE id=1";
		$res = mysqli_query($mysqli,$qry);
		$data = mysqli_fetch_assoc($res );
		return $data;
	}
	$data_suitpay = data_suitpay();
	#=====================================================#
	# DATA CONFIG
	function data_afiliados_cpa_rev(){
		global $mysqli;
		$qry = "SELECT * FROM afiliados_config WHERE id=1";
		$res = mysqli_query($mysqli,$qry);
		$data = mysqli_fetch_assoc($res );
		return $data;
	}
	$data_afiliados_cpa_rev = data_afiliados_cpa_rev();
	#=====================================================#
	#criar financeiro
	function criar_financeiro($id){
		global $mysqli;
		$sql1 = $mysqli->prepare("INSERT INTO financeiro (usuario,saldo,bonus) VALUES (?,0,0)");
		$sql1->bind_param("i",$id);
		if($sql1->execute()){
			$tr = 1; //certo
		}else{
			$tr = 0; //erro
		}
		return $tr;
	}
	#=====================================================#
	#criar financeiro
	function criar_tokenrefer($id){
		global $mysqli;
		$aftoken = 'af'.$id.token_aff();
		$sql = $mysqli->prepare("UPDATE usuarios SET token_refer=? WHERE id=?");
		$sql->bind_param("si",$aftoken,$id);
		if($sql->execute()) {
			$tr = 1; //certo
		}else{
			$tr = 0; //erro
			
		}
		return $tr;
	}
	#=====================================================#
	// request curl (fiverscan)
	function enviarRequest($url, $config) {
        $ch = curl_init();
        $headerArray = ['Content-Type: application/json'];
        // Configurando as opções do cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $config);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // Executando a requisição e obtendo a resposta
        $response = curl_exec($ch);
        // Fechando a conexão cURL
        curl_close($ch);
        return $response;
    }
    #=====================================================#
	// saldo atual do user
    function saldo_user($id) {
        global $mysqli;
        $qry = "SELECT * FROM financeiro WHERE usuario='".intval($id)."'";
		$res = mysqli_query($mysqli,$qry);
		if(mysqli_num_rows($res)>0){
			$data = mysqli_fetch_assoc($res);
			$saldo_arr = array(
				"saldo" => $data['saldo'],
				"saldo_afiliado" => $data['saldo_afiliados']
			);
		}else{
			$saldo_arr - array(
				"saldo" => 0,
				"saldo_afiliado" => 0
			);
		}
        return $saldo_arr;
    }
	#=====================================================#
    // atualiza saldo do user
   function att_saldo_user($saldo,$id) {
        global $mysqli;
        $id_user = intval($id);
        $sql = $mysqli->prepare("UPDATE financeiro SET saldo=? WHERE id=?");
        $sql->bind_param("di",$saldo,$id_user);
        if($sql->execute()) {
            $rt = 1;
        }else{
            $rt = 0;
            
        }
        return $rt;
    }
	#=====================================================#
	// financeiro user atual do user
    function financeiro_saldo_user($id) {
        global $mysqli;
        $qry = "SELECT * FROM financeiro WHERE usuario='".intval($id)."'";
		$res = mysqli_query($mysqli,$qry);
		if(mysqli_num_rows($res)>0){
			$saldo = mysqli_fetch_assoc($res);
		}else{
			$saldo=0;
		}
        return $saldo;
    }
	#=====================================================#
	//  se exisitr refer 1
    function pegar_refer($refer) {
        global $mysqli;
        $qry = "SELECT * FROM usuarios WHERE token_refer='".$refer."'";
		$res = mysqli_query($mysqli,$qry);
		if(mysqli_num_rows($res)>0){
			$ex_refer=1;
		}else{
			$ex_refer=0;
		}
        return $ex_refer;
    }
	#=====================================================#
    function criarUsuarioAPI($email){
        global $data_fiverscanpanel;
        $SF = 0;
    
        $postArray = [
            'method' => 'CreateUser', 
            'agentCode' => $data_fiverscanpanel['agent_code'], 
            'token' => $data_fiverscanpanel['agent_token'], 
            'userCode' => $email
        ];
    
        $jsonData = json_encode($postArray);
        $headerArray = ['Content-Type: application/json'];
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $data_fiverscanpanel['url']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $res = curl_exec($ch);
        curl_close($ch);
    
        // Verifique se houve algum erro durante a solicitação
        if (curl_errno($ch)) {
            die('Erro ao fazer a solicitação cURL: ' . curl_error($ch));
        }
    
        // Decodifique o JSON retornado
        $data = json_decode($res, true);
    
        // Verifique se a decodificação foi bem-sucedida
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            die('Erro na decodificação JSON: ' . json_last_error_msg());
        }
    
        // Verifique o status da resposta
        if ($data['status'] === 0 && $data['msg'] === 'Success') {
            $SF = 1;
        }
    
        return $SF;
    }

	#=====================================================#
	//  DELETAR USER
    function deletar_user($id) {
        global $mysqli;
        $sql = $mysqli->prepare("DELETE FROM  usuarios WHERE id=?");
		$sql->bind_param("i",$id);
		$sql->execute();
		
		$sql99 = $mysqli->prepare("DELETE FROM  financeiro WHERE usuario=?");
		$sql99->bind_param("i",$id);
		$sql99->execute();
    }
	#=====================================================#
	function enviarRequest_PAYMENT($url, $header, $data=null) {
        $ch = curl_init();
        $data_json = json_encode($data);

        // Configurando as opções do cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        if(!$data == null){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Executando a requisição e obtendo a resposta
        $response = curl_exec($ch);

        // Fechando a conexão cURL
        curl_close($ch);

        return $response;
    }
	#=====================================================#
    function requestToken_PAYMENT($url, $header, $data) {
        $ch = curl_init();

        // Configurando as opções do cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Executando a requisição e obtendo a resposta
        $response = curl_exec($ch);

        // Fechando a conexão cURL
        curl_close($ch);

        return $response;
    }
	#=====================================================#
	#request pix
	function request_paymentPIX($transactionId){
		global $data_suitpay ,$tipoAPI_SUITPAY;
		if($tipoAPI_SUITPAY == 0){
			$url = 'https://sandbox.ws.suitpay.app/api/v1/gateway/consult-status-transaction';
			$data = array(
				'typeTransaction' => "PIX",
				'idTransaction' => $transactionId
			);
			$header = array(
				'ci: testesandbox_1687443996536',
				'cs: 5b7d6ed3407bc8c7efd45ac9d4c277004145afb96752e1252c2082d3211fe901177e09493c0d4f57b650d2b2fc1b062d',
				'Content-Type: application/json',
			);
		}else{
			$url = $data_suitpay['url'].'/api/v1/gateway/consult-status-transaction';
			$data = array(
				'typeTransaction' => "PIX",
				'idTransaction' => $transactionId
			);
			$header = array(
				'ci: '.$data_suitpay['client_id'],
                'cs: '.$data_suitpay['client_secret'],
				'Content-Type: application/json'
			);
			
		}
		$response = enviarRequest_PAYMENT($url, $header, $data);
        $dados = json_decode($response, true);
		return $dados;
	}
	#=====================================================#
	# coun refer direto
	function count_refer_direto($refer){
		global $mysqli;
        $qry = "SELECT * FROM usuarios WHERE afiliado='".$refer."'";
		$res = mysqli_query($mysqli,$qry);
		$ex_refer= mysqli_num_rows($res);
        return $ex_refer;
	}
	#=====================================================#
	# count saque
	function total_saques_id($id){
		global $mysqli;
		$qry = "SELECT SUM(valor) as total_soma FROM solicitacao_saques WHERE id_user='".$id."'";
		$result = mysqli_query($mysqli, $qry);
		while($row = mysqli_fetch_assoc($result)){
			if($row['total_soma'] >0){
				$dinheiro = $row['total_soma'];
			}else{
			   $dinheiro = '0.00';
			}
		}
        return $dinheiro;
	}
	#=====================================================#
	# count depositos
	function total_dep_id($id){
		global $mysqli;
		$qry = "SELECT SUM(valor) as total_soma FROM transacoes WHERE usuario='".$id."' AND tipo='deposito'";
		$result = mysqli_query($mysqli, $qry);
		while($row = mysqli_fetch_assoc($result)){
			if($row['total_soma'] >0){
				$dinheiro = $row['total_soma'];
			}else{
			   $dinheiro = '0.00';
			}
		}
        return $dinheiro;
	}
	#=====================================================#
	# SUM TOTAL ID CPA/REV
	function total_CPA_REV_id($id){
		global $mysqli;
		$qry = "SELECT SUM(valor) as total_soma FROM pay_valores_cassino WHERE id_user='".$id."' AND tipo=0 OR tipo=1";
		$result = mysqli_query($mysqli, $qry);
		while($row = mysqli_fetch_assoc($result)){
			if($row['total_soma'] >0){
				$dinheiro = $row['total_soma'];
			}else{
			   $dinheiro = '0.00';
			}
		}
        return $dinheiro;
	}
	#=====================================================#
	function pegarLinkJogo($provedor, $game, $email){
        global $data_fiverscanpanel;
    
        $keys = $data_fiverscanpanel;
        $url = $keys['url']; 
    
        // Dados para o corpo da requisição em formato JSON
        $data = array(
            'method' => 'GetGameUrl',
            'agentCode' => $keys['agent_code'],
            'token' => $keys['agent_token'], 
            'userCode' => $email,
            'vendorCode' => $provedor,
            'gameCode' => $game,
            'lang' =>  'pt'
        );
    
        $json_data = json_encode($data);
        $response = enviarRequest($url, $json_data);
        $data = json_decode($response, true);
    
        // Verifica se a decodificação foi bem-sucedida
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            die('Erro na decodificação JSON: ' . json_last_error_msg());
        }
    
        // Verifica se a resposta foi bem-sucedida
        if ($data['status'] === 0 && $data['msg'] === 'Success') {
            $gameURL = $data['launchUrl'];
            $gameName = $game; // Não há campo "gameName" na resposta, então usamos o parâmetro $game
            $games = array('gameURL' => $gameURL, 'gameName' => $gameName);
        } else {
            $games = array('gameURL' => '', 'gameName' => ''); // Retorna valores vazios em caso de falha
        }
    
        return $games;
    }

	#=====================================================#
	# DATA USER ID
	function data_user_id($id){
		global $mysqli;
		$qry = "SELECT * FROM usuarios WHERE id='".$id."'";
		$res = mysqli_query($mysqli,$qry);
		$data = mysqli_fetch_assoc($res);
		return $data;
	}
	#=====================================================#
	function enviarSaldo($email, $saldo){
        global $data_fiverscanpanel;
    
        $keys = $data_fiverscanpanel;
        $url = $keys['url']; 
    
        $num = floatval($saldo);
        $data = array(
            'method' => 'Deposit',
            'agentCode' => $keys['agent_code'],
            'token' => $keys['agent_token'], 
            'userCode' => $email,
            'amount' => $num
        );
    
        $json_data = json_encode($data);
        $response = enviarRequest($url, $json_data);
        $data = json_decode($response, true);
    
        // Verifica se a decodificação foi bem-sucedida
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            die('Erro na decodificação JSON: ' . json_last_error_msg());
        }
    
        // Verifica se a resposta foi bem-sucedida
        if ($data['status'] === 0 && $data['msg'] === 'Success') {
            $prevBalance = $data['prevBalance'];
            $balance = $data['balance'];
            $status = $data['status'];
            $msg = $data['msg'];
        } else {
            $prevBalance = 0;
            $balance = 0;
            $status = $data['status'];
            $msg = $data['msg'];
        }
    
        return array('prevBalance' => $prevBalance, 'balance' => $balance, 'status' => $status, 'msg' => $msg);
    }

	#=====================================================#
	function withdrawSaldo($email, $saldo){
        global $data_fiverscanpanel;
    
        $keys = $data_fiverscanpanel;
        $url = $keys['url'];
        $num = floatval($saldo);
    
        $data = array(
            'method' => 'Withdraw',
            'agentCode' => $keys['agent_code'],
            'token' => $keys['agent_token'], 
            'userCode' => $email,
            'amount' => $num 
        );
    
        $json_data = json_encode($data);
        $response = enviarRequest($url, $json_data);
        $data = json_decode($response, true);
    
        // Verifica se a decodificação foi bem-sucedida
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            die('Erro na decodificação JSON: ' . json_last_error_msg());
        }
    
        // Verifica se a resposta foi bem-sucedida
        if ($data['status'] === 0 && $data['msg'] === 'Success') {
            $prevBalance = $data['prevBalance'];
            $balance = $data['balance'];
            $status = $data['status'];
            $msg = $data['msg'];
        } else {
            $prevBalance = 0;
            $balance = 0;
            $status = $data['status'];
            $msg = $data['msg'];
        }
    
        return array('prevBalance' => $prevBalance, 'balance' => $balance, 'status' => $status, 'msg' => $msg);
    }

    #=====================================================#
	#inserir saldo
	function insert_payment_adm($id,$email,$valor){
       global $mysqli;
	   $tokentrans = '#pixdinamic-'.rand(99,99999);
		$data_hora =  date('Y-m-d H:i:s');
		$sql1 = $mysqli->prepare("INSERT INTO transacoes (transacao_id,usuario,valor,data_hora,tipo,status,code) VALUES (?,?,?,?,'deposito','pago','dinamico')");
		$sql1->bind_param("ssss",$tokentrans,$id,$valor,$data_hora);
		#ENVIA SALDO VIA API
		$retorna_insert_saldo_suit_pay = enviarSaldo($email, $valor);
		if($retorna_insert_saldo_suit_pay['status'] == 1 AND $retorna_insert_saldo_suit_pay['msg'] == "SUCCESS" AND $sql1->execute()){
			$ert = 1;
		}else{
			 $ert = 0;
		 }
       return $ert;
   }
   #=====================================================#
	#contar visitas
	function visitas_count($tipo) {
        global $mysqli;
		$data_hoje = date("Y-m-d");
		if($tipo == 'diario'){
			$qry = "SELECT * FROM visita_site WHERE data_cad='".$data_hoje."'";
			$res = mysqli_query($mysqli,$qry);
			$count = mysqli_num_rows($res);
		}elseif($tipo == 'total'){
			$qry = "SELECT * FROM visita_site";
			$res = mysqli_query($mysqli,$qry);
			$count = mysqli_num_rows($res);
		}else{
			$count =0;
		}
        return $count;
    }
	#=====================================================#
	# busca por token retorn o id
	function busca_id_por_refer($token){
		global $mysqli;
		
		$qry = "SELECT * FROM usuarios WHERE token_refer='".$token."'";
		$res = mysqli_query($mysqli,$qry);
		if(mysqli_num_rows($res)>0){
			$data = mysqli_fetch_assoc($res);
			$count = $data['id'];
		}else{
			$count = 0;
		}
		return $count;
	}
	#=====================================================#
	function generateQRCode_pix($data) {
        // Carregue a biblioteca PHP QR Code
        require_once('../docs_cassino/libraries/phpqrcode/qrlib.php');
        // Caminho onde você deseja salvar o arquivo PNG do QRCode (opcional)
        $file = '../uploads/qrcode.png';
        // Gere o QRCode
        QRcode::png($data, $file);
        // Carregue o arquivo PNG do QRCode
        $qrCodeImage = file_get_contents($file);
        // Converta a imagem para base64
        $base64QRCode = base64_encode($qrCodeImage);
        return $base64QRCode;
    } 
	#=====================================================#
	# busca por ALERT DEP PENDENTES id
	function busca_dep_pendentes($id){
		global $mysqli;
		$qry = "SELECT * FROM transacoes WHERE usuario='".$id."' AND tipo='deposito' AND status='processamento'";
		$res = mysqli_query($mysqli,$qry);
		if(mysqli_num_rows($res)>0){
			$data = 1;
		}else{
			$data = 0;
		}
		return $data;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	








?>