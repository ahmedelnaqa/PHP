<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جدول امتحانات مراقبى اللجان 2023-2024</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA0yAhklv3NHn+89t53hZHrg+8v2B1Ik7U8o2T1WR78W0U+V2oZ+8VLq6r" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" integrity="sha384-nU14brUcp6StFntEOOEBvcJm4huWjB0OcIeQ3fltAfSmuZFrkAif0T+UtNGlKKQv" crossorigin="anonymous">
	
<style>
container1{
	
	text-align:center;
	background-color:#1B2643;
}

h1{
	text-align:center;
	
}

table {
  border-collapse: collapse;
  width: 100%;
}

th {
  border: 1px solid black;
  text-align: center;
  padding: 8px;
  background-color:#D5A82B !important;
  color:#1B2643 !important;
}

td {
  border: 1px solid black;
  text-align: center;
  padding: 8px;
}

th {
  background-color: #000080;
  color: white;
}
</style>	
</head>
<body>
    <div class="container card text-white bg-dark mb-3" style="margin-top: 0px;">
	<div class="card text-center" style="margin-top: 20px;">
        <h1 class="card-header">جدول امتحانات مراقبى اللجان 2023-2024</h1>
          <div class="alert alert-success" role="alert" style="font-weight: bold;">
تم الانتهاء رفع جميع المراقبات
</div>
        <form action="" method="post" class="row mb-3">
            <div class="col-3" style="margin-top: 15px;">
              
                <label for="styid" class="col-form-label">الرقم القومي:</label>
            </div>
			<div class="col-6" style="margin-top: 15px;">
                <input type="text" name="styid" id="styid" class="form-control" placeholder="" pattern="[1-9]{1}[0-9]{13}"> 
				<p>يجب كتابه 14 رقم</p>
            </div>
             

            <div class="col-12">
                <button type="submit" class="btn btn-primary" style="margin-top: 10px;">بحث</button>
            </div>
        </form>
	</div>
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $styid = $_POST['styid'];

            // Connect to MySQL database
			$servername = "localhost";
			$username = "infihfyb_urisu";
			$password = "M7J-Ws1QmM[2";
			$dbname = "infihfyb_risu";


  // Connect to MySQL database
  $db = mysqli_connect($servername, $username, $password, $dbname);
            if (!$db) {
                die('Error: Could not connect to MySQL database');
            }
            // Set character set and collation for proper Arabic display
            mysqli_set_charset($db, "utf8");  // Use utf8mb4 if needed

            // Search for stylist
            $sql = "SELECT * FROM  monitors WHERE field5 = '$styid'";
            $result = mysqli_query($db, $sql);
			
			// read the name only:
			$sql1 = "SELECT field3,field4,field5 FROM  monitors WHERE field5 = '$styid'";
            $result1 = mysqli_query($db, $sql1);
			$MainData = mysqli_fetch_assoc($result1);
			//echo $MainData['field3'];
			/////////////////
            if (mysqli_num_rows($result) > 0) {
				echo '<p>الاسم: ' .$MainData['field4'] . '</p>';
				echo '<p>الرقم القومي: ' .$MainData['field5'] . '</p>';
				#echo '<p>رقم الجلوس: ' .$MainData['field4'] . '</p>';
                echo '<table class="table table-responsive table-striped">';
                echo '<thead class="table-dark"><tr><th>م</th><th>التاريخ</th><th>مكان اللجنة</th></tr></thead><tbody class="table-group-divider">';
				//<th>سكشن</th><th>مكان المقعد</th><th>الفرقه/المستوى</th><th>الفترة</th><th>مكان اللجنة</th><th>رقم اللجنة</th>
				$SID=1;
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<tr>';
                    echo '<td>' . $SID . '</td>';
					$SID += 1; 
                    echo '<td>' . $row['field2'] . '</td>';
                    echo '<td>' . $row['field3'] . '</td>';
					//echo '<td>' . $row['field8'] . '</td>';
                    //echo '<td>' . $row['field7'] . '</td>';
					//echo '<td>' . $row['field6'] . '</td>';
                    //echo '<td>' . $row['field2'] . '</td>';
					//echo '<td>' . $row['field1'] . '</td>';
                    //echo '<td>' . $row['field10'] . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p class="alert alert-danger" role="alert">لا يوجد بيانات خاصه بهذا الرقم</p>';
            }

            // Close MySQL connection
            mysqli_close($db);
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/5VuP+C3ns" crossorigin="anonymous"></script>
	
	
</body>
</html>

