<?php
   include_once("../sys/conexao.php");
   include_once("../sys/funcao.php");
   include_once("../sys/crud.php");
   include_once("../sys/CSRF_Protect.php");
   $csrf = new CSRF_Protect();



if(isset($_REQUEST['slug'])){
  $id_user = decodeAll($_REQUEST['slug']);
  $qry = "SELECT * FROM usuarios WHERE id='".intval($id_user)."'";
  $res = mysqli_query($mysqli,$qry);
  $data = mysqli_fetch_assoc($res);
  
  
  $saldo_user = saldo_user($data['id']);
}

   #======================================#
   #======================================#
   function pegarSaldo(){
        global $data_fiverscanpanel, $ids;
    
        // Obter o saldo do usuário do banco de dados
        $saldoreq = saldo_user($ids);
    
        $url = $data_fiverscanpanel['url']; 
        // Dados para o corpo da requisição em formato JSON
        $data = array(
            'method' => 'GetUserInfo',
            'agentCode' => $data_fiverscanpanel['agent_code'],
            'token' => $data_fiverscanpanel['agent_token'], 
            'userCode' =>  $_SESSION['data_user']['email']
        );
    
        $json_data = json_encode($data);
        $response = enviarRequest($url, $json_data);
        $dados = json_decode($response, true);
    
        if (!empty($dados) && isset($dados['users'])) {
            $user = $dados['users'][0]; // Obter o primeiro usuário (supondo que haja apenas um)
            
            if ($dados['status'] === 0) {
                $saldoapi = floatval($user['balance']);
            } else {
                $novoSaldo = $user['balance'];
                // Atualizar o saldo no banco de dados
                $att_saldo = att_saldo_user($novoSaldo, $ids);
                if ($att_saldo == 1) {
                    $saldoapi = floatval($novoSaldo);
                } else {
                    $saldoapi = floatval($saldoreq['saldo']);
                }
            }
        } else {
            $saldoapi = floatval(saldo_user($ids));
        }
    
        return $saldoapi;
    }

   #======================================#
   //MOSTRA SALDO API
   echo Reais2(pegarSaldo());
   #======================================#
?>