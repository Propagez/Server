<?php
/*
 *
 *
 * Classe d'api
 *
 * Cette classe est celle que vous devez modifier pour faire le pont entre
 * les données envoyées par propagez et votre base de données. Cette classe
 * doit implémenter l'interface Propagez_Server_Interface.
 *
 * Chaque méthode reçoit un ou des paramètres d'entrés provenant de la requête
 * et doit retourner l'information demandée.
 *
 * Si une erreur se produit, la méthode doit retourner une Exception avec un message
 * expliquant l'erreur.
 *
 *
 */
class PropagezApi implements Propagez_Server_Interface {
	
	//Votre clé secrète 
	public $secret = 'VOTRE_CLE_SECRETE';
	
	//Mode débogage, ne vérifie pas la signature
	public $debug = true;
	
	/**
	 *
	 * Vous pouvez utiliser le constructeur pour intialiser une connexion
	 * à votre base de données.
	 *
	 */
	public function __construct() {
		
	}
	
	/**
	 *
	 * Création d'un événement
	 *
	 * Cette méthode reçoit un array événement (voir README) en entré et doit
	 * retourner le nouvel événement.
	 *
	 * L'événement retourné doit OBLIGATOIREMENT contenir un champ 'id' contenant
	 * l'identifiant unique de l'événement dans la base de donnée du site.
	 *
	 */
	public function add($data) {
		
		//Ajoutez le code permettant de créer un nouvel événement
		//....
		//....
		
		return $response;
	}
	
	/**
	 *
	 * Mise à jour d'un événement
	 *
	 * Cette méthode reçoit un identifiant d'événement et un array événement modifié (voir README).
	 *
	 * Elle doit retourner le nouvel événement mis à jour.  
	 *
	 */
	public function update($id,$data) {
		
		//Ajoutez le code permettant de mettre à jour un événement selon l'identifiant fourni
		//....
		//....
		
		return $response;
	}
	
	/**
	 *
	 * Obtenir un événement
	 *
	 * Cette méthode reçoit un identifiant d'événement en entré et doit
	 * retourner l'événement correspondant.  
	 *
	 */
	public function get($id) {
		
		//Ajoutez le code permettant de retrouver un événement selon l'identifiant fourni
		//....
		//....
		
		return $response;
	}
	
	
	/**
	 *
	 * Méthode  pour supprimer un événement
	 *
	 * Cette méthode reçoit un identifiant d'événement en entré et doit
	 * supprimer l'événement corresponsant.
	 *
	 */
	public function delete($id) {
		
		//Ajoutez le code permettant de supprimer un événement selon l'identifiant fourni
		//....
		//....
		
		return true;
	}
	
	
	/**
	 *
	 * Méthode  pour vérifier un doublon
	 *
	 * Cette méthode sera appelée avant la création d'un nouvel événement.
	 * Elle reçoit un array événement (voir README) et le site doit vérifier
	 * selon ces propres moyens si l'événement est un doublon et retourner true
	 * si c'est le cas. Cette méthode peut être ignorée en retournant false
	 *
	 */
	public function isDuplicate($data) {
		
		//Ajoutez le code permettant de vérifier le doublon
		//....
		//....
		
		return false;
	}
	
	
	
}



/**
 *
 * Exécution du serveur
 *
 * Vous devez fournir une instance de votre classe api comme argument
 *
 */
Propagez_Server::run(new PropagezApi());





/* ---------------------------------------------------------
 *
 *
 * Vous ne devez pas modifier le code ci-dessous
 *
 *
 * ---------------------------------------------------------
 */

/**
 *
 * Interface du web service
 *
 * Votre classe d'api doit implémenter cette interface
 *
 */
interface Propagez_Server_Interface {
	
    public function add($data);
    public function update($id,$data);
	public function get($id);
	public function delete($id);
	public function isDuplicate($data);
	
}


/**
 *
 * Classe pour le serveur
 *
 * Cette classe défini la logique de base du web service
 *
 *
 */
class Propagez_Server {
	
	public static $api;
	public static $response;
	
	protected static $_secret;
	
	public static $config = array(
		'input_namespace' => 'propagez_api'
	);
	
	public static function run($api,$request = null) {
		
		ini_set('display_errors', 0);
		error_reporting(E_NONE);
		
		try {
			
			self::init($api);
			self::handleRequest(isset($request) ? $request:$_REQUEST);
			self::sendResponse();
			
		} catch(Exception $e) {
			self::error($e);
		}
	}
	
