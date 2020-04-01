<?php

namespace Hcode\Model;

use ErrorException;
use Exception;
use \Hcode\Model;
use \Hcode\DB\Sql;
use Hcode\Mailer;

class User extends Model
{

	const SESSION = "User";
	const SECRET = "recuperacaoSenha";
	const ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";
	const SUCCESS = "Error";
	protected $fields = [
		"iduser", "idperson", "deslogin", "despassword", 
		"inadmin", "dtergister", "desperson", "desemail", "nrphone", "password",
		'idaddress', 'idcart', 'idstatus', 'idorder',
        'iduser', 'vltotal', 'dtregister', 'desstatus',
        'dtregister', 'dessessionid', 'iduser', 'deszipcode',
        'vlfreight', 'nrdays', 'dtregister', 'iduser',
        'idperson', 'deslogin', 'despassword', 'inadmin',
        'dtregister', 'idperson', 'desaddress', 'descomplement',
        'descity', 'desstate', 'descountry', 'deszipcode',
        'desdistrict', 'dtregister', 'idperson', 'desperson',
        'desemail', 'nrphone', 'dtregister'
	];

	public static function getFromSession()
	{

		$user = new User();
		if (isset($_SESSION[User::SESSION]) && (int) $_SESSION[User::SESSION]['iduser'] > 0) {

			$user->setData($_SESSION[User::SESSION]);
		}
		return $user;
	}

