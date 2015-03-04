<?php
/*
Plugin Name: Nejnavštěvovanější příspěvky/Top visited posts
Plugin URI: http://jk-software.cz, phgame.cz
Description: Plugin, který ukazuje, které příspěvky jsou nejnavštěvovanější
Version: 2.0
Author: Webster.K
Author URI: http://jk-software.cz, http://phgame.cz
*/



function top_visited_post_install(){
	global $wpdb;
	//mysql_query("DROP TABLE IF EXISTS ".$wpdb->prefix."plugin_pocitadlo");
	//mysql_query("DROP TABLE IF EXISTS ".$wpdb->prefix."plugin_pocitadlo_dnes");
	//mysql_query("DROP TABLE IF EXISTS ".$wpdb->prefix."plugin_pocitadlo_tyden");
	//mysql_query("DROP TABLE IF EXISTS ".$wpdb->prefix."plugin_pocitadlo_celek");

	mysql_query("CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."plugin_pocitadlo (id INT NOT NULL AUTO_INCREMENT,post_id INT NOT NULL,cas INT NOT NULL, cislo INT(1) NOT NULL,PRIMARY KEY (id))");
	mysql_query("CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."plugin_pocitadlo_dnes (id INT NOT NULL AUTO_INCREMENT,post_id INT NOT NULL,cas INT NOT NULL, cislo INT(1) NOT NULL,PRIMARY KEY (id))");
	mysql_query("CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."plugin_pocitadlo_tyden (id INT NOT NULL AUTO_INCREMENT,post_id INT NOT NULL,cas INT NOT NULL, cislo INT(1) NOT NULL,PRIMARY KEY (id))");
	mysql_query("CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."plugin_pocitadlo_celek (id INT NOT NULL AUTO_INCREMENT,post_id INT NOT NULL,cas INT NOT NULL, cislo INT(1) NOT NULL,PRIMARY KEY (id))");
}
	
function top_visited_post_uninstall(){
	//global $wpdb;
	//mysql_query("DROP TABLE IF EXISTS ".$wpdb->prefix."plugin_pocitadlo");
}

function top_visited_post_dashboard_widgets_obsah() {
	global $wpdb;
	$pocet_zobrazeni = mysql_query("SELECT cislo, cas, post_id FROM ".$wpdb->prefix."plugin_pocitadlo_celek ORDER BY cislo DESC LIMIT 15") or die(mysql_error());
	?>
	<div class="top_visited_post_dashboard" style="width:100%">
	<?php
	if(isset($_POST["pocitadlo_submit"])){
		echo "Tabulka byla obnovena<br>";
	}
	?>
		<table width="100%"><tr><td colspan="2"><br><b>15 celkově nejnavštěvovanějších příspěvků na blogu:</b></td></tr><tr><td><b>Příspěvek</b></td><td><b>Počet zobrazení</b></td></tr><tr><td colspan="2"><hr></td></tr>
	<?php
	while($pocet_zobraz = mysql_fetch_array($pocet_zobrazeni)):
		echo "<tr><td><a href=\"". get_permalink($pocet_zobraz["post_id"]) ."\" target=\"_blank\">" . get_the_title($pocet_zobraz["post_id"]) . "</a></td><td width=\"50px\">". $pocet_zobraz["cislo"] ."</td></tr>";
	endwhile;
	
	$pocet_zobrazeni_tyden = mysql_query("SELECT cislo, cas, post_id FROM ".$wpdb->prefix."plugin_pocitadlo_tyden ORDER BY cislo DESC ") or die(mysql_error());
	echo "<tr><td colspan=\"2\"><br><br><b>10 nejnavštěvovanějších příspěvků za poslední týden:</b></td></tr>";
	while($pocet_zobraz_tyden = mysql_fetch_array($pocet_zobrazeni_tyden)):
		echo "<tr><td><a href=\"". get_permalink($pocet_zobraz_tyden["post_id"]) ."\" target=\"_blank\">" . get_the_title($pocet_zobraz_tyden["post_id"]) . "</a></td><td width=\"50px\">". $pocet_zobraz_tyden["cislo"] ."</td></tr>";
	endwhile;
		
	$pocet_zobrazeni_dnes = mysql_query("SELECT cislo, cas, post_id FROM ".$wpdb->prefix."plugin_pocitadlo_dnes ORDER BY cislo DESC ") or die(mysql_error());
	echo "<tr><td colspan=\"2\"><br><br><b>Dnešních 10 nejnavštěvovanějších příspěvků:</b></td></tr>";
	while($pocet_zobraz_dnes = mysql_fetch_array($pocet_zobrazeni_dnes)):
		echo "<tr><td><a href=\"". get_permalink($pocet_zobraz_dnes["post_id"]) ."\" target=\"_blank\">" . get_the_title($pocet_zobraz_dnes["post_id"]) . "</a></td><td width=\"50px\">". $pocet_zobraz_dnes["cislo"] ."</td></tr>";
	endwhile;
	
	
	echo "<tr><td colspan=\"2\"><br><br><b>Vaše nejnavštěvovanější příspěvky:</b></td></tr>";
	$pocet_zobrazeni_user = mysql_query("SELECT cislo, cas, post_id FROM ".$wpdb->prefix."plugin_pocitadlo_celek ORDER BY cislo DESC") or die(mysql_error());
	$i = 0;
	while($pocet_zobraz_usr = mysql_fetch_array($pocet_zobrazeni_user)):
		$zjisteni_id_autora = mysql_query("SELECT post_author FROM ".$wpdb->prefix."posts WHERE ID='".$pocet_zobraz_usr["post_id"]."'");
		while($post_autor_id = mysql_fetch_array($zjisteni_id_autora)):
			$user_id_ted = get_current_user_id();
			if($user_id_ted == $post_autor_id["post_author"]){
				//je autor, takze zobrazit
				if($i<=10){
					echo "<tr><td><a href=\"". get_permalink($pocet_zobraz_usr["post_id"]) ."\" target=\"_blank\">" . get_the_title($pocet_zobraz_usr["post_id"]) . "</a></td><td width=\"50px\">". $pocet_zobraz_usr["cislo"] ."</td></tr>";
				}
			}
		endwhile;
		$i++;
	endwhile;
	
	?>
	</table><br><br>
	<!--
	<form action="" method="post">
	<input type="submit" value=" Aktualizovat tabulku" name="top_visited_post" class="button button-primary">
	</form>
	-->
	</div>
	<?php
}

function top_visited_post_dashboard_widgets() {
	if(isset($_POST["top_visited_post"])){
		//pridat funkci na aktualizaci
		top_visited_post_aktualizace_tabulek();
		
		
	}
	wp_add_dashboard_widget(
                 'top_visited_post_dashboard_widget',
                 'Počítadlo',
                 'top_visited_post_dashboard_widgets_obsah'
        );	
}

if ( ! wp_next_scheduled( 'top_visited_post_task_hook' ) ) {
  wp_schedule_event( time(), 'hourly', 'top_visited_post_task_hook' );
}

add_action( 'top_visited_post_task_hook', 'top_visited_post_aktualizace_tabulek' );

function top_visited_post_aktualizace_tabulek() {
	global $wpdb;
	//aktualizace tabulek	
	//celkem prispevky
	mysql_query("TRUNCATE table ".$wpdb->prefix."plugin_pocitadlo_celek");
	mysql_query("INSERT INTO ".$wpdb->prefix."plugin_pocitadlo_celek (post_id, cas, cislo) SELECT post_id, cas, sum(cislo) as cislo FROM ".$wpdb->prefix."plugin_pocitadlo GROUP BY post_id ORDER BY cislo DESC LIMIT 1000");
	
	//celkem tyden
	$datum = StrFTime("%d/%m/%Y", current_time( 'timestamp', 0 ));
	$retezec_pro_datum = explode("/",$datum);
	$datum_na_strtime = mktime(0, 0, 0, $retezec_pro_datum[1], $retezec_pro_datum[0], $retezec_pro_datum[2]);
	$datum_na_strtime_tyden = $datum_na_strtime - (7 * 24 * 3600);
	mysql_query("TRUNCATE table ".$wpdb->prefix."plugin_pocitadlo_tyden");
	mysql_query("INSERT INTO ".$wpdb->prefix."plugin_pocitadlo_tyden (post_id, cas, cislo) SELECT post_id, cas, sum(cislo) as cislo FROM ".$wpdb->prefix."plugin_pocitadlo WHERE cas>'".$datum_na_strtime_tyden."' GROUP BY post_id ORDER BY cislo DESC LIMIT 10");
	
	//celkem dnes
	$datum_na_strtime_dnes = strtotime($datum);
	mysql_query("TRUNCATE table ".$wpdb->prefix."plugin_pocitadlo_dnes");
	mysql_query("INSERT INTO ".$wpdb->prefix."plugin_pocitadlo_dnes (post_id, cas, cislo) SELECT post_id, cas, sum(cislo) as cislo FROM ".$wpdb->prefix."plugin_pocitadlo WHERE cas>'".$datum_na_strtime."' GROUP BY post_id ORDER BY cislo DESC LIMIT 10");
	
	}


	
function top_visited_post_zobrazeni_na_strance() {
	if(is_single()){
		$id = get_the_ID();
		global $wpdb;
	
		$cas = current_time( 'timestamp', 0 );
		//stranku je potřeba vytvořit
		mysql_query("INSERT INTO ".$wpdb->prefix."plugin_pocitadlo (post_id,cas,cislo) VALUES ('".$id."','".$cas."','1')");
		?>
		<!--  Top visited post is active - post ID: <?php echo $id;?> -->
		<?php
	}else{
	?>
	<!--  Top visited post is active -->
	<?php
	}
}

add_action( 'wp_head', 'top_visited_post_zobrazeni_na_strance' );

add_action( 'wp_dashboard_setup', 'top_visited_post_dashboard_widgets' );	
	
add_action('activate_top-visited-post/top_visited_post.php', 'top_visited_post_install');

add_action('deactivate_top-visited-post/top_visited_post.php', 'top_visited_post_uninstall');