	public static function init($api) {
		
		if(!$api instanceof Propagez_Server_Interface) {
			throw new Exception('Vous devez utiliser l\'interface');
		}
		
		if(!isset($api->secret) || empty($api->secret)) {
			throw new Exception('Vous devez fournir une clé secrète');
		}
		
		self::$api = $api;
		self::$_secret = $api->secret;
		
	}
	
	public static function handleRequest($url = null) {
		
		if(is_array($url)) {
			$inputs = $url;
		} else {
			if(strpos($url, '?') !== false) {
				$query = substr($url,strpos($url, '?')+1);
			} elseif(strpos($url, '&') !== false) {
				$query = $url;
			} else {
				throw new Exception('Aucune requête');
			}
			
			$parts = explode('&',$query);
			$inputs = array();
			foreach($parts as $part) {
				$part = explode('=',$part);
				$inputs[$part[0]] = isset($part[1]) ? $part[1]:null;
			}
		}
		
		$methodName = self::$config['input_namespace'].'_method';
		$signatureName = self::$config['input_namespace'].'_signature';
		$idName = self::$config['input_namespace'].'_id';
		$dataName = self::$config['input_namespace'].'_data';
		
		if(!isset($inputs[$methodName])) throw new Exception('Requête incomplète');
		$method = $inputs[$methodName];
		if(!method_exists(self::$api,$method)) throw new Exception('Méthode inconnue');
		
		if(isset(self::$api->debug) && !self::$api->debug) {
			if(!isset($inputs[$signatureName])) throw new Exception('Vous devez fournir une signature');
			if(!self::verifySignature($inputs[$signatureName],$inputs)) throw new Exception('Signature invalide');
		}
		
		
		switch($method) {
			
			case 'add':
				if(!isset($inputs[$dataName])) throw new Exception('Aucune donnée');
				$response = self::$api->$method(self::decodeData($inputs[$dataName]));
			break;
			
			case 'update':
				if(!isset($inputs[$idName])) throw new Exception('Aucun ID');
				if(!isset($inputs[$dataName])) throw new Exception('Aucune donnée');
				$response = self::$api->$method($inputs[$idName], self::decodeData($inputs[$dataName]));
			break;
			
			case 'get':
			case 'delete':
				if(!isset($inputs[$idName])) throw new Exception('Aucun ID');
				$response = self::$api->$method($inputs[$idName]);
			break;
			
			case 'isDuplicate':
				if(!isset($inputs[$dataName])) throw new Exception('Aucune donnée');
				$response = self::$api->$method(self::decodeData($inputs[$dataName]));
			break;
			
			default:
				throw new Exception('Méthode inconnue');
			break;
		}
		
		self::validateResponse($method,$response);
		
		self::$response = array(
			'success' => true,
			'response' => $response
		);
		
		
	}
	
	public static function validateResponse($method,$response) {
		
		if(!isset($response)) throw new Exception('Aucune réponse');
		
		switch($method) {
			case 'add':
			case 'update':
			case 'get':
				if(!is_array($response)) throw new Exception('Vous devez retourner un événement');
				if(!isset($response['id'])) throw new Exception('L\'événement doit contenir un champ id');
			break;
			
			case 'delete':
			case 'isDuplicate':
				if($response !== true && $response !== false) throw new Exception('Vous devez retourner true ou false');
			break;
		}
		
		return true;
	}
	
	public static function verifySignature($signature,$inputs) {
		
		$methodName = self::$config['input_namespace'].'_method';
		$idName = self::$config['input_namespace'].'_id';
		$dataName = self::$config['input_namespace'].'_data';
		
		$parts = array();
		if(isset($inputs[$methodName])) $parts[] = $methodName.'='.rawurlencode($inputs[$methodName]);
		if(isset($inputs[$idName])) $parts[] = $idName.'='.rawurlencode($inputs[$idName]);
		if(isset($inputs[$dataName])) $parts[] = $dataName.'='.rawurlencode($inputs[$dataName]);
		
		if($signature != md5(self::$_secret.'&'.implode('&',$parts))) return false;
		
		return true;
		
	}
	
	public static function sendResponse() {
		
		self::response(self::$response);
		
	}
	
	public static function response($data) {
		
		header('Content-type: text/plain; charset="utf-8"');
		echo json_encode($data);
		exit();
		
	}
	
	public static function error($e) {
		
		self::response(array(
			'success' => false,
			'error' => is_a($e,'Exception') ? $e->getMessage():$e
		));
		
	}
	
	
	
	public static function decodeData($data) {
		return is_array($inputs[$dataName]) ? $inputs[$dataName]:json_decode($inputs[$dataName],true);
	}
	
	
	
}

