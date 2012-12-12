<?php
/**
 * Class for Maker management
 *
 * class name is in lowerclass to match table name ("commun" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Maker
 * @category Maker
 */
class maker extends commun {
	private $_sortTypes = array(
		'makerName',
		'makerName DESC',
	);

	// Constructor
	public function __construct() {
		//for "commun" ($this->db & co)
		parent::__construct();
	}

	/**
	 * @return array[][]
	 */
	public function getMakers() {
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getMakers = $this->db->prepare("
					SELECT makerID, makerName
					FROM maker
					ORDER BY ".$this->_sortTypes[0]."
				");

				$getMakers->execute();

				$results = $getMakers->fetchAll();

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * dupplicate the maker table into a myisam temporary table for full text search
	 * @param array $filters
	 * @return array[][]
	 */
	public function getMakersByFullTextSearch(){
		try {
			//sanitize the form data
			$args = array(
				'makerNameFilter'		=> FILTER_SANITIZE_STRING,
				'makerSortType'			=> FILTER_SANITIZE_NUMBER_INT,
			);
			$filters = filter_var_array($_POST, $args);

			$filters['makerSortType'] = filter_var($filters['makerSortType'], FILTER_VALIDATE_INT, array('min_range' => 0, 'max-range' => 3));
			if( $filters['makerSortType'] === false ) $filters['makerSortType'] = 0;

			//construct the query
			$sql = " SELECT *";

			$sqlSelect = array();
			$sqlWhere = array();
			$sqlOrder = 'score DESC, ';
			$params = array();
			if( !empty($filters['makerNameFilter']) ){
				$sqlSelect[] = "MATCH(makerName) AGAINST (:makerNameS)";
				$sqlWhere[] = "MATCH(makerName) AGAINST (:makerNameW)";
				$params[':makerNameS'] = $this->prepareForFullTextQuery($filters['makerNameFilter']);
				$params[':makerNameW'] = $params[':makerNameS'];
			}

			$sql = " SELECT bft.*"
				  .( !empty($sqlSelect) ? ', '.implode(' + ', $sqlSelect).' AS score' : '')
				  ." FROM maker_ft bft"
				  ." WHERE 1 "
				  .( !empty($sqlWhere) ? ' AND '.implode(' AND ', $sqlWhere) : '')
				  ." ORDER BY "
				  .( !empty($sqlSelect) ? $sqlOrder : '')
				  .$this->_sortTypes[$filters['makerSortType']];


			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			if( empty($params) ) $stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $sql);
			else $stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $sql, serialize($params));
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them

				//drop the temporary table if it exists
				$destroyTmpTable = $this->db->prepare("DROP TEMPORARY TABLE IF EXISTS maker_ft");
				$destroyTmpTable->execute();

				//create the temporary table
				$tmpTable = $this->db->prepare("
					CREATE TEMPORARY TABLE maker_ft AS
					SELECT  makerID, makerName
					FROM maker
				");
				$tmpTable->execute();

				//add the fulltext index
				$indexTmpTable = $this->db->prepare("
					ALTER TABLE maker_ft ENGINE = MyISAM,
					ADD FULLTEXT INDEX makerNameFT (makerName)
				");
				$indexTmpTable->execute();


				$getMakers = $this->db->prepare($sql);

				$getMakers->execute( $params );

				$results = $getMakers->fetchAll();

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id : maker id
	 * @return array[][]
	 */
	public function getMakerById( $id ) {
		try {
			$getMakerById = $this->db->prepare("
				SELECT makerID, makerName
				FROM maker
				WHERE makerID = :id
			");

			$getMakerById->execute( array( ':id' => $id ) );

			return $getMakerById->fetchAll();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param boolean $returnTs : flag for the function to return the list and the ts or only the list
	 * @param boolean $tsOnly : flag for the function to return the cache creation date timestamp only
	 * @return array[][]
	 */
	public function getMakersForDropDownList( $returnTs = false, $tsOnly = false ){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);

			if( $tsOnly ){
				$ts = $stash->getTimestamp();
				if( $stash->isMiss() ){
					return null;
				} else {
					return $ts;
				}
			}

			$results = $stash->get();
			$ts = null;
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getMakers = $this->db->prepare("
					SELECT makerName AS value
					FROM maker
					ORDER BY value
				");

				$getMakers->execute();

				$results = $getMakers->fetchAll();

				if( !empty($results) ){
					$stash->store($results, STASH_EXPIRE);
					$ts = $stash->getTimestamp();
				}
			}

			if( $returnTs ){
				return array($ts, $results);
			} else {
				return $results;
			}

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param boolean $returnTs : flag for the function to return the list and the ts or only the list
	 * @param boolean $tsOnly : flag for the function to return the cache creation date timestamp only
	 * @return array[]
	 */
	public function getMakersForFilterList( $returnTs = false, $tsOnly = false ){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);

			if( $tsOnly ){
				$ts = $stash->getTimestamp();
				if( $stash->isMiss() ){
					return null;
				} else {
					return $ts;
				}
			}

			$results = $stash->get();
			$ts = null;
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getMakersForFilterList = $this->db->prepare("
					SELECT makerName as value
					FROM alcohol_makers_view
					GROUP BY makerID
					ORDER BY makerName
				");

				$getMakersForFilterList->execute();

				$results = $getMakersForFilterList->fetchAll();

				if( !empty($results) ){
					$stash->store($results, STASH_EXPIRE);
					$ts = $stash->getTimestamp();
				}
			}

			if( $returnTs ){
				return array($ts, $results);
			} else {
				return $results;
			}

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param boolean $returnTs : flag for the function to return the list and the ts or only the list
	 * @param boolean $tsOnly : flag for the function to return the cache creation date timestamp only
	 * @return array[]
	 */
	public function getMakersNameForFilterList( $returnTs = false, $tsOnly = false ){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);

			if( $tsOnly ){
				$ts = $stash->getTimestamp();
				if( $stash->isMiss() ){
					return null;
				} else {
					return $ts;
				}
			}

			$results = $stash->get();
			$ts = null;
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getMakersNameForFilterList = $this->db->prepare("
					SELECT makerName as value
					FROM maker
					ORDER BY makerName
				");

				$getMakersNameForFilterList->execute();

				$results = $getMakersNameForFilterList->fetchAll();

				if( !empty($results) ){
					$stash->store($results, STASH_EXPIRE);
					$ts = $stash->getTimestamp();
				}
			}

			if( $returnTs ){
				return array($ts, $results);
			} else {
				return $results;
			}

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * clean the caches for the related lists
	 */
	private function _cleanCaches(){
		//clear stash cache
		$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
		$stash = new Stash($stashFileSystem);

		$toClean = array('alcohol', 'maker', 'storage');
		foreach( $toClean as $t ){
			$stash->setupKey($t);
			$stash->clear();

			if( isset($_SESSION[$t.'s']) ) unset($_SESSION[$t.'s']['list']);
		}
	}

	/**
	 * @param array $data
	 * @return integer
	 */
	public function addMaker( $data ) {
		try {
			$addMaker = $this->db->prepare("
				INSERT INTO maker (makerName)
				VALUES (:name)
			");

			$addMaker->execute(
				array(
					':name' => $data['name'],
				)
			);

			$id = $this->db->lastInsertId();

			$this->_cleanCaches();

			return $id;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param array $data
	 */
	public function updMaker( $data ) {
		try {
			$updMaker = $this->db->prepare("
				UPDATE maker
				SET makerName = :name
				WHERE makerID = :id
			");

			$updMaker->execute(
				array(
					':id' => $data['id'],
					':name' => $data['name'],
				)
			);

			$this->_cleanCaches();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 * @return boolean
	 */
	private function isUsedMaker( $id ) {
		try {
			$verif = false;

			$isUsedMaker = $this->db->prepare("
				SELECT COUNT(DISTINCT alcoholFK) AS verif
				FROM alcohols_makers
				WHERE makerFK = :id");

			$isUsedMaker->execute( array( ':id' => $id ) );

			$result = $isUsedMaker->fetch();
			if( !empty($result) && $result['verif'] == 0 ) {
				$verif = true;
			}

			return $verif;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 * @return string error message
	 */
	public function delMaker( $id ) {
		try {
			$delMaker = $this->db->prepare("
				DELETE
				FROM maker
				WHERE makerID = :id
			");

			$delMaker->execute( array( ':id' => $id ) );

			//delete alcohol link
			$delLink = $this->db->prepare("
				DELETE
				FROM alcohols_makers
				WHERE makerFK = :id
			");

			$delLink->execute( array( ':id' => $id ) );

			//delete orphan alcohol
			$delLink = $this->db->prepare("
				DELETE
				FROM alcohol
				WHERE alcoholID NOT IN ( SELECT alcoholFK FROM alcohols_makers )
			");

			$delLink->execute( array( ':id' => $id ) );

			$this->_cleanCaches();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 * @return array[][]
	 */
	public function delMakerImpact( $id ) {
		try {
			$delMakerImpact = $this->db->prepare("
				SELECT alcoholID AS impactID, alcoholTitle AS impactTitle
				FROM alcohols_view av
				INNER JOIN alcohol_makers_view abv ON alcoholID = alcoholFK
				WHERE makerID = :makerFK
				ORDER BY impactTitle
			");

			$delMakerImpact->execute( array( ':makerFK' => $id ) );

			return $delMakerImpact->fetchAll();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param string $name
	 * @return id or false
	 */
	public function isMaker( $maker ) {
		try {
			$isMaker = $this->db->prepare('
				SELECT makerID
				FROM maker
				WHERE makerName = :maker
			');

			$isMaker->execute( array( ':maker' => $maker ) );

			$result = $isMaker->fetchAll();
			if( count($result) > 0 ){
				$makerID = $result[0]['makerID'];
			} else $makerID = false;

			return $makerID;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 * @return boolean
	 */
	public function exists( $id ) {
		try {
			$verif = false;

			$exists = $this->db->prepare("
				SELECT COUNT(makerID) AS verif
				FROM maker
				WHERE makerID = :id
			");

			$exists->execute( array( ':id' => $id ) );

			$result = $exists->fetch();

			if( !empty($result) && $result['verif'] == 1 ){
				$verif = true;
			}

			return $verif;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * check and parse form data for add or update
	 * errors are returned with form inputs ids as (id, text, type)
	 *
	 * @return array[]
	 */
	public function checkAndPrepareFormData(){
		$formData = array();
		$errors = array();

		$args = array(
			'action'		=> FILTER_SANITIZE_STRING,
			'id'			=> FILTER_SANITIZE_NUMBER_INT,
			'name'			=> FILTER_SANITIZE_STRING,
		);

		foreach( $args as $field => $validation ){
			if( !filter_has_var(INPUT_POST, $field) ){
				$errors[] = array('global', 'Le champ '.$field.' est manquant.', 'error');
			}
		}

		if( empty($errors) ){

			$formData = filter_var_array($_POST, $args);

			foreach( $formData as $field => $value ){
				${$field} = $value;
			}

			//maker id
			//errors are set to #makerName because #makerID is hidden
			if( $action == 'update' ){
				if( is_null($id) || $id === false ){
					$errors[] = array('makerName', 'Identifiant incorrect.', 'error');
				} else {
					$id = filter_var($id, FILTER_VALIDATE_INT, array('min_range' => 1));
					if( $id === false ){
						$errors[] = array('makerName', 'Identifiant du producteur incorrect.', 'error');
					} else {
						//check if id exists in DB
						if( $this->exists($id) ){
							$formData['id'] = $id;
						} else {
							$errors[] = array('makerName', 'Identifiant du producteur inconnu.', 'error');
						}
					}
				}
			}

			if( $action == 'update' || $action == 'add' ){
				//name
				if( is_null($name) || $name === false ){
					$errors[] = array('makerName', 'Nom incorrect.', 'error');
				} else {
					$formData['name'] = trim($name);
				}

				//unicity
				if( empty($errors) ){
					$check = $this->isMaker($name);
					if( $check ){
						if( $action == 'add' || ($action == 'update' && $formData['id'] != $check) ){
							$errors[] = array('makerName', 'Ce producteur est déjà présent.', 'error');
						}
					}
				}
			}
		}
		$formData['errors'] = $errors;

		return $formData;
	}
}
?>
