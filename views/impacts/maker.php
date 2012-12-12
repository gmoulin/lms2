<?php
if( count($response) ){
?>
	<form id="impact_maker" name="impact_maker" method="post" action="" class="impact-form form-inline">
		Vous devez au préalable définir un nouveau rangement pour les alcools suivant :
		<br />
		<ul>
		<?php
			$i = 0;
			foreach( $response as $impact ){ ?>
			<li>
				<input type="checkbox" id="<?php echo $impact['type']; ?>ID_<?php echo $i; ?>" name="<?php echo $impact['type']; ?>ID[]" value="<?php echo $impact['impactID']; ?>" />
				<label for="<?php echo $impact['type']; ?>ID_<?php echo $i; ?>"><?php echo $impact['impactTitle']; ?></label>
			</li>
		<?php
			}
		?>
		</ul>
		<label for="impactStorageList">Rangement</label>
		<select id="impactStorageList" name="storage" required>
			<option value="">nouveau lieu de rangement</option>
		</select>
		<button type="submit" class="btn btn-warning relocate">Enregistrer</button>
	</form>
<?php } ?>

