<?php
//manage alcohols related ajax requests
try {
	require_once('../inc/conf.ini.php');

	header('Content-type: application/json');

	$action = filter_has_var(INPUT_POST, 'action');
	if( is_null($action) || $action === false ){
		throw new Exception('Gestion des alcools : action manquante.');
	}

	$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
	if( $action === false ){
		throw new Exception('Gestion des alcools : action incorrecte.');
	}

	switch ( $action ){
		case 'add' :
				$oAlcohol = new alcohol();

				$formData = $oAlcohol->checkAndPrepareFormData();

				if ( empty($formData['errors']) ) {
					$id = $oAlcohol->addAlcohol( $formData );
					$response = 'ok';
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'update' :
				$oAlcohol = new alcohol();

				$formData = $oAlcohol->checkAndPrepareFormData();

				if ( empty($formData['errors']) ) {
					$id = $oAlcohol->updAlcohol( $formData );
					$response = 'ok';
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'delete' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des alcools : identitifant de l\'alcohol manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des alcools : identifiant incorrect.');
				}

				$oAlcohol = new alcohol();
				$oAlcohol->delAlcohol( $id );
				$response = 'ok';
			break;
		case 'get' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des alcools : identitifant de l\'alcohol manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des alcools : identifiant incorrect.');
				}

				if(    isset($_SESSION['alcohols'])
					&& isset($_SESSION['alcohols']['list'])
					&& isset($_SESSION['alcohols']['list'][$id])
					&& !empty($_SESSION['alcohols']['list'][$id])
				){
					$response = $_SESSION['alcohols']['list'][$id];

				} else {
					$oAlcohol = new alcohol();
					$response = $oAlcohol->getAlcoholById($id);
				}

				if( empty($response) ){
					throw new Exception('Gestion des alcools : identitifant de l\'alcohol incorrect.');
				}
			break;
		case 'list' :
				$type = filter_has_var(INPUT_POST, 'type');
				if( is_null($type) || $type === false ){
					throw new Exception('Gestion des alcools : type de recherche manquant.');
				}

				$type = filter_var($_POST['type'], FILTER_VALIDATE_INT, array('min_range' => 0, 'max-range' => 2));
				if( $type === false ){
					throw new Exception('Gestion des alcools : type de recherche incorrect.');
				}

				if( !isset($_SESSION['alcohols']) || !isset($_SESSION['alcohols']['page']) ){
					$_SESSION['alcohols']['page'] = 0;
					$_SESSION['alcohols']['numPerPage'] = 20;
				}

				if( $type == 0 ){
					$_SESSION['alcohols']['page'] = ( isset($_SESSION['alcohols']['page']) ? $_SESSION['alcohols']['page'] : 0 );
					$_SESSION['alcoholListFilters'] = array();
				}
				if( $type == 1 ){
					$_SESSION['alcohols']['page'] = 0;
				}

				if( $type == 1 ){
					$_SESSION['alcoholListFilters'] = $_POST;
				} else {
					$_POST = $_SESSION['alcoholListFilters'];
				}

				$oAlcohol = new alcohol();
				if( $type == 0 ) $alcohols = $oAlcohol->getAlcohols();
				else $alcohols = $oAlcohol->getAlcoholsByFullTextSearch();

				//save the list on session for future pagination
				$_SESSION['alcohols']['list'] = $alcohols;
				$_SESSION['alcohols']['total'] = count($alcohols);

				if( $type == 2 || ( $type == 0 && $_SESSION['alcohols']['page'] > 0 ) ){
					$alcohols = array_slice( $alcohols, 0, $_SESSION['alcohols']['numPerPage'] * ($_SESSION['alcohols']['page']+1), false );
				} else {
					$alcohols = array_slice( $alcohols, $_SESSION['alcohols']['numPerPage'] * $_SESSION['alcohols']['page'], $_SESSION['alcohols']['numPerPage'], false );
				}

				if( $type == 2 || ( $type == 0 && $_SESSION['alcohols']['page'] > 0 ) ){
					$nb = count($alcohols);
				} else {
					$nb = $_SESSION['alcohols']['numPerPage'] * $_SESSION['alcohols']['page'] + count($alcohols);
				}

				if( $nb > $_SESSION['alcohols']['total'] ) $nb = $_SESSION['alcohols']['total'];

				$response = array('nb' => $nb, 'total' => $_SESSION['alcohols']['total'], 'list' => $alcohols);

			break;
		case 'more':
				if( isset($_SESSION['alcohols']) ){
					$_SESSION['alcohols']['page']++;
					$alcohols = array_slice( $_SESSION['alcohols']['list'], $_SESSION['alcohols']['numPerPage'] * $_SESSION['alcohols']['page'], $_SESSION['alcohols']['numPerPage'], false );
					$type = 3;
					$nb = $_SESSION['alcohols']['numPerPage'] * $_SESSION['alcohols']['page'] + count($alcohols);

					$response = array('nb' => $nb, 'total' => $_SESSION['alcohols']['total'], 'list' => $alcohols);

				} else {
					throw new Exception('Gestion des alcools : pagination impossible, liste non disponible.');
				}
			break;
		default:
			throw new Exception('Gestion des alcools : action non reconnue.');
	}

	echo json_encode($response);
	die;

} catch (Exception $e) {
	header($_SERVER["SERVER_PROTOCOL"]." 555 Response with exception");
	echo json_encode($e->getMessage());
	die;
}
?>
