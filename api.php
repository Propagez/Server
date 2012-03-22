<?php

class PropagezApi implements Propagez_PushServer_Interface {
	
	//Votre clé secrète 
	public $secret = 'VOTRE_CLE_SECRETE';
	
	//Mode débogage, ne vérifie pas la signature
	public $debug = true;
	
	/**
	 *
	 * Création d'un événement
	 *
	 * Cette méthode reçoit un array événement (voir plus haut) en entré et doit
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
	 * Cette méthode reçoit un identifiant d'événement et un array événement modifié (voir plus haut).
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
	
	
	
}



/**
 *
 * Exécution du serveur
 *
 * Vous devez fournir une instance de votre classe api comme argument
 *
 */
Propagez_PushServer::run(new PropagezApi());


/* --------------------------------------------------------- */
/* --------------------------------------------------------- */
/*
 *
 *
 * Classe d'api
 *
 * Cette classe défini les méthodes appelées par le web service. Elle
 * agit en tant que pont entre les actions faites sur propagez et la
 * base de données du site.
 *
 * Chaque méthode reçoit un ou des paramètres d'entrés provenant de la requête
 * et doit retourner l'information demandée.
 *
 * Si une erreur se produit, la méthode doit retourner une Exception.
 *
 *
 */
interface Propagez_PushServer_Interface {
	
    public function add($data);
    public function update($id,$data);
	public function get($id);
	public function delete($id);
}


/**
 *
 * Classe pour le serveur
 *
 * Cette classe défini la logique de base du web service
 *
 *
 */
class Propagez_PushServer {
	
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
		
		if(!$api instanceof Propagez_PushServer_Interface) {
			throw new Exception('Vous devez utiliser l\'interface');
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
		$idName = self::$config['input_namespace'].'_id';
		$dataName = self::$config['input_namespace'].'_data';
		$signatureName = self::$config['input_namespace'].'_signature';
		
		if(!isset($inputs[$methodName])) throw new Exception('Requête incomplète');
		$method = $inputs[$methodName];
		if(!method_exists(self::$api,$method)) throw new Exception('Requête incomplète');
		
		if(!self::$api->debug) {
			if(!isset($inputs[$signatureName])) throw new Exception('Vous devez fournir une signature');
			if(!self::verifySignature($inputs[$signatureName],$inputs)) throw new Exception('Signature invalide');
		}
		
		switch($method) {
			
			case 'add':
				if(!isset($inputs[$dataName])) throw new Exception('Aucune donnée');
				$data = is_array($inputs[$dataName]) ? $inputs[$dataName]:json_decode($inputs[$dataName],true);
				$response = self::$api->$method($data);
			break;
			
			case 'update':
				if(!isset($inputs[$idName])) throw new Exception('Aucun ID');
				if(!isset($inputs[$dataName])) throw new Exception('Aucune donnée');
				$data = is_array($inputs[$dataName]) ? $inputs[$dataName]:json_decode($inputs[$dataName],true);
				$response = self::$api->$method($inputs[$idName],$data);
			break;
			
			case 'get':
			case 'delete':
				if(!isset($inputs[$idName])) throw new Exception('Aucun ID');
				$response = self::$api->$method($inputs[$idName]);
			break;
			
			default:
				throw new Exception('Méthode inconnue');
			break;
		}
		
		if(!$response) throw new Exception('Aucune réponse');
		
		self::$response = array(
			'success' => true,
			'response' => $response
		);
		
		
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
	
	
	
}

