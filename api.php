<?php

//Inclure le classe serveur
require_once 'PropagezServer.php';

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