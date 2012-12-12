<?php
//manage makers related ajax requests
try {
	require_once('../inc/conf.ini.php');

	header('Content-type: application/json');

	$action = filter_has_var(INPUT_POST, 'action');
	if( is_null($action) || $action === false ){
		throw new Exception('Gestion des producteurs : action manquante.');
	}

	$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
	if( $action === false ){
		throw new Exception('Gestion des producteurs : action incorrecte.');
	}

	switch ( $action ){
		case 'add' :
				$oMaker = new maker();

				$formData = $oMaker->checkAndPrepareFormData();

				if ( empty($formData['errors']) ) {
					$oMaker->addMaker( $formData );
					$response = 'ok';

					if( isset($_SESSION['makers']) ) unset($_SESSION['makers']['list']);
					if( isset($_SESSION['alcohols']) ) unset($_SESSION['alcohols']['list']);
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'update' :
				$oMaker = new maker();

				$formData = $oMaker->checkAndPrepareFormData();

				if ( empty($formData['errors']) ) {
					$oMaker->updMaker( $formData );
					$response = 'ok';

					if( isset($_SESSION['makers']) ) unset($_SESSION['makers']['list']);
					if( isset($_SESSION['alcohols']) ) unset($_SESSION['alcohols']['list']);
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'delete' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des producteurs : identitifant du producteur manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des producteurs : identifiant incorrect.');
				}

				$oMaker = new maker();
				$oMaker->delMaker( $id );
				$response = "ok";

				if( isset($_SESSION['makers']) ) unset($_SESSION['makers']['list']);
				if( isset($_SESSION['alcohols']) ) unset($_SESSION['alcohols']['list']);
			break;
		case 'impact' : //on deletion
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des producteurs : identitifant du producteur manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des producteurs : identifiant incorrect.');
				}

				$oMaker = new maker();
				$response = $oMaker->delMakerImpact( $id );

				include( '../views/impacts/maker.php' );
				die;
			break;
		case 'get' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des producteurs : identitifant du producteur manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des producteurs : identifiant incorrect.');
				}

				if(    isset($_SESSION['makers'])
					&& isset($_SESSION['makers']['list'])
					&& isset($_SESSION['makers']['list'][$id])
					&& !empty($_SESSION['makers']['list'][$id])
				){
					$response = $_SESSION['makers']['list'][$id];

				} else {
					$oMaker = new maker();
					$response = $oMaker->getMakerById($id);

					if( !is_array($response) || empty($response) ){
						throw new Exception('Gestion des producteurs : identitifant du producteur incorrect.');
					}

					$response = $response[0];
				}
			break;
		case 'list' :
				$type = filter_has_var(INPUT_POST, 'type');
				if( is_null($type) || $type === false ){
					throw new Exception('Gestion des producteurs : type de recherche manquant.');
				}

				$type = filter_var($_POST['type'], FILTER_VALIDATE_INT, array('min_range' => 0, 'max-range' => 2));
				if( $type === false ){
					throw new Exception('Gestion des producteurs : type de recherche incorrect.');
				}

				if( !isset($_SESSION['makers']) || !isset($_SESSION['makers']['page']) ){
					$_SESSION['makers']['page'] = 0;
					$_SESSION['makers']['numPerPage'] = 120;
				}

				if( $type == 0 ){
					$_SESSION['makers']['page'] = ( isset($_SESSION['makers']['page']) ? $_SESSION['makers']['page'] : 0 );
					$_SESSION['makerListFilters'] = array();
				}
				if( $type == 1 ){
					$_SESSION['makers']['page'] = 0;
				}

				if( $type == 1 ){
					$_SESSION['makerListFilters'] = $_POST;
				} else {
					$_POST = $_SESSION['makerListFilters'];
				}

				$oMaker = new maker();
				if( $type == 0 ) $response = $oMaker->getMakers();
				else $response = $oMaker->getMakersByFullTextSearch();

				$makers = array();
				foreach( $response as $a ){
					$makers[$a['makerID']] = $a;
				}

				//save the list on session for future pagination
				$_SESSION['makers']['list'] = $makers;
				$_SESSION['makers']['total'] = count($makers);

				if( $type == 2 || ( $type == 0 && $_SESSION['makers']['page'] > 0 ) ){
					$makers = array_slice( $makers, 0, $_SESSION['makers']['numPerPage'] * ($_SESSION['makers']['page']+1), false );
				} else {
					$makers = array_slice( $makers, $_SESSION['makers']['numPerPage'] * $_SESSION['makers']['page'], $_SESSION['makers']['numPerPage'], false );
				}

				if( $type == 2 || ( $type == 0 && $_SESSION['makers']['page'] > 0 ) ){
					$nb = count($makers);
				} else {
					$nb = $_SESSION['makers']['numPerPage'] * $_SESSION['makers']['page'] + count($makers);
				}

				if( $nb > $_SESSION['makers']['total'] ) $nb = $_SESSION['makers']['total'];

				$response = array('nb' => $nb, 'total' => $_SESSION['makers']['total'], 'list' => $makers);

			break;
		case 'more':
				if( isset($_SESSION['makers']) ){
					$_SESSION['makers']['page']++;
					$makers = array_slice( $_SESSION['makers']['list'], $_SESSION['makers']['numPerPage'] * $_SESSION['makers']['page'], $_SESSION['makers']['numPerPage'], false );
					$type = 3;
					$nb = $_SESSION['makers']['numPerPage'] * $_SESSION['makers']['page'] + count($makers);

					$response = array('nb' => $nb, 'total' => $_SESSION['makers']['total'], 'list' => $makers);

				} else {
					throw new Exception('Gestion des producteurs : pagination impossible, liste non disponible.');
				}
			break;
		default:
			throw new Exception('Gestion des producteurs : action non reconnue.');
	}

	echo json_encode($response);
	die;

} catch (Exception $e) {
	header($_SERVER["SERVER_PROTOCOL"]." 555 Response with exception");
	echo json_encode($e->getMessage());
	die;
}
?>
