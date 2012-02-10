<?php
/*
 *      guestbook.inc.php
 *      
 *      Copyright 2010 Indra Sutriadi Pipii <indra.sutriadi@gmail.com>
 *      
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *      
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */

	$array_pekerjaan = array(
		'pelajar' => __('Students'),
		'mahasiswa' => __('Mahasiswa'),
		'guru' => __('Teacher'),
		'dosen' => __('Lecturer'),
		'pns' => __('Civil Servants'),
		'karyawan' => __('Private Sector Employees'),
		'lsm' => __('Member of NGOs'),
		'irt' => __('Housewives'),
		'nonjob' => __('Does not work')
	);
	if (in_array(SENAYAN_VERSION, array('senayan3-stable15')))
		$admin = (isset($_COOKIE['admin_logged_in']) && $_COOKIE['admin_logged_in'] == 1) ? TRUE : FALSE;
	else
		$admin = (isset($_SESSION['uid']) && $_SESSION['uid'] == 1) ? TRUE : FALSE;
	$iframe_target = "<iframe name=\"submitExec\" class=\"noBlock\" style=\"visibility: hidden; width: 100%; height: 0pt;\"></iframe>";
	$link_entry = '<a href="?p=guestbook&v=form">' . __('Guestbook submission') . '</a>';
	$link_list = '&nbsp; <a href="?p=guestbook">' . __('Guestbook entries') . '</a>';
	$link_stats = '&nbsp; <a href="?p=guestbook&v=stats">' . __('Guestbook stats') . '</a>';

/*
 * prosedur ketika mengirimkan komentar buku tamu
 * atau menghapus items buku tamu
 */
if ($_POST):

	if (!isset($_SESSION))
		session_start();
	if (isset($_POST['delete'])):
		$items = (isset($_POST['items'])) ? $_POST['items'] : array();
		$n = count($items);
		$page = isset($_GET['page']) ? $_GET['page'] : 1;
		if ($admin === TRUE && $n != 0):
			$items = $_POST['items'];
			$cond = array();
			for ($c = 0; $c < count($items); $c++)
				$cond[] = "guestbook_id = {$items[$c]}";
			$conds = implode(" OR ", $cond);
			$sql = "DELETE FROM guestbook WHERE $conds";
			$dbs->query($sql);
			$script = "alert('" . __('Selected items deleted successfully!') . "');"
				. "window.parent.location.href = \"?p=guestbook&page=$page\";";
		elseif ($admin === TRUE && $n == 0):
			$script = "alert('" . __('No items selected!') . "');";
		else:
			$script = "alert('" . __('Access denied!') . "');";
		endif;
		echo "<html><head><script type=\"text/javascript\">$script</script></head></html>";
		exit();
	endif;

	$valid = FALSE;

	$badwords = array('%', '!', '~', '`', '@', '#', '$',
		'^', '&', '*', '(', ')', '-', '_', '+', '=',
		'{', '}', '[', ']', ':', ';', '<', '>', '?', '/', '\\');

	$nama = trim(ucwords(sprintf("%s", str_replace($badwords, '', $_POST['nama']))));
	$kota = trim(ucwords(sprintf("%s", str_replace($badwords, '', $_POST['kota']))));
	$email = trim(sprintf("%s", $_POST['email']));
	$website = trim(sprintf("%s", str_replace('http://', '', $_POST['website'])));
	$pekerjaan = trim(strtolower(sprintf("%s", $_POST['pekerjaan'])));
	$komentar = trim(sprintf("%s", $_POST['komentar']));
	
	$wpdoc = "wp.document.guestbook";
	if (empty($nama) || strlen($nama) <= 1):
		$script = "alert('Nama tidak valid atau kosong!');$wpdoc.nama.focus();";
	elseif (empty($kota) || strlen($kota) <= 1):
		$script = "alert('Kota tidak valid atau kosong!');$wpdoc.kota.focus();";
	elseif (empty($email) || ! preg_match("/^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/", $email)):
		$script = "alert('Email tidak valid!');$wpdoc.email.focus();";
	elseif ( ! empty($website) && ! preg_match('/^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&amp;?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/', 'http://' . $website)):
		$script = "alert('Website tidak valid!');$wpdoc.website.focus();";
	elseif (empty($pekerjaan) || ! array_key_exists($pekerjaan, $array_pekerjaan)):
		$script = "alert('Pilih pekerjaan!');$wpdoc.pekerjaan.focus();";
	elseif (empty($komentar) || strlen($komentar) <= 1):
		$script = "alert('Komentar tidak valid atau kosong!');$wpdoc.komentar.focus();";
	elseif ($_SESSION['security_code'] != $_POST['kode']):
		$valid = FALSE;
		$script = "alert('" . __('Wrong code!'). "');"
			."var c=wp.document.getElementById('captcha');"
			."var src=c.src;"
			."var pos=src.indexOf('?');"
			."if(pos>=0){src=src.substr(0,pos);};"
			."var date=new Date();"
			."c.src=src+'?p=captcha&v='+date.getTime();"
			."$wpdoc.kode.focus();";
	else:
		$valid = TRUE;
		$script = "wp.location.href = \"?p=guestbook&v=$view\";";
		$_SESSION['guestbook'] = true;
	endif;

	$sql = '';
	if ($valid == TRUE):	
		$cols = "guestbook_posted, guestbook_nama, guestbook_kota,"
			." guestbook_email, guestbook_website,"
			." guestbook_pekerjaan, guestbook_komentar";
		$vals = "(now(), '$nama', '$kota', '$email', '$website', '$pekerjaan', '$komentar')";
		$sql = "INSERT INTO guestbook ($cols) VALUES $vals";
		$dbs->query($sql);
	endif;

	$view = $valid === TRUE ? "" : "form";
	echo "<html><head><script type=\"text/javascript\">var wp=window.parent;$script</script></head></html>";
	exit();
