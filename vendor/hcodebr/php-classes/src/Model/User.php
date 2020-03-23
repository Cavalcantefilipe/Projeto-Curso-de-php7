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

	protected $fields = [
		"iduser", "idperson", "deslogin", "despassword", "inadmin", "dtergister", "desperson", "desemail", "nrphone"
	];

	public static function login($login, $password): User
	{

		$db = new Sql();

		$results = $db->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN" => $login
		));

		if (count($results) === 0) {
			throw new \Exception("Não foi possível fazer login.");
		}

		$data = $results[0];

		if (password_verify($password, $data["despassword"])) {

			$user = new User();
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

	public static function listAll()
	{

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users JOIN tb_persons on tb_persons.idperson = tb_users.idperson");
	}

	public function createUser()
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save(:desperson,:deslogin,:despassword,:desemail,:nrphone,:inadmin)", array(
			":desperson" => $this->getdesperson(),
			":deslogin" => $this->getdeslogin(),
			":despassword" => $this->getdespassword(),
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

		$this->setData($result[0]);
	}

	public function updateUser()
	{

		$sql = new Sql();
		$results = $sql->select("CALL sp_usersupdate_save(:iduser,:desperson,:deslogin,:despassword,:desemail,:nrphone,:inadmin)", array(
			":iduser" => $this->getiduser(),
			":desperson" => $this->getdesperson(),
			":deslogin" => $this->getdeslogin(),
			":despassword" => $this->getdespassword(),
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

	public function getForgot($email)
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

				$Cifra =  'AES-256-CBC';

				$IV = random_bytes(openssl_cipher_iv_length($Cifra));

				$TextoCifrado = openssl_encrypt($rec[0]['idrecovery'], $Cifra, user::SECRET, OPENSSL_RAW_DATA, $IV);

				$code = base64_encode($IV . $TextoCifrado);


				$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";

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
		$code = base64_decode($id);
		$Cifra =  'AES-256-CBC';
		$TextoCifrado = mb_substr($code, openssl_cipher_iv_length($Cifra), null, '8bit');

		//$Chave = pack('H*', 'be3494ff4904fd83bf78e3cec0d38ddbf48d0a6a666be05420667a5a7d2c4e0d');
		$IV = mb_substr($code, 0, openssl_cipher_iv_length($Cifra), '8bit');

		$TextoClaro = openssl_decrypt($TextoCifrado, $Cifra, User::SECRET, OPENSSL_RAW_DATA, $IV);

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
				":ID" => $TextoClaro
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

		if (
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int) $_SESSION[User::SESSION]["iduser"] > 0
			||
			(bool) $_SESSION[User::SESSION]["iduser"] !== $inadmin
		) {

			header("Location: /admin/login");
			exit;
		}
	}
}
