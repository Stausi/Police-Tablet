<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$sql = "SELECT * FROM population_wanted";
$result = $link->query($sql);
?>

<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="utf-8">

    <title>Stausi Database</title>
    <meta name="description" content="Stausi-Data">
    <meta name="author" content="Stausi">
    <link rel="stylesheet" href="/assets/css/board.css">
</head>
<body>
    <header>
        <h1>Eftersøgt af Politiet</h1>
    </header>

    <div class="wanted-list">
        <?php 
        while($row = $result->fetch_assoc()) { 
            $sql_player = "SELECT * FROM population WHERE id='" . $row['target_id'] . "'";
            $result_player = $link->query($sql_player);

            $target_id = 0;
            $target_name = "";
			
			while($row_player = mysqli_fetch_array($result_player)) {
                $target_name = $row_player['name'];
                $target_id = $row_player['id'];
            }

            $imgURL = "";
            $file_pointer = '../assets/playersIMG/' . $target_id . '.png';

            if (file_exists($file_pointer)) {
                $imgURL = $file_pointer;
            } else {
                $imgURL = '../assets/playersIMG/unknown.png';
            }
        ?>
            <div class="wanted-entry">
                <img src="<?php echo $imgURL ?>" alt="Wanted Individual">
                <div class="info">
                    <?php echo "<h2>" . $target_name . "</h2>" ?>
                </div>
            </div>
        <?php } ?>
    </div>
</body>
</html>