/*
 * prosedur tidak dalam status mengirim:
 * tampilan daftar item buku tamu,
 * formulir pengisian buku tamu,
 * statistik buku tamu
 */
else:

	ob_start();
	$info = __('Website Visitors Comments');
	/*
	 * tampilan daftar item buku tamu
	 */
	if ( ! isset($_GET['v']) || ($_GET['v'] != 'form' && $_GET['v'] != 'stats')):
		if(!isset($_SESSION))
			session_start();
		//session_destroy();
		require SIMBIO_BASE_DIR.'simbio_GUI/paging/simbio_paging.inc.php';
		$limit = 10;
		$range = 2;
		$allrange = ($range * 2) + 1;
		$page = isset($_GET['page']) ? $_GET['page'] : 1;
		$offset = ($page - 1) * $limit;
		$d0 = $page - $range;
		$d1 = $page + $range;
		$first = 1;
		$prev = $page - 1;
		$next = $page + 1;

		$table = "guestbook";
		$sql = "SELECT * FROM `%s`";
		$sql_all = sprintf($sql, $table);
		$sql_cur = sprintf($sql
			. " ORDER BY `guestbook_id` DESC"
			. " LIMIT $offset, $limit"
			, $table);
		$items_all = $dbs->query($sql_all);
		$items_cur = $dbs->query($sql_cur);

		if ($items_cur->num_rows == 0):
			$list = '<p>' . __('The list is empty') . '</p>';
		else:
			$list = '';
			if ($admin === TRUE):
				$list = '<form action="?p=guestbook" '
					. ' method="post" '
					. ' name="guestbook" '
					. ' id="guestbook" '
					. ' target="submitExec">';
			endif;
			$bgcolor = array(0 => '#EDEFF4', 1 => '#FFF');
			$x = 0;
			while($item = $items_cur->fetch_assoc()):
				$input = $admin === TRUE ? "<input type=\"checkbox\" name=\"items[]\" value=\"{$item['guestbook_id']}\" /><br />" : "";
				$nama = ( ! empty($item['guestbook_website'])) ?
					"<a style=\"font-size: 8pt;\" href=\"http://{$item['guestbook_website']}\" target=\"_blank\">{$item['guestbook_nama']}</a>" :
					"<strong>{$item['guestbook_nama']}</strong>";
				$list .= "<p style=\"padding: 2px; background: ".$bgcolor[$x % 2]."\">"
					. "$input"
					. "<span style=\"float: right;\">" . __('Posted at') . " {$item['guestbook_posted']}</span>"
					. "$nama di {$item['guestbook_kota']}"
					. "<br /><span style=\"font-style: italic;\">" . nl2br($item['guestbook_komentar']) . "</span>"
					. "</p>";
				$x++;
			endwhile;
			if ($admin === TRUE) :
				$list .= sprintf('<input type="hidden" id="delete" name="delete" value="delete" /> '
					. ' <input type="button" value="%s" '
					. ' onclick="var c=confirm(\'%s\');alert(c);if(c === true){this.form.submit()};" /> '
					. '</form>',
					__('Delete'),
					__('Delete selected items?')
					);
			endif;
		endif;
		$num_all = $items_all->num_rows;

		$last = @ceil($num_all/$limit);
		$nomor = array();
		$s = 0;
		for ($x = $d0; $x <= $d1; $x++):
			if ($x >= $first && $x <= $last):
				$nomor[$s] = $x;
				$s++;
			endif;
		endfor;
		$nums = count($nomor);
		$selisih = $nums - $allrange;
		if ($selisih < 0 && $last >= $allrange):
			$add = $selisih * -1;
			$l = $nums - 1;
			if ($nomor[0] == $first):
				$m = $l + $add;
				for ($y = $l+1; $y <= $m; $y++):
					if ($y <= $last):
						$nomor[$y] = $y + 1;
					endif;
				endfor;
			elseif ($nomor[$l] == $last):
				for($y = $selisih; $y < 0; $y++):
					$m = $nomor[0] + $y;
					if ($m >= $first):
						$nomor[$y] = $m;
					endif;
				endfor;
			endif;
		endif;
		asort($nomor);

		if ($nums > 1):
			$options = '';
			foreach ($nomor as $x):
				$selected = $x == $page ? "selected" : "";
				$options .= "<option $selected value=\"$x\">$x</option>";
			endforeach;
		else:
			$options = "<option>1</option>";
		endif;
		$link_first = $page != $first ? "<a href=\"?p=guestbook&page=$first\">" . __('First page') . "</a> &nbsp;" : "";
		$link_prev = $page != $first ? "<a href=\"?p=guestbook&page=$prev\">" . __('Previous') . "</a> &nbsp;" : "";
		$link_next = $page != $last ? "&nbsp; <a href=\"?p=guestbook&page=$next\">" . __('Next') . "</a>" : "";
		$link_last = $page != $last ? "&nbsp; <a href=\"?p=guestbook&page=$last\">" . __('Last page') . "</a>" : "";
		if ($nums != 0)
			$paging = "<hr /><p>$link_first $link_prev Page: <select onchange=\"location.href='?p=guestbook&page='+this.value\">$options</select> of $last $link_next $link_last</p>";
		else
			$paging = "";

