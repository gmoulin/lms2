<?php
if( count($response) ){
?>
	<form id="impact_author" name="impactAuthor" method="post" action="" class="impact-form form-inline">
		Vous devez au préalable définir un nouveau rangement pour les livres suivant :
		<br />
		<?php
			$i = 0;
			$currentSaga = null;
			$opened = false;
			foreach( $response as $impact ){
				if( $currentSaga != $impact['sagaTitle'] || ($i == 0 && is_null($impact['sagaTitle'])) ){
					$currentSaga = $impact['sagaTitle'];
					if( $opened ){
						$opened = false;
						echo '</ul>';
					}

					if( !empty($impact['sagaTitle']) ){
		?>
						<h3>
							<input type="checkbox">
							<?php echo $impact['sagaTitle']; ?>
						</h3>
		<?php
					}
		?>
					<ul>
		<?php
					$opened = true;
				}
		?>
			<li>
				<input type="checkbox" id="<?php echo $impact['type']; ?>ID_<?php echo $i; ?>" name="<?php echo $impact['type']; ?>ID[]" value="<?php echo $impact['impactID']; ?>" />
				<label for="<?php echo $impact['type']; ?>ID_<?php echo $i; ?>"><?php echo $impact['impactTitle']; ?></label>
			</li>
		<?php }
			if( $opened ){
				$opened = false;
				echo '</ul>';
			}
		?>
		<label for="impactStorageList">Rangement</label>
		<select id="impactStorageList" name="storage" required>
			<option value="">nouveau lieu de rangement</option>
		</select>
		<button type="submit" class="btn btn-warning relocate">Enregistrer</button>
	</form>
<?php } ?>

