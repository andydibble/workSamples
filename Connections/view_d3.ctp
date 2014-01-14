<?php echo $this->Html->script('http://d3js.org/d3.v3.min.js'); ?>

<?php const NODE_RADIUS = 30;?>
<?php const FOCUS_NODE_RADIUS = 40;?>

<script>
var NODE_RADIUS = <?php echo NODE_RADIUS ?>;
var FOCUS_NODE_RADIUS = <?php echo FOCUS_NODE_RADIUS ?>;
var data = '<?php echo $graph ?>';
var focusId = <?php echo $focusId ?>;
</script>

<?php echo $this->Html->script('ViewD3'); ?>


<svg height="970" version="1.1" width="970"
	xmlns="http://www.w3.org/2000/svg" class="message-area">	

<defs> 

<?php foreach($pics as $i => $pic):

if($pic['user_id']):
?>   
	<?php $r = $pic['user_id'] == $authUser['id'] ? FOCUS_NODE_RADIUS : NODE_RADIUS; ?> 
	<pattern id="user-img-<?php echo $pic['user_id'] ?>" 
		x="<?php ECHO $r?>" 
		y="<?php ECHO $r?>"
		patternUnits="userSpaceOnUse" 
		width="<?php ECHO $r*2?>" 
		height="<?php ECHO $r*2?>"> 		                
	    <image xlink:href="<?php echo Router::url($pic['src'], true);?>" 
	    	width="<?php ECHO $r*2?>" 
	    	height="<?php ECHO $r*2?>" />
	</pattern>    
	<?php endif; ?>
<?php endforeach;?>

<?php $r = NODE_RADIUS; ?>
<pattern id="no-img"
	x="<?php ECHO $r?>" 
	y="<?php ECHO $r?>"
	patternUnits="userSpaceOnUse" 
	width="<?php ECHO $r*2?>" 
	height="<?php ECHO $r*2?>"> 		                
    <image xlink:href="<?php echo Router::url($this->ProfilePicture->getSrc(null), true);?>" x="0"
	y="0" width="<?php ECHO $r*2?>" height="<?php ECHO $r*2?>" />
</pattern>

<?php $r = FOCUS_NODE_RADIUS ?>
<pattern id="no-img-focus"
	x="<?php ECHO $r?>" 
	y="<?php ECHO $r?>"
	patternUnits="userSpaceOnUse" 
	width="<?php ECHO $r*2?>" 
	height="<?php ECHO $r*2?>"> 		                
    <image xlink:href="<?php echo Router::url($this->ProfilePicture->getSrc(null), true);?>" x="0"
	y="0" width="<?php ECHO $r*2?>" height="<?php ECHO $r*2?>" />
</pattern>
 
</defs>
</svg>