?>

<div id="guestbookList" name="gbList">
	<h3><?php echo __('Guestbook Entries');?></h3>
	<p><?php echo $link_entry . $link_stats;?></p>
	<div>
		<?php echo $list;?>
	</div>
	<div style="text-align: center;"><?php echo $paging;?></div>
	<p><?php echo $link_entry . $link_stats;?></p>
	<?php echo $iframe_target;?>
</div>

<?php
	/*
	 * formulir buku tamu
	 */
	elseif ($_GET['v'] == 'form'):
		$data_image = 'iVBORw0KGgoAAAANSUhEUgAAAH8AAAAKCAYAAACdUoFhAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAN1wAADdcBQiibeAAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAUhSURBVFiF7dd9rNd1FQfw14GbpEEi4QPMBDNzhlquaZIUJNbIx016cKs/fFppxcxWznQ9UD72MGDWRgQqNSyybCZY9jDgZhDmIlErwpkyU4scJmI1ldMfn3Plx+Ve7r0x65/O9tvv8z2f9/mc8z2fz+d9zhe6M9Ngf5iKK4disyc/HIYDdjN/Md49hPW6sKLGb8F1fWBGYTLG/Rff8224quOdZr7UPrtwtaHJSBw0RJs9kY/g1/h2P/M/wbNDWC+0AwUbsfTFiYj98CWMxr04NCIOwazM/P0Q4x6qbMDWGu+PZ15if7pwJn4cEUfhJIzBG3BDZt4OEXEgLsU4rO4xjohz8HecUcH/EJdX8MuwODOf7nQYEYFPYDqew+zMvCci5mfmhYV5E47FCpyKEyLiTFxW8T6Es9GNLdiMRyLibLxfOww3Z+Zttd4InI8Z+EZHOAfhBPwmIobhB/hUZq7piHd/LI6ISzJzQ0R04QOYiZsxNTMvjIiJFdMLmIYb8AA+iz9rDLM5Io7EFZWj5bipcjQOx2Ndf5tVvi/BydhWubu3fH8Yr8RXM/P+ws/HT3EeVuHruBb74Bp4rKhmei14FibhQYysubvxPhyNe7Cw9NdrN+TN2A+vxRHazfk8Lu2D3t6FhRq1HoyJpX+iA3NGBTocX6uNG13P38VdOKZ0X8AFZTe5kjqhXvbw0l+NuZhYm7Kp9O/AN2t8Lj6EYZhT9gvw5bJbVLhZWIxDKpHPlf6N+Jd2+I7Go1iC11eMswvXmaPZuKz0p3T4uBLn95G7z+FWHI5D8ZqKd2PZn4iHMabw/8BV5fNX+LlW6s7Cj4bZWdZm5q2Z+UAl+MSIGIuuzFyamffhll42SzNzbWZuKcfj8THtJJ9uV9mG42ruycx8uA8MyMwXKqHPZuZT9Qw3Zub6zHyql8kGvB0fxXacVvp34prydX0/7qbhNkzB85k5FWsxtuzGFm4GvpKZm/BFrYz0yB8yc0nl6bdYlpm/w3e0pLMjRxfX/2kGL+/R+q2NmfmnzHxIY671mXlHZv5SY69TCr8d12bmg7ijcKsLc0zvzX+sY7xVu50H4MkO/eZeNn/sGJ+Hc7BSq9G79AaZ2V2YyRrdTuvjJffuQ9efz05ZgldpyV7T4X8Meg7KX/uxHYW/4EjcX7r7ICLGaDeMRq099fifGs33SGf+nul43qb1SjSGOVdjliWG1j9tx+O9dOPt/E5PaBcPtmZmTx/xYjzZaGF4783fRerkToiIfUs1Yzfw6VoNW6nRHYiIkRHx1hqPyMx1mTkL12kUD5si4uAan9qx5uNaSdmtVD2cpFH1Oo1deqS7YqOVnb5ko0bHy/HBiDgZn9SSuwjzCnenVvPhvdhroNh6yUl25GjSQOCIOCoiXl2P3y+fPXOhNbxTIuLlETFcy93ywQQy4OaXzMYvIuLuAXALMTciVtg5+RNwY40vioi7IuJ7GlP0NGDzcGf56IzrdsyMiDUR8br+HGfm81oD1q1t/paO6bm4IiJWaZ+qfclN+ExmPooLNGaaozWdn87MnxVuAcZHxHqtKX2kv5j6kUWYUzk6fhD4j9tRGhbguIhYHRErcXo1i/M1JlmNVXVhB5YhfIeOwCsGgXsZRg+AGaWPb2iN7vfZk29XrZHaq5+5MQPYXoRvaQ1S4ECtcT2iAxMd4ylY8h/EOGCOBvGO+/bSdWHvoawTZfh/KYmIY7WafBj+hlsyc1mv+Xl4Wjusl2fm2v9FrHsq/wbziWhuSmbLtwAAAABJRU5ErkJggg==';
		$data_image = 'data:image/png;base64,' . $data_image;
