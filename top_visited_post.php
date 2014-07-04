<?php
/*
Plugin Name: Nejnavštěvovanější příspěvky/Top visited posts
Plugin URI: http://jk-software.cz, phgame.cz
Description: Plugin, který ukazuje, které příspěvky jsou nejnavštěvovanější
Version: 1.0
Author: Webster.K
Author URI: http://jk-software.cz, http://phgame.cz
*/



function top_visited_post_install(){
	global $wpdb;
	mysql_query("DROP TABLE IF EXISTS ".$wpdb->prefix."plugin_pocitadlo");

	mysql_query("CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."plugin_pocitadlo (id INT NOT NULL AUTO_INCREMENT,stranka TEXT NOT NULL,cislo INT(1) NOT NULL,PRIMARY KEY (id))");
	
}
	
function top_visited_post_uninstall(){
	global $wpdb;
	mysql_query("DROP TABLE IF EXISTS ".$wpdb->prefix."plugin_pocitadlo");
}

function top_visited_post_dashboard_widgets_obsah() {
	global $wpdb;
	$pocet_zobrazeni = mysql_query("SELECT cislo, stranka FROM ".$wpdb->prefix."plugin_pocitadlo ORDER BY cislo DESC LIMIT 10") or die(mysql_error());
	?>
	<div class="top_visited_post_dashboard" style="width:100%">
		<table>
	<?php
	while($pocet_zobraz = mysql_fetch_array($pocet_zobrazeni)):
		echo "<tr><td>" . $pocet_zobraz["stranka"] . "</td><td>". $pocet_zobraz["cislo"] ."</td></tr>";
	endwhile;
	?>
	</table>
	</div>
	<?php
}

function top_visited_post_dashboard_widgets() {
	wp_add_dashboard_widget(
                 'top_visited_post_dashboard_widget',
                 'Počítadlo',
                 'top_visited_post_dashboard_widgets_obsah'
        );	
}
	
function top_visited_post_zobrazeni_na_strance() {

	global $wpdb;
	$adresa = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	
	$vyber_stranky = mysql_query("SELECT * FROM ".$wpdb->prefix."plugin_pocitadlo WHERE stranka='".$adresa."'");
	$pocet_stranek = mysql_num_rows($vyber_stranky);
	
	if($pocet_stranek>=1){
		//stranka je jiz vytvořena, stačí přidat hodnotu
		while($vyber_hodnoty = mysql_fetch_array($vyber_stranky)):
			$cislo = $vyber_hodnoty["cislo"];
		endwhile;
		$cislo = $cislo + 1;
		mysql_query("UPDATE ".$wpdb->prefix."plugin_pocitadlo SET cislo='". $cislo ."' WHERE stranka='".$adresa."'");
	}else{
		//stranku je potřeba vytvořit
		mysql_query("INSERT INTO ".$wpdb->prefix."plugin_pocitadlo (stranka, cislo) VALUES ('".$adresa."','1')");
	}
	?>
	<!--  1  -->
	<?php
}

add_action( 'wp_head', 'top_visited_post_zobrazeni_na_strance' );

add_action( 'wp_dashboard_setup', 'top_visited_post_dashboard_widgets' );	
	
add_action('activate_top-visited-post/top_visited_post.php', 'top_visited_post_install');

add_action('deactivate_top-visited-post/top_visited_post.php', 'top_visited_post_uninstall');