	public static function checkLogin($inadmin = true)
	{
		if (
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int) $_SESSION[User::SESSION]["iduser"] > 0
		) {
			return false;
		} else {

			if ($inadmin === true && (bool) $_SESSION[User::SESSION]["inadmin"] === true) {
				return true;
			} else if ($inadmin === false) {

				return true;
			} else {

				return false;
			}
		}
	}

	public static function login($login, $password): User
	{

		$db = new Sql();

		$results = $db->select(
			"SELECT * 
			FROM tb_users a
			Join tb_persons b on b.idperson = a.idperson
			WHERE a.deslogin = :LOGIN",
			array(
				":LOGIN" => $login
			)
		);
		if (count($results) === 0) {
			throw new \Exception("Não foi possível fazer login.");
		}

		$data = $results[0];


		if (password_verify($password, $data["despassword"]) === true) {

			$user = new User();

			$data['desperson'] = utf8_encode($data['desperson']);

			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;
		} else {

			throw new \Exception("Não foi possível fazer login.");
		}
	}

	public static function logout()
	{

		$_SESSION[User::SESSION] = NULL;
	}

	public static function listAll($search = null)
	{

		$sql = new Sql();

		if($search != null){
			return $sql->select(
				"SELECT * 
				FROM tb_users a
				JOIN tb_persons b on b.idperson = a.idperson
				WHERE b.desperson like :search OR b.desemail like :search or a.deslogin like :search",array(
					':search'=>'%'.$search.'%'
				));
		}

		return $sql->select("SELECT * FROM tb_users JOIN tb_persons on tb_persons.idperson = tb_users.idperson");
	}

	public function createUser()
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save(:desperson,:deslogin,:despassword,:desemail,:nrphone,:inadmin)", array(
			":desperson" => $this->getdesperson(),
			":deslogin" => utf8_decode($this->getdeslogin()),
			":despassword" => User::getPasswordHash($this->getdespassword()),
			":desemail" => $this->getdesemail(),
			":nrphone" => $this->getnrphone(),
			":inadmin" => $this->getinadmin()
		));
		$this->setData($results[0]);
	}

	public  function getUser($idUser)
	{
		$sql = new Sql();
		$result = $sql->select("SELECT * FROM tb_users JOIN tb_persons on tb_persons.idperson = tb_users.idperson WHERE tb_users.idUser = :ID", array(
			":ID" => $idUser
		));

		$result[0]['desperson'] = utf8_encode($result[0]['desperson']);


		$this->setData($result[0]);
	}

	public function updateUser($senha = null)
	{

		if ($senha != null) {
			$this->setdespassword($senha);
		}
		$sql = new Sql();
		$results = $sql->select("CALL sp_usersupdate_save(:iduser,:desperson,:deslogin,:despassword,:desemail,:nrphone,:inadmin)", array(
			":iduser" => $this->getiduser(),
			":desperson" => utf8_decode($this->getdesperson()),
			":deslogin" => $this->getdeslogin(),
			":despassword" => User::getPasswordHash($this->getdespassword()),
			":desemail" => $this->getdesemail(),
			":nrphone" => $this->getnrphone(),
			":inadmin" => $this->getinadmin()
		));
		$this->setData($results[0]);
	}

	public function deleteUser($idUser)
	{
		$sql = new Sql();
		$sql->query("CALL sp_users_delete(:ID)", array(
			":ID" => $idUser
		));
	}

	public function getForgot($email, $inadmin = true)
	{

		$sql = new Sql();

		$result = $sql->select(
			" SELECT * 
		FROM tb_users 
		JOIN tb_persons on tb_persons.idperson = tb_users.idperson 
		WHERE tb_persons.desemail = :EMAIL ",
			array(
				":EMAIL" => $email
			)
		);

		if (count($result) > 0) {

			$rec = $sql->select("CALL sp_userspasswordsrecoveries_create(:IDUSER,:IP)", array(
				":IDUSER" => $result[0]["iduser"],
				":IP" => $_SERVER["REMOTE_ADDR"]
			));

			if (count($rec) > 0) {

				$ivlen = openssl_cipher_iv_length($cipher = "aes-128-cbc");

				//Generate Random IV  
				$iv = openssl_random_pseudo_bytes($ivlen);
				$ciphertext_raw = openssl_encrypt($rec[0]['idrecovery'], $cipher, User::SECRET, $options = OPENSSL_RAW_DATA, $iv);
				$code = base64_encode($iv . $ciphertext_raw);

				if ($inadmin === true) {
					$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";
				} else {
					$link = "http://www.hcodecommerce.com.br/forgot/reset?code=$code";
				}
				$email = new Mailer($result[0]["desemail"], $result[0]["desperson"], "Recuperação de senha", "forgot", array(
					"name" => $result[0]["desperson"],
					"link" => $link
				));

				$email->sendEmail();

				return $result[0];
			} else {
				throw new Exception("Não foi possivel realizar recuperação de senha verifique os dados");
			}
		} else {
			throw new Exception("Não foi possivel realizar recuperação de senha verifique os dados");
		}
	}

	public static function validForgotDecrypt($id)
	{
		$c = base64_decode("$id");
		$ivlen = openssl_cipher_iv_length($cipher = "aes-128-cbc");
		$iv = substr($c, 0, $ivlen);
		$ciphertext_raw = substr($c, $ivlen);
		$original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, User::SECRET, $options = OPENSSL_RAW_DATA, $iv);

		$sql = new Sql();

		$results = $sql->select(
			"SELECT * 
			FROM tb_userspasswordsrecoveries a 
			JOIN tb_users b USING(iduser)
			JOIN tb_persons c USING(idperson)
			WHERE 
				a.idrecovery = :ID
				AND
				a.dtrecovery IS NULL
				AND
				DATE_ADD(a.dtregister,INTERVAL 1 HOUR) >= Now()",
			array(
				":ID" => $original_plaintext
			)
		);
		if (count($results) == 0) {
			throw new Exception("Não foi possivel recuperar a senha. ", 1);
		} else {
			return $results[0];
		}
	}

	public static function setForgotUsed($idrecovery)
	{

		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries set = NOW() WHERE idrecovery = :idrecovery", array(
			":idrecovery" => $idrecovery
		));
	}

	public static function setErrorRegister($msg)
	{
		$_SESSION[User::ERROR_REGISTER] = $msg;
	}

	public static function getErrorRegister()
	{
		$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR_REGISTER] : '';

		User::clearError();

		return $msg;
	}

	public static function clearErrorRegister()
	{
		$_SESSION[User::ERROR_REGISTER] = NULL;
	}

	public static function setError($msg)
	{
		$_SESSION[User::ERROR] = $msg;
	}


	public static function getError()
	{
		$msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';

		User::clearError();

		return $msg;
	}

	public static function clearError()
	{
		$_SESSION[User::ERROR] = NULL;
	}

	public static function checkLoginExist($login)
	{

		$sql = new Sql;

		$results = $sql->select("SELECT * FROM Tb_users WHERE deslogin = :deslogin", array(
			':deslogin' => $login
		));

		return (count($results) > 0);
	}



	public function setPassword($password)
	{
		$sql = new Sql();

		$pass = password_hash($password, PASSWORD_DEFAULT, [
			"cost" => 12
		]);

		$sql->query("UPDATE tb_users SET despassword = :senha WHERE iduser = :iduser", array(
			":senha" => $pass,
			":iduser" => $this->getiduser()
		));
	}

	public static function verifyLogin($inadmin = true)
	{

		if (User::checkLogin($inadmin)) {

			if ($inadmin) {
				header("Location: /admin/login");
			} else if ($inadmin == false) {
				header("Location: /login");
			}
			exit;
		}
	}

	public static function getPasswordHash($password)
	{
		return password_hash($password, PASSWORD_DEFAULT, array(
			'cost' => 12
		));
	}

	public static function setSuccess($msg)
	{
		$_SESSION[User::SUCCESS] = $msg;
	}


	public static function getSuccess()
	{
		$msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : '';

		User::clearSuccess();

		return $msg;
	}

	public static function clearSuccess()
	{
		$_SESSION[User::SUCCESS] = NULL;
	}


	public function getOrders()
	{
		$sql = new Sql();

        $results = $sql->select(
        "SELECT * 
        FROM tb_orders a 
        JOIN tb_ordersstatus b USING(idstatus)
        JOIN tb_carts c USING(idcart)
        JOIN tb_users d ON d.iduser = a.iduser
        JOIN tb_addresses e USING(idaddress)
        JOIN tb_persons f ON f.idperson = d.idperson
        where d.iduser = :iduser",
            array(
                ':iduser' => $this->getiduser()
            )
		);
        return $results;
	}
}