?>

<div id="guestbookForm">
	<h3><?php echo __('Guestbook Submission');?></h3>
	<form action="?p=guestbook" method="post" name="guestbook" id="guestbook" target="submitExec">
	<p><?php echo __('(*) is required.');?></p>
	<table>
		<tr valign="top">
			<td><label for="nama"><?php echo __('Name');?>: *</label></td>
			<td>
				<input id="nama" name="nama" type="text" size="30" />
				<br />
				<span><?php echo __('Example');?>: Indra Sutriadi Pipii</span>
			</td>
		</tr>
		<tr valign="top">
			<td><label for="kota"><?php echo __('City');?>: *</label></td>
			<td>
				<input id="kota" name="kota" type="text" size="30" />
				<br />
				<span><?php echo __('Example');?>: Kotamobagu</span>
			</td>
		</tr>
		<tr valign="top">
			<td><label for="email"><?php echo __('E-Mail');?>: *</label></td>
			<td>
				<input id="email" name="email" type="text" size="30" />
				<br /><span><?php echo __('Example');?>: <img src="<?php echo $data_image;?>" style="display: inline; margin: 0; padding: 0; border:0; " border="0" /> , <?php echo __('e-mail will not be displayed');?>.</span>
			</td>
		</tr>
		<tr valign="top">
			<td><label for="website"><?php echo __('Website');?>:</label></td>
			<td>
				http://<input id="website" name="website" type="text" size="30" />
				<br /><span><?php echo __('Example');?>: sutriadi.web.id <?php echo __('atau');?> facebook.com/indra.sutriadi</span>
			</td>
		</tr>
		<tr valign="top">
			<td><label for="pekerjaan"><?php echo __('Occupation');?>: *</label></td>
			<td>
				<select id="pekerjaan" name="pekerjaan">
					<option value="">-- <?php echo __('Select occupation');?> --</option>
<?php
	foreach ($array_pekerjaan as $value => $label)
		echo "<option value=\"$value\">$label</option>";
