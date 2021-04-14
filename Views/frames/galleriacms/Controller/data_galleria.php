
<?php

$path = "http://" . filter_input(INPUT_SERVER, 'HTTP_HOST') . "/";
$ulbum = $_GET['ulbum_id'];

ini_set('display_errors', '0');
mysql_connect('192.168.2.52', 'poweruser', 'iMfFIg7gAxCmstc76KyQ');
//mysql_connect('192.168.1.2:3308', 'poweruser', 'iMfFIg7gAxCmstc76KyQ');
mysql_select_db('nts_websites');
mysql_set_charset('utf8');

switch ($_GET['action']) {

    case 1:
        $select = "SELECT * FROM photolistgrid WHERE menu_id=$ulbum  AND type=1  AND visible=1";
        $result = mysqli_query($dbc,$select) or die(mysqli_error($dbc) . $select);

        $rowcheck = mysqli_num_rows($result);
        if ($rowcheck > 0) {
            while ($row = mysqli_fetch_array($result)) {
                $filename = $row['filename'];
                $name = $row['title'];
                $ulbum_id = $row['menu_id'];
                $info = $row['info'];
//           
                @list($yr, $month, $day, $hrs) = explode('-', $date);
                $dy = substr($day, 0, 2);

                $mageUrl = $path . 'photouploads/Views/js/pics/content/' . $ulbum_id . '/' . $filename;

                $data[] = array(
                    "thumb" => $mageUrl,
                    "image" => $mageUrl,
                    "title" => $name,
                    "description" => $info,
                    "link" => $mageUrl,
                );
            }
        } else {
            $selects = "SELECT * FROM photolistgrid WHERE menu_id=$ulbum   AND visible=1";
            $results = mysqli_query($dbc,$selects) or die(mysqli_error($dbc) . $select);
            while ($rows = mysqli_fetch_array($results)) {
                $name = $rows['title'];
                $ulbum_id = $rows['menu_id'];
                $path = $rows['path'];
                $info = $rows['info'];
                $data[] = array(
                    "video" => $path,
                    "title" => $name,
                    "description" => $info,
                );
            }
        }
        echo json_encode($data);
        break;
}
?>