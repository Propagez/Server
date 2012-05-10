<?php

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
	
	const VERSION = 0.1;
	
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
			
			case 'version':
				$response = Propagez_Server::VERSION;
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
		return is_array($data) ? $data:json_decode($data,true);
	}
	
	
	
}