?>

				</select>
				<br /><span><?php echo __('Occupation will not be displayed.');?></span>
			</td>
		</tr>
		<tr valign="top">
			<td><label for="komentar"><?php echo __('Comment');?>: *</label></td>
			<td><textarea id="komentar" name="komentar" cols="30" rows="4"></textarea></td>
		</tr>
		<tr valign="top">
			<td></td>
			<td><label for="kode"><img id="captcha" name="captcha" src="?p=captcha" style="display: inline; margin: 0; padding: 0; border:0; " border="0" /></label></td>
		</tr>
		<tr valign="top">
			<td><label for="kode"><?php echo __('Code');?>: *</label></td>
			<td>
				<input id="kode" name="kode" size="5" maxlength="5" />
				<br /><span><?php echo __('Enter code from image above');?></span>
			</td>
		</tr>
		<tr valign="top">
			<td></td>
			<td>
				<input type="submit" value="Kirim" />
				<input type="reset" value="Batal" />
				<input type="button" onclick="history.back(-1);" value="Kembali" />
			</td>
		</tr>
	</table>
	</form>
</div>
<?php echo $iframe_target;?>


<?php
	/*
	 * statistik buku tamu
	 */
	elseif ($_GET['v'] == 'stats'):
		$d = isset($_GET['d']) ? (int) $_GET['d'] : 0;
		$m = isset($_GET['m']) ? (int) $_GET['m'] : 0;
		$y = isset($_GET['y']) ? (int) $_GET['y'] : 0;
		$where = "1";
		if ($d != 0)
			$where .= " AND DAY(guestbook_posted) = $d ";
		if ($m != 0)
			$where .= " AND MONTH(guestbook_posted) = $m ";
		if ($y != 0)
			$where .= " AND YEAR(guestbook_posted) = $y ";
		$sql = "SELECT guestbook_pekerjaan AS job, "
			. " COUNT(guestbook_id) AS post "
			. " FROM guestbook "
			. " WHERE $where "
			. " GROUP BY guestbook_pekerjaan";
		$items = $dbs->query($sql);
		$list = array();
		while ($item = $items->fetch_assoc()):
			$list[$item['job']] = $item['post'];
		endwhile;
		//$posts = array();
		$tbody = "";
		$bgcolor = array(0 => '#EDEFF4', 1 => '#FFF');
		$tr = 0;
		foreach ($array_pekerjaan as $value => $label):
			//$posts[$value] = array($label, isset($list[$value]) ? $list[$value] : 0);
			$post = isset($list[$value]) ? $list[$value] : 0;
			$tbody .= "<tr bgcolor=\"" . $bgcolor[$tr%2] . "\"><td>$label</td><td align=\"center\">$post</td></tr>";
			$tr++;
		endforeach;
?>

<div id="guestbookStats">
	<h3><?php echo __('Guestbook Stats');?></h3>
	<p><?php echo $link_entry . $link_list;?></p>
	<form name="guestbook" id="guestbook" method="get" action="<?php echo $_SERVER['PHP_SELF'];?>">
		<input type="hidden" name="p" value="guestbook" />
		<input type="hidden" name="v" value="stats" />
		<p>
			<?php echo __('Day');?> : <select name="d">
				<option value="0"><?php echo __('All');?></option>
<?php
	for ($i = 1; $i <= 31; $i++):
		$selected = $i == $d ? "selected" : "";
		echo "<option $selected value=\"$i\">$i</option>";
	endfor;
?>

			</select>
			<?php echo __('Month');?> : <select name="m">
				<option value="0"><?php echo __('All');?></option>
<?php
	for ($i = 1; $i <= 12; $i++):
		$selected = $i == $m ? "selected" : "";
		echo "<option $selected value=\"$i\">" . date('F', mktime(0, 0, 0, $i, 1, 0)) . "</option>";
	endfor;
?>

			</select>
			<?php echo __('Year');?> : <select name="y">
				<option value="0"><?php echo __('All');?></option>
<?php
	$ybase = "2010";
	$ynow = date('Y');
	while ($ybase <= $ynow):
		$selected = $ybase == $y ? "selected" : "";
		echo "<option $selected value=\"$ybase\">$ybase</option>";
		$ybase++;
	endwhile;
?>

			</select>
			<input type="submit" value="Lihat" />
		</p>
		<div style="margin: 3px;">
			<table width="50%" cellspacing="0">
				<thead bgcolor="#C7C7C7">
					<tr>
						<th><?php echo __('Occupation');?></th>
						<th><?php echo __('Total');?></th>
					</tr>
				</thead>
				<tbody><?php echo $tbody;?></tbody>
			</table>
		</div>
	</form>
	<p><?php echo $link_entry . $link_list;?></p>
</div>

<?php

	endif;
endif;
?>
