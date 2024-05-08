<?php
	include_once('conexao.php');
	include_once('funcao.php');
	#-------------------------------------#
	#=====================================================#
	# DATA CONFIG
	function data_api_fiverscan(){
		global $mysqli;
		$qry = "SELECT * FROM fiverscan WHERE id=1";
		$res = mysqli_query($mysqli,$qry);
		$data = mysqli_fetch_assoc($res );
		return $data;
	}
	$data_fiverscan = data_api_fiverscan();
	#=====================================================#
	#=====================================================#
	# DATA AVATAR
	function data_avatar(){
		global $mysqli;
		$qry = "SELECT * FROM admin_users WHERE id=1";
		$res = mysqli_query($mysqli,$qry);
		$data = mysqli_fetch_assoc($res );
		return $data;
	}
	$data_avatar = data_avatar();
	#=====================================================#
	function balance_api(){
        global $data_fiverscan;
        $balance = 0;
    
        $postArray = [
            'method' => 'GetAgentInfo', 
            'agentCode' => $data_fiverscan['agent_code'], 
            'token' => $data_fiverscan['agent_token']
        ];
        $jsonData = json_encode($postArray);
        $headerArray = ['Content-Type: application/json'];
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $data_fiverscan['url']);
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

	#=====================================================#
	# DATA CONFIG
	function qtd_provedor_games($provedor){
		global $mysqli;
		$qry = "SELECT * FROM games WHERE provider='".$provedor."'";
		$res = mysqli_query($mysqli,$qry);
		$data = mysqli_num_rows($res);
		return $data;
	}
	#=====================================================#
	# DATA RPOVEDOR count
	function qtd_provedor_ativos(){
		global $mysqli;
		$qry = "SELECT * FROM provedores WHERE status=1";
		$res = mysqli_query($mysqli,$qry);
		$data = mysqli_num_rows($res);
		return $data;
	}
	#=====================================================#
	# DATA games count
	function qtd_games_ativos(){
		global $mysqli;
		$qry = "SELECT * FROM games WHERE status=1";
		$res = mysqli_query($mysqli,$qry);
		$data = mysqli_num_rows($res);
		return $data;
	}
	#=====================================================#
	# DATA user count
	function qtd_usuarios(){
		global $mysqli;
		$qry = "SELECT * FROM usuarios";
		$res = mysqli_query($mysqli,$qry);
		$data = mysqli_num_rows($res);
		return $data;
	}
	#=====================================================#
	# DATA SALDO CASSINO
	function saldo_cassino(){
		global $mysqli;
		$qry = "SELECT SUM(valor) as total_soma FROM transacoes WHERE tipo='deposito' AND status='pago'";
		$result = mysqli_query($mysqli, $qry);
		while($row = mysqli_fetch_assoc($result)){
			if($row['total_soma'] >0){
				$deposito = $row['total_soma'];
			}else{
			   $deposito = '0.00';
			}
		}
		#-
		$qry_saques = "SELECT SUM(valor) as total_soma FROM solicitacao_saques WHERE status=1";
		$result_saques = mysqli_query($mysqli, $qry_saques);
		while($row_saques = mysqli_fetch_assoc($result_saques)){
			if($row_saques['total_soma'] >0){
				$saques = $row_saques['total_soma'];
			}else{
			   $saques = '0.00';
			}
		}
		$total = $deposito-$saques;
		return $total;
	}
	#=====================================================#
	# DATA deposito pendentes
	function depositos_pendentes(){
		global $mysqli;
		$qry = "SELECT SUM(valor) as total_soma FROM transacoes WHERE tipo='deposito' AND status='processamento'";
		$result = mysqli_query($mysqli, $qry);
		while($row = mysqli_fetch_assoc($result)){
			if($row['total_soma'] >0){
				$deposito = $row['total_soma'];
			}else{
			   $deposito = '0.00';
			}
		}
		return $deposito;
	}
	#=====================================================#
	# DATA deposito diario
	function depositos_diarios(){
		global $mysqli;
		$data = date('Y-m-d');
		$qry = "SELECT SUM(valor) as total_soma FROM transacoes WHERE tipo='deposito' AND status='pago' AND data_hora='".$data."'";
		$result = mysqli_query($mysqli, $qry);
		while($row = mysqli_fetch_assoc($result)){
			if($row['total_soma'] >0){
				$deposito = $row['total_soma'];
			}else{
			   $deposito = '0.00';
			}
		}
		return $deposito;
	}
	#=====================================================#
	# DATA deposito diario
	function depositos_total(){
		global $mysqli;
		$data = date('Y-m-d');
		$qry = "SELECT SUM(valor) as total_soma FROM transacoes WHERE tipo='deposito' AND status='pago'";
		$result = mysqli_query($mysqli, $qry);
		while($row = mysqli_fetch_assoc($result)){
			if($row['total_soma'] >0){
				$deposito = $row['total_soma'];
			}else{
			   $deposito = '0.00';
			}
		}
		return $deposito;
	}
	#=====================================================#
	# DATA saque pendentes 
	function saques_pendentes(){
		global $mysqli;
		$qry = "SELECT SUM(valor) as total_soma FROM solicitacao_saques WHERE status=0";
		$result = mysqli_query($mysqli, $qry);
		while($row = mysqli_fetch_assoc($result)){
			if($row['total_soma'] >0){
				$deposito = $row['total_soma'];
			}else{
			   $deposito = '0.00';
			}
		}
		return $deposito;
	}
	#=====================================================#
	# DATA saque diarios pagos 
	function saques_diarios_pagos(){
		global $mysqli;
		$data = date('Y-m-d');
		$qry = "SELECT SUM(valor) as total_soma FROM solicitacao_saques WHERE data_cad='".$data."' AND status=1";
		$result = mysqli_query($mysqli, $qry);
		while($row = mysqli_fetch_assoc($result)){
			if($row['total_soma'] >0){
				$deposito = $row['total_soma'];
			}else{
			   $deposito = '0.00';
			}
		}
		return $deposito;
	}
	#=====================================================#
	# DATA saque diarios pagos 
	function saques_total(){
		global $mysqli;
		$data = date('Y-m-d');
		$qry = "SELECT SUM(valor) as total_soma FROM solicitacao_saques WHERE data_cad='".$data."' AND status=1";
		$result = mysqli_query($mysqli, $qry);
		while($row = mysqli_fetch_assoc($result)){
			if($row['total_soma'] >0){
				$deposito = $row['total_soma'];
			}else{
			   $deposito = '0.00';
			}
		}
		return $deposito;
	}
	#=====================================================#
	#count saques pendentes
	function count_saques_pendentes(){
		global $mysqli;
		$qry = "SELECT * FROM solicitacao_saques WHERE status=0";
		$res = mysqli_query($mysqli, $qry);
		$count = mysqli_num_rows($res);
		return $count;
	}
	#=====================================================#
	# DATA user count
	function qtd_usuarios_diarios(){
		global $mysqli;
		$data = date('Y-m-d');
		$qry = "SELECT * FROM usuarios WHERE data_cad='".$data."'";
		$res = mysqli_query($mysqli,$qry);
		$data = mysqli_num_rows($res);
		return $data;
	}
	#=====================================================#
	








?>