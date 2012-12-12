<?php
/**
 * Class for alcohol management
 *
 * class name is in lowerclass to match table name ("commun" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Alcohols
 * @category Alcohols
 */
class alcohol extends commun {
	private $_sortTypes = array(
		'alcoholName, makerName, alcoholYear',
		'alcoholName DESC, makerName, alcoholYear',
		'makerName, alcoholName, alcoholYear',
		'makerName DESC, alcoholName, alcoholYear',
		'storageRoom, storageType, storageColumn, storageLine, alcoholName, makerName, alcoholYear',
		'storageRoom DESC, storageType, storageColumn, storageLine, alcoholName, makerName, alcoholYear',
		'alcoholDate, alcoholName, makerName, alcoholYear',
		'alcoholDate DESC, alcoholName, makerName, alcoholYear',
	);

	// Constructor
	public function __construct(){
		//for "commun" ($this->db & co)
		parent::__construct();
	}

	/**
	 * @return array[][]
	 */
	public function getAlcohols(){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getAlcohols = $this->db->prepare("
					SELECT  alcoholID, alcoholName, alcoholType, alcoholYear, alcoholRating, alcoholOfferedBy, alcoholDate,
							storageID, storageRoom, storageType, storageColumn, storageLine,
							makerID, makerName
					FROM alcohols_view
					INNER JOIN alcohol_makers_view ON alcoholID = alcoholFK
					ORDER BY ".$this->_sortTypes[0]."
				");

				$getAlcohols->execute();

				$results = $this->_merge($getAlcohols->fetchAll());

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * dupplicate the alcohols_view table joinned with alcohol_makers_view into a myisam temporary table for full text search
	 * @param array $filters
	 * @return array[][]
	 */
	public function getAlcoholsByFullTextSearch(){
		try {
			//sanitize the form data
			$args = array(
				'alcoholSearch'			=> FILTER_SANITIZE_STRING,
				'alcoholNameFilter'		=> FILTER_SANITIZE_STRING,
				'alcoholMakerFilter'		=> FILTER_SANITIZE_STRING,
				'alcoholStorageFilter'	=> FILTER_SANITIZE_NUMBER_INT,
				'alcoholSortType'			=> FILTER_SANITIZE_NUMBER_INT,
			);
			$filters = filter_var_array($_POST, $args);

			$filters['alcoholStorageFilter'] = filter_var($filters['alcoholStorageFilter'], FILTER_VALIDATE_INT, array('min_range' => 1));
			$filters['alcoholSortType'] = filter_var($filters['alcoholSortType'], FILTER_VALIDATE_INT, array('min_range' => 0, 'max-range' => 7));
			if( $filters['alcoholSortType'] === false ) $filters['alcoholSortType'] = 0;

			//construct the query
			$sql = " SELECT *";

			$sqlSelect = array();
			$sqlWhere = array();
			$sqlOrder = 'score DESC, ';
			$params = array();
			if( !empty($filters['alcoholSearch']) ){
				$sqlSelect = array(
					"MATCH(alcoholName) AGAINST (:searchS)",
					"MATCH(bft.makerName) AGAINST (:searchS)",
				);
				$sqlWhere = array(
					"MATCH(alcoholName) AGAINST (:searchW)",
					"MATCH(bft.makerName) AGAINST (:searchW)",
				);
				$params[':searchS'] = $this->prepareForFullTextQuery($filters['alcoholSearch']);
				$params[':searchW'] = $params[':searchS'];
			}
			if( !empty($filters['alcoholNameFilter']) ){
				$sqlSelect[] = "MATCH(alcoholName) AGAINST (:alcoholNameS)";
				$sqlWhere[] = "MATCH(alcoholName) AGAINST (:alcoholNameW)";
				$params[':alcoholNameS'] = $this->prepareForFullTextQuery($filters['alcoholNameFilter']);
				$params[':alcoholNameW'] = $params[':alcoholNameS'];
			}
			if( !empty($filters['alcoholMakerFilter']) ){
				$sqlSelect[] = "MATCH(baft.makerName) AGAINST (:makerS)";
				$sqlWhere[] = "MATCH(baft.makerName) AGAINST (:makerW)";
				$params[':makerS'] = $this->prepareForFullTextQuery($filters['alcoholMakerFilter']);
				$params[':makerW'] = $params[':makerS'];
			}
			if( !empty($filters['alcoholStorageFilter']) ){
				$sqlWhere[] = "storageID = :storageID";
				$params[':storageID'] = $filters['alcoholStorageFilter'];
			}

			$sql = " SELECT bft.*, ba.*"
				  .( !empty($sqlSelect) ? ', '.implode(' + ', $sqlSelect).' AS score' : '')
				  ." FROM alcohols_view_ft bft"
				  ." INNER JOIN alcohol_makers_view_ft baft ON alcoholID = baft.alcoholFK "
				  ." LEFT JOIN alcohol_makers_view ba ON alcoholID = ba.alcoholFK "
				  ." WHERE 1 "
				  .( !empty($sqlWhere) ? ' AND '.implode(' AND ', $sqlWhere) : '')
				  ." ORDER BY "
				  .( !empty($sqlSelect) ? $sqlOrder : '')
				  .$this->_sortTypes[$filters['alcoholSortType']];

			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			if( empty($params) ) $stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $sql);
			else $stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $sql, serialize($params));
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them

				//drop the temporary table if it exists
				$destroyTmpTable = $this->db->prepare("DROP TEMPORARY TABLE IF EXISTS alcohols_view_ft");
				$destroyTmpTable->execute();
				$destroyTmpTable = $this->db->prepare("DROP TEMPORARY TABLE IF EXISTS alcohol_makers_view_ft");
				$destroyTmpTable->execute();

				//create the temporary table
				$tmpTable = $this->db->prepare("
					CREATE TEMPORARY TABLE alcohols_view_ft AS
					SELECT  alcoholID, alcoholName, alcoholType, alcoholYear, alcoholRating, alcoholOfferedBy, alcoholDate,
							storageID, storageRoom, storageType, storageColumn, storageLine
					FROM alcohols_view
				");
				$tmpTable->execute();

				//add the fulltext index
				$indexTmpTable = $this->db->prepare("
					ALTER TABLE alcohols_view_ft ENGINE = MyISAM,
					ADD FULLTEXT INDEX alcoholFT (alcoholName alcoholType, alcoholOfferedBy),
					ADD INDEX storageID (storageID),
					ADD INDEX alcoholID (alcoholID)
				");
				$indexTmpTable->execute();

				//create the temporary table
				$tmpTable = $this->db->prepare("
					CREATE TEMPORARY TABLE alcohol_makers_view_ft AS
					SELECT  alcoholFK, makerID, makerName
					FROM alcohol_makers_view
				");
				$tmpTable->execute();

				//add the fulltext index
				$indexTmpTable = $this->db->prepare("
					ALTER TABLE alcohol_makers_view_ft ENGINE = MyISAM,
					ADD FULLTEXT INDEX makerFT (makerName),
					ADD INDEX alcoholFK (alcoholFK)
				");
				$indexTmpTable->execute();

				$getAlcohols = $this->db->prepare($sql);

				$getAlcohols->execute( $params );

				$results = $this->_merge($getAlcohols->fetchAll());

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * merged multiple lines into one with a sub array
	 * @params array $results
	 */
	private function _merge($results){
		if( !empty($results) ){
			$currentId = null;
			$merged = array();
			foreach( $results as $r ){

				if( $currentId != $r['alcoholID'] && !isset($merged[$r['alcoholID']]) ){
					$currentId = $r['alcoholID'];

					$merged[$r['alcoholID']]['alcoholID'] = $r['alcoholID'];
					$merged[$r['alcoholID']]['alcoholName'] = $r['alcoholName'];
					$merged[$r['alcoholID']]['alcoholType'] = $r['alcoholType'];
					$merged[$r['alcoholID']]['alcoholYear'] = $r['alcoholYear'];
					$merged[$r['alcoholID']]['alcoholRating'] = $r['alcoholRating'];
					$merged[$r['alcoholID']]['alcoholOfferedBy'] = $r['alcoholOfferedBy'];
					$merged[$r['alcoholID']]['storageID'] = $r['storageID'];
					$merged[$r['alcoholID']]['storageRoom'] = $r['storageRoom'];
					$merged[$r['alcoholID']]['storageType'] = $r['storageType'];
					$merged[$r['alcoholID']]['storageColumn'] = $r['storageColumn'];
					$merged[$r['alcoholID']]['storageLine'] = $r['storageLine'];
					$merged[$r['alcoholID']]['makers'] = array();
				}

				$merged[$r['alcoholID']]['makers'][$r['makerID']] = array(
					'makerID' => $r['makerID'],
					'makerName' => $r['makerName'],
				);
			}

			$results = $merged;
		}

		return $results;
	}

	/**
	 * @param integer $id
	 * @return array[][]
	 */
	public function getAlcoholDateById( $id ){
		try {
			$getAlcoholDateById = $this->db->prepare("
				SELECT alcoholDate AS lastModified
				FROM alcohol
				WHERE alcoholID = :id
			");

			$getAlcoholDateById->execute( array( ':id' => $id ) );

			$results = $getAlcoholDateById->fetch();
			if( !empty($results) ) $results = $results['lastModified'];

			return $results;

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 * @return array[][]
	 */
	public function getAlcoholCoverById( $id ){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler('covers', $stashFileSystem);
			$stash = StashBox::getCache('covers', get_class($this), $id);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getAlcoholCoverById = $this->db->prepare("
					SELECT alcoholCover AS cover
					FROM alcohol
					WHERE alcoholID = :id
				");

				$getAlcoholCoverById->execute( array( ':id' => $id ) );

				$results = $getAlcoholCoverById->fetch();
				if( !empty($results) ){
					$results = base64_decode($results['cover']);
					$stash->store($results, STASH_EXPIRE);
				}
			}

			return $results;

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 * @return array[]
	 */
	public function getAlcoholById( $id ){
		try {
			$getAlcoholById = $this->db->prepare("
				SELECT  alcoholID, alcoholName, alcoholType, alcoholYear, alcoholRating, alcoholOfferedBy,
						storageID, storageRoom, storageType, storageColumn, storageLine,
						makerID, makerName
				FROM alcohols_view
				INNER JOIN alcohol_makers_view ON alcoholID = alcoholFK
				WHERE alcoholID = :id
			");

			$getAlcoholById->execute( array( ':id' => $id ) );

			$results = $this->_merge($getAlcoholById->fetchAll());

			return $results[$id];

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param boolean $returnTs : flag for the function to return the list and the ts or only the list
	 * @param boolean $tsOnly : flag for the function to return the cache creation date timestamp only
	 * @return array[]
	 */
	public function getAlcoholsTypes( $returnTs = false, $tsOnly = false ){
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
				$getAlcoholsTypes = $this->db->prepare("
					SELECT alcoholType as value
					FROM alcohol
					GROUP BY alcoholType
					ORDER BY alcoholType
				");

				$getAlcoholsTypes->execute();

				$results = $getAlcoholsTypes->fetchAll();

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
	public function getAlcoholsOfferedBys( $returnTs = false, $tsOnly = false ){
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
				$getAlcoholsOfferedBys = $this->db->prepare("
					SELECT alcoholOfferedBy as value
					FROM alcohol
					GROUP BY alcoholOfferedBy
					ORDER BY alcoholOfferedBy
				");

				$getAlcoholsOfferedBys->execute();

				$results = $getAlcoholsOfferedBys->fetchAll();

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
	public function getAlcoholsNameForFilterList( $returnTs = false, $tsOnly = false ){
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
				$getAlcoholsNameForFilterList = $this->db->prepare("
					SELECT alcoholName as value
					FROM alcohol
					GROUP BY alcoholName
					ORDER BY alcoholName
				");

				$getAlcoholsNameForFilterList->execute();

				$results = $getAlcoholsNameForFilterList->fetchAll();

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
	public function getAlcoholsOfferedByForFilterList( $returnTs = false, $tsOnly = false ){
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
				$getAlcoholsOfferedByForFilterList = $this->db->prepare("
					SELECT alcoholOfferedBy as value
					FROM alcohol
					GROUP BY alcoholOfferedBy
					ORDER BY alcoholOfferedBy
				");

				$getAlcoholsOfferedByForFilterList->execute();

				$results = $getAlcoholsOfferedByForFilterList->fetchAll();

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
	 * @param array $data
	 * @param integer $alcoholID
	 */
	private function _manageMakerLink( $data, $alcoholID ){
		//maker link deletion
		if( isset($data['id']) ){
			$delMakersLinks = $this->db->prepare("
				DELETE
				FROM alcohols_makers
				WHERE alcoholFK = :id
			");

			$delMakersLinks->execute( array( ':id' => $data['id'] ) );
		}

		$addMakerLink = $this->db->prepare("
			INSERT INTO alcohols_makers (alcoholFK, makerFK)
			VALUES (:alcoholID, :makerID)
		");

		$oMaker = new maker();
		foreach ( $data['makers'] as $maker ){
			//checking if maker already exists
			$makerID = $oMaker->isMaker($maker);
			if( $makerID === false ){
				//get first and last name
				$name = explode(' ', $maker);

				$makerID = $oMaker->addMaker( array(
					'name' => trim($name),
				) );
			}
			if( empty($makerID) ) throw new PDOException('Bad maker id for '.$maker.'.');

			$addMakerLink->execute(
				array(
					':alcoholID' => $alcoholID,
					':makerID' => $makerID,
				)
			);
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
	public function addAlcohol( $data ){
		try {
			set_error_handler("errorHandler"); //handle any error and throw exception, forcing transaction rollback

			$this->db->beginTransaction(); //needed for rollback

			//alcohol
			$addAlcohol = $this->db->prepare("
				INSERT INTO alcohol (alcoholName, alcoholCover, alcoholType, alcoholYear, alcoholRating, alcoholOfferedBy, alcoholStorageFK, alcoholDate)
				VALUES (:name, :cover, :type, :year, :rating, :offeredBy, :storage, NULL, NOW())
			");

			$addAlcohol->execute(
				array(
					':name' => $data['name'],
					':cover' => $data['cover'],
					':type' => $data['type'],
					':year' => $data['year'],
					':rating' => $data['rating'],
					':offeredBy' => $data['offeredBy'],
					':storage' => $data['storage'],
				)
			);

			$alcoholID = $this->db->lastInsertId();

			if( empty($alcoholID) ) throw new PDOException('Bad alcohol id.');

			//maker(s)
			$this->_manageMakerLink( $data, $alcoholID );

			$this->db->commit(); //transaction validation

			restore_error_handler();

			$this->_cleanCaches();

			//create stash cache
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			$stash = new Stash($stashFileSystem);
			$stash->setupKey('covers', get_class($this), $alcoholID);
			$stash->store(base64_decode($data['cover']), STASH_EXPIRE);

			return $alcoholID;

		} catch ( Exception $e ){
			restore_error_handler();
			$this->db->rollBack(); //cancel transaction
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param array $data
	 */
	public function updAlcohol( $data ){
		try {
			set_error_handler("errorHandler"); //handle any error and throw exception, forcing transaction rollback

			$this->db->beginTransaction(); //needed for rollback

			//alcohol
			$updAlcohol = $this->db->prepare("
				UPDATE alcohol
				SET alcoholName = :name,
					".( isset($data['cover']) && !empty($data['cover']) ? "alcoholCover = :cover," : "")."
					alcoholType = :type,
					alcoholYear = :year,
					alcoholRating = :rating,
					alcoholOfferedBy = :offeredBy,
					alcoholStorageFK = :storage,
					alcoholDate = NOW()
				WHERE alcoholID = :id
			");

			$params = array(
				':id' => $data['id'],
				':name' => $data['name'],
				':type' => $data['type'],
				':year' => $data['year'],
				':rating' => $data['rating'],
				':offeredBy' => $data['offeredBy'],
				':storage' => $data['storage']
			);

			if( isset($data['cover']) && !empty($data['cover']) ){
				$params[':cover'] = $data['cover'];

				//update stash cache
				$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
				$stash = new Stash($stashFileSystem);
				$stash->setupKey('covers', get_class($this), $data['id']);
				$stash->store(base64_decode($data['cover']), STASH_EXPIRE);
			}

			$updAlcohol->execute( $params );

			//maker(s)
			$this->_manageMakerLink( $data, $data['id'] );

			$this->db->commit(); //transaction validation

			restore_error_handler();

			$this->_cleanCaches();

		} catch ( Exception $e ){
			restore_error_handler();
			$this->db->rollBack(); //cancel transaction
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 */
	public function delAlcohol( $id ){
		try {
			set_error_handler("errorHandler"); //handle any error and throw exception, forcing transaction rollback

			$this->db->beginTransaction(); //needed for rollback

			//loan deletion
			$this->_delLinkedLoan( $id, true );

			//alcohol deletion
			$delAlcohol = $this->db->prepare("
				DELETE
				FROM alcohol
				WHERE alcoholID = :id
			");

			$delAlcohol->execute( array( ':id' => $id ) );

			if( isset($_SESSION['images']['alcohols'][$id]) ) unset($_SESSION['images']['alcohols'][$id]);

			//maker link deletion
			$delMakersLinks = $this->db->prepare("
				DELETE
				FROM alcohols_makers
				WHERE alcoholFK = :id
			");

			$delMakersLinks->execute( array( ':id' => $id ) );

			$this->db->commit(); //transaction validation

			restore_error_handler();

			$this->_cleanCaches();
			$this->cleanImageCache($data['id']);

		} catch ( Exception $e ){
			restore_error_handler();
			$this->db->rollBack(); //cancel transaction
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
				SELECT COUNT(alcoholID) AS verif
				FROM alcohol
				WHERE alcoholID = :id
			");

			$exists->execute( array( ':id' => $id ) );

			$result = $exists->fetch();

			if( !empty($result) && $result['verif'] == 1 ) {
				$verif = true;
			}

			return $verif;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 * @return boolean
	 */
	public function alcoholUnicityCheck( $data ) {
		try {
			$alcoholNameCheck = $this->db->prepare("
				SELECT alcoholID
				FROM alcohol
				WHERE alcoholName = :name
				AND alcoholType = :type
				AND alcoholYear = :year
			");

			$params = array(
				':name' => $data['name'],
				':type' => $data['type'],
				':year' => $data['year'],
			);

			$alcoholNameCheck->execute( $params );

			$result = $alcoholNameCheck->fetchAll();

			if( empty($result) ) {
				return true;
			} elseif( isset($data['id']) ){
				return $result[0]['alcoholID'] == $data['id'];
			}

			return false;

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
		$makerFields = array();

		$args = array(
			'action'	=> FILTER_SANITIZE_STRING,
			'id'		=> FILTER_SANITIZE_NUMBER_INT,
			'name'		=> FILTER_SANITIZE_STRING,
			'type'		=> FILTER_SANITIZE_STRING,
			'year'		=> FILTER_SANITIZE_NUMBER_INT,
			'rating'	=> FILTER_SANITIZE_NUMBER_INT,
			'offeredBy'	=> FILTER_SANITIZE_STRING,
			'cover'		=> FILTER_SANITIZE_STRING,
			'storage'	=> FILTER_SANITIZE_NUMBER_INT,
		);

		foreach( $args as $field => $validation ){
			if( !filter_has_var(INPUT_POST, $field) ){
				$errors[] = array('global', 'Le champ '.$field.' est manquant.', 'error');
			}
		}

		//makers with variable name save
		$indice = null;
		foreach( $_POST as $key => $val ){
			if( strpos($key, 'maker_') !== false ){
				$indice = substr($key, strpos($key, '_') + 1);

				$args['maker_'.$indice] = FILTER_SANITIZE_STRING;
				$makerFields[] = 'maker_'.$indice;
			}
		}

		if( empty($errors) ){

			$formData = filter_var_array($_POST, $args);

			foreach( $formData as $field => $value ){
				${$field} = $value;
			}

			//alcohol id
			//errors are set to #alcoholName because #alcoholID is hidden
			if( $action == 'update' ){
				if( is_null($id) || $id === false ){
					$errors[] = array('alcoholName', 'Identifiant incorrect.', 'error');
				} else {
					$id = filter_var($id, FILTER_VALIDATE_INT, array('min_range' => 1));
					if( $id === false ){
						$errors[] = array('alcoholName', 'Identifiant de l\'alcohol incorrect.', 'error');
					} else {
						//check if id exists in DB
						if( $this->exists($id) ){
							$formData['id'] = $id;
						} else {
							$errors[] = array('alcoholName', 'Identifiant de l\'alcohol inconnu.', 'error');
						}
					}
				}
			}

			if( $action == 'update' || $action == 'add' ){
				//name
				if( is_null($name) || $name === false ){
					$errors[] = array('alcoholName', 'Titre incorrect.', 'error');
				} elseif( empty($name) ){
					$errors[] = array('alcoholName', 'Le titre est requis.', 'required');
				} else {
					$formData['name'] = trim($name);
				}

				//type
				if( is_null($type) || $type === false ){
					$errors[] = array('alcoholType', 'Type incorrect.', 'error');
				} elseif( empty($type) ){
					$errors[] = array('alcoholType', 'Le type est requis.', 'required');
				} else {
					$formData['type'] = trim($type);
				}

				//year
				if( is_null($year) || $year === false ){
					$errors[] = array('alcoholYear', 'Année incorrecte.', 'error');
				} elseif( empty($year) ){
					$errors[] = array('alcoholYear', 'L\'année est requise.', 'required');
				} else {
					$formData['year'] = trim($year);
				}

				//rating
				if( is_null($rating) || $rating === false ){
					$errors[] = array('alcoholRating', 'Qualité incorrecte.', 'error');
				} else {
					$formData['rating'] = trim($rating);
				}

				//offeredBy
				if( is_null($offeredBy) || $offeredBy === false ){
					$errors[] = array('alcoholOfferedBy', 'Offert par incorrect.', 'error');
				} else {
					$formData['offeredBy'] = trim($offeredBy);
				}

				//cover
				if( is_null($cover) || $cover === false ){
					$errors[] = array('alcoholCoversStatus', 'Couverture incorrecte.', 'error');
				} elseif( !empty($cover) ){
					if( !file_exists(UPLOAD_COVER_PATH.$cover) ){
						$errors[] = array('alcoholCoversStatus', 'Couverture non trouvée.', 'error');
					} else {
						$formData['cover'] = chunk_split( base64_encode( file_get_contents( UPLOAD_COVER_PATH.$cover ) ) );
					}
				} else {
					$formData['cover'] = null;
				}

				//unicity check for name + type
				if( empty($errors) && $action == 'add' ){
					if( !$this->alcoholUnicityCheck($formData) ){
						$errors[] = array('alcoholName', 'Alcohol déjà présent. (unicité sur titre + type)', 'error');
					}
				}

				//storage
				if( empty($storage) ){
					$errors[] = array('alcoholStorage', 'Le rangement est requis.', 'required');
				}
				if( is_null($storage) || $storage === false ){
					$errors[] = array('alcoholStorage', 'Rangement incorrect.', 'error');
				} elseif( empty($storage) ){
					$errors[] = array('alcoholStorage', 'Le rangement est requis.', 'required');
				} else {
					$formData['storage'] = trim($storage);
				}

				//maker
				$makers = array();
				$atLeastOneMaker = false;
				foreach( $makerFields as $field ){
					if( is_null(${$field}) || ${$field} === false ){
						$errors[] = array($field, 'Producteur incorrect.', 'error');
					} elseif( !empty(${$field}) ){
						$makers[] = trim(${$field});
						$atLeastOneMaker = true;
					}
				}

				if( !$atLeastOneMaker ){
					$errors[] = array('alcoholMakers_1', 'Au moins un producteur est requis.', 'required');
				} else {
					$formData['makers'] = $makers;
				}
			}
		}
		$formData['errors'] = $errors;

		return $formData;
	}
}